<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Import putusan dari Direktori Mahkamah Agung (putusan3.mahkamahagung.go.id).
 *
 * Situs MA memblokir akses dari server (Cloudflare challenge),
 * sehingga data diambil dari snapshot Wayback Machine atas halaman resmi MA —
 * sumber yang sama (satu-satunya sumber resmi), hanya lewat arsipnya.
 *
 * Pipa: listing per-PN (arsip) -> halaman detail (arsip) -> dokumen PDF asli (arsip)
 *       -> ekstraksi teks via smalot/pdfparser.
 *
 * PENTING: hasil impor disimpan sebagai DRAFT (is_published=false) dan wajib
 * direview/dipublish via admin sebelum tampil di pustaka publik.
 */
class PutusanDirectoryService
{
    public const MA_BASE = 'https://putusan3.mahkamahagung.go.id';
    public const CDX_ENDPOINT = 'https://web.archive.org/cdx/search/cdx';
    public const WAYBACK_WEB = 'https://web.archive.org/web';

    public const KATEGORI = [
        'perdata-17441'      => ['label' => 'Perdata', 'count' => 17441, 'ma_slug' => 'perdata'],
        'pidana-umum-5237'   => ['label' => 'Pidana Umum', 'count' => 5237, 'ma_slug' => 'pidana-umum'],
        'pidana-khusus-5065' => ['label' => 'Pidana Khusus', 'count' => 5065, 'ma_slug' => 'pidana-khusus'],
        'perdata-agama-1665' => ['label' => 'Perdata Agama', 'count' => 1665, 'ma_slug' => 'perdata-agama'],
        'perdata-khusus-12'  => ['label' => 'Perdata Khusus', 'count' => 12, 'ma_slug' => 'perdata-khusus'],
        'tun-1'              => ['label' => 'TUN', 'count' => 1, 'ma_slug' => 'tun'],
    ];

    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
    ];

    /** @var array<string,string> prefix slug -> jenis_pengadilan */
    private const JENIS_PREFIX = [
        'pn-' => 'PN',
        'pa-' => 'PA',
        'pt-' => 'PT',
        'ptun-' => 'PTUN',
        'ms-' => 'PA',
        'mt-' => 'PT',
        'pk-' => 'PK',
    ];

    private int $sleepMicros = 250000;

    private function ua(): string
    {
        return self::USER_AGENTS[array_rand(self::USER_AGENTS)];
    }

    public function categoryLabel(?string $slug): string
    {
        if (!$slug || $slug === '') {
            return 'Umum';
        }
        return self::KATEGORI[$slug]['label'] ?? str_replace('-', ' ', $slug);
    }

    /**
     * Cari snapshot halaman asli di Wayback.
     *
     * @return array{ts:string,original:string}|null
     */
    public function cdxLatest(string $url, string $matchType = 'prefix'): ?array
    {
        $key = 'pds_cdx_' . md5($matchType . '|' . $url);

        return Cache::remember($key, now()->addDays(7), function () use ($url, $matchType) {
            try {
                $resp = Http::timeout(45)
                    ->retry(2, 500)
                    ->withHeaders(['User-Agent' => $this->ua()])
                    ->get(self::CDX_ENDPOINT, [
                        'url' => $url,
                        'matchType' => $matchType,
                        'limit' => 200,
                        'collapse' => 'urlkey',
                        'fl' => 'timestamp,original',
                        'filter' => 'statuscode:200',
                    ]);

                if (!$resp->ok()) {
                    return null;
                }

                $lines = [];
                foreach (explode("\n", str_replace("\r", '', trim($resp->body()))) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = explode(' ', $line);
                    if (count($parts) >= 2) {
                        $lines[] = ['ts' => $parts[0], 'original' => $parts[1]];
                    }
                }
                if (!$lines) {
                    return null;
                }

                usort($lines, fn ($a, $b) => strcmp($a['ts'], $b['ts']));

                return end($lines) ?: null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /**
     * Ambil konten mentah halaman (HTML atau biner) dari arsip Wayback.
     * timestamp argumen = hasil cdxLatest; jika null, dicari sendiri via cdxLatest.
     */
    public function fetchArchived(string $originalUrl, ?string $timestamp = null): ?string
    {
        if ($timestamp === null) {
            $snap = $this->cdxLatest($originalUrl, 'exact');
            if (!$snap) {
                return null;
            }
            $timestamp = $snap['ts'];
        }

        $key = 'pds_page_' . md5($timestamp . '|' . $originalUrl);

        return Cache::remember($key, now()->addHours(1), function () use ($originalUrl, $timestamp) {
            $this->bePolite();
            $archiveUrl = self::WAYBACK_WEB . '/' . $timestamp . 'id_/' . $originalUrl;
            try {
                $resp = Http::timeout(60)
                    ->retry(2, 500)
                    ->withHeaders(['User-Agent' => $this->ua()])
                    ->get($archiveUrl);

                return $resp->ok() && $resp->body() !== '' ? $resp->body() : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    private function bePolite(): void
    {
        if ($this->sleepMicros > 0) {
            usleep($this->sleepMicros);
        }
    }

    /** Susun URL dasar listing direktori MA utk satu pengadilan. */
    private function courtBaseUrl(string $pn): string
    {
        return self::MA_BASE . '/direktori/index/pengadilan/' . $pn;
    }

    /**
     * Pilih snapshot listing terbaik dari daftar baris CDX sebuah pengadilan:
     * url yang paling sesuai kategori+tahun lalu termuda.
     *
     * @param array<int,array{ts:string,original:string}> $rows
     * @return array{snap:array{ts:string,original:string},tahun_used:bool}|null
     */
    private function pickListingSnapshot(array $rows, string $kategori = '', string $tahun = ''): ?array
    {
        $candidates = $rows;
        if ($kategori !== '' && isset(self::KATEGORI[$kategori])) {
            $slug = self::KATEGORI[$kategori]['ma_slug'];
            $filtered = array_filter($candidates, fn ($r) => str_contains($r['original'], '/kategori/' . $slug));
            if ($filtered) {
                $candidates = $filtered;
            }
        }

        $result = null;
        foreach ($candidates as $r) {
            if ($tahun !== '') {
                $y = preg_quote($tahun, '/');
                if (preg_match('/(?:tahunjenis|tahun)\/.*' . $y . '/', $r['original'])) {
                    if ($result === null || $r['ts'] > $result['ts']) {
                        $result = $r;
                    }
                }
            }
        }

        if ($tahun !== '' && $result === null) {
            foreach ($candidates as $r) {
                if (str_contains($r['original'], 'tahunjenis')) {
                    continue;
                }
                if ($result === null || $r['ts'] > $result['ts']) {
                    $result = $r;
                }
            }
        }

        if ($result === null) {
            usort($candidates, fn ($a, $b) => strcmp($a['ts'], $b['ts']));
            $result = end($candidates) ?: null;
        }

        if ($result === null) {
            return null;
        }

        return ['snap' => $result, 'tahun_used' => $tahun !== '' && str_contains($result['original'], '/' . $tahun)];
    }

    /**
     * Parse HTML listing direktori MA.
     *
     * @return array<int,array{nomor:string,court_slug:string,court_name:string,kategori:string,register:string|null,putus:string|null,upload:string|null,snippet:string,detail_url:string}>
     */
    public function parseListing(string $html): array
    {
        $entries = [];
        $blocks = explode('<div class="spost clearfix">', $html);
        array_shift($blocks);

        foreach ($blocks as $block) {
            $title = null;
            if (preg_match('#<strong><a[^>]*>([^<]+)</a></strong>#s', $block, $m)) {
                $title = trim(strip_tags($m[1]));
            } elseif (preg_match('#<a[^>]*class="entry-title"[^>]*>([^<]+)</a>#s', $block, $m2)) {
                $title = trim(strip_tags($m2[1]));
            }
            if (!$title || !preg_match('/Putusan\s/i', $title)) {
                continue;
            }

            $detailUrl = null;
            if (preg_match('#https?://putusan3\.mahkamahagung\.go\.id/direktori/putusan/([a-z0-9]+)\.html#i', $block, $m)) {
                $detailUrl = self::MA_BASE . '/direktori/putusan/' . $m[1] . '.html';
            } elseif (preg_match('#/direktori/putusan/([a-z0-9]+)\.html#i', $block, $m2)) {
                $detailUrl = self::MA_BASE . '/direktori/putusan/' . $m2[1] . '.html';
            }

            $courtSlug = null;
            $courtName = null;
            if (preg_match('#/pengadilan/([a-z0-9-]+)\.html">([^<]+)</a>#i', $block, $m)) {
                $courtSlug = $m[1];
                $courtName = trim(strip_tags($m[2]));
            }
            if (!$courtSlug) {
                if (preg_match('/Putusan\s+([A-Z0-9\s]{2,30}?)\s+Nomor/i', $title, $mCourt)) {
                    $courtName = trim($mCourt[1]);
                }
            }

            $kategori = null;
            if (preg_match('#/direktori/index/pengadilan/[a-z0-9-]+/kategori/[a-z0-9-]+\.html">([^<]+)</a>#i', $block, $m)) {
                $kategori = trim(strip_tags($m[1]));
            } elseif (preg_match('/Nomor\s+([^\s]+)/', $title, $m3)) {
                $kategori = $this->categoryLabel('');
            }

            $dates = [
                'register' => null,
                'putus' => null,
                'upload' => null,
            ];
            // Tanggal pada halaman asli diapit tag HTML, e.g. <strong>Register :</strong> 07-03-2024
            if (preg_match('/Register[^0-9]{0,40}(\d{2}-\d{2}-\d{4})/', $block, $m)) {
                $dates['register'] = $m[1];
            }
            if (preg_match('/Putus[^0-9]{0,40}(\d{2}-\d{2}-\d{4})/', $block, $m)) {
                $dates['putus'] = $m[1];
            }
            if (preg_match('/Upload[^0-9]{0,40}(\d{2}-\d{2}-\d{4})/', $block, $m)) {
                $dates['upload'] = $m[1];
            }

            $snippet = '';
            // Baris para pihak: "Tanggal ... &#8212; Penggugat melawan Tergugat"
            if (preg_match('/Tanggal[^<]{0,80}?(?:&#8212;|&mdash;|—)\s*(.*?)(?=<br)/is', $block, $m)) {
                $snippet = trim(strip_tags(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            }

            $entries[] = [
                'nomor' => $title,
                'nomor_clean' => preg_replace('/^Putusan\s+.*?\s+Nomor\s+/i', '', $title) ?: $title,
                'court_slug' => $courtSlug,
                'court_name' => $courtName,
                'kategori' => $kategori,
                'register' => $dates['register'],
                'putus' => $dates['putus'],
                'upload' => $dates['upload'],
                'snippet' => $snippet,
                'detail_url' => $detailUrl,
            ];
        }

        return $entries;
    }

    /**
     * Ekstrak, dari halaman detail arsip, URL dokumen PDF asli.
     */
    public function extractPdfUrl(string $detailHtml): ?string
    {
        if (preg_match('#/direktori/download_file/([a-f0-9]+)/pdf/([a-z0-9]+)#i', $detailHtml, $m)) {
            return self::MA_BASE . '/direktori/download_file/' . $m[1] . '/pdf/' . $m[2];
        }

        return null;
    }

    /**
     * Ubah byte PDF menjadi teks via smalot/pdfparser.
     */
    public function pdfToText(string $bytes): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $text = $parser->parseContent($bytes)->getText();

            return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function jenisFromSlug(?string $slug): ?string
    {
        if (!$slug) {
            return null;
        }
        foreach (self::JENIS_PREFIX as $prefix => $jenis) {
            if (str_starts_with($slug, $prefix)) {
                return $jenis;
            }
        }

        return null;
    }

    private function parseDate(?string $dmy): ?Carbon
    {
        if (!$dmy) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-m-Y', $dmy);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Jalankan impor putusan sebuah pengadilan (PN) dari snapshot arsip.
     *
     * @return array{
     *   attempted:int, created:int, updated:int, with_text:int, no_text:int,
     *   pdf_fetched:int, errors:int, tahun_used:bool, entries:array<int,array{nomor:string,action:string,text:string,error:?string}>
     * }
     */
    public function import(
        string $pn,
        string $kategori = '',
        string $tahun = '',
        int $limit = 15,
        int $maxPages = 3,
        int $maxPdf = 5,
        bool $withPdf = false
    ): array {
        $stats = [
            'attempted' => 0,
            'created' => 0,
            'updated' => 0,
            'with_text' => 0,
            'no_text' => 0,
            'pdf_fetched' => 0,
            'errors' => 0,
            'tahun_used' => false,
            'entries' => [],
        ];

        $base = $this->courtBaseUrl($pn);
        $rows = $this->cdxLatestRows($base);
        if (!$rows) {
            $stats['errors'] = 1;

            return $stats;
        }

        $picked = $this->pickListingSnapshot($rows, $kategori, $tahun);
        if ($picked === null) {
            $stats['errors'] = 1;

            return $stats;
        }

        $stats['tahun_used'] = $picked['tahun_used'];
        $pageSnap = $picked['snap'];

        $pages = [$pageSnap];
        if ($maxPages > 1) {
            foreach (range(2, $maxPages) as $pageNo) {
                $pageUrl = preg_replace('#/[^/]+\.html$#', '', $pageSnap['original']) . '/page/' . $pageNo . '.html';
                $pageSnapHit = $this->cdxLatest($pageUrl, 'exact');
                if ($pageSnapHit === null) {
                    break;
                }
                $pages[] = $pageSnapHit;
            }
        }

        foreach ($pages as $snapshot) {
            $html = $this->fetchArchived($snapshot['original'], $snapshot['ts']);
            if ($html === null || !str_contains($html, 'spost')) {
                continue;
            }

            foreach ($this->parseListing($html) as $entry) {
                if ($stats['attempted'] >= $limit) {
                    break 2;
                }
                $stats['attempted']++;

                try {
                    $stats['entries'][] = $this->persistEntry($pn, $entry, $kategori, $withPdf, $maxPdf, $stats);
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $stats['entries'][] = [
                        'nomor' => $entry['nomor'],
                        'action' => 'error',
                        'text' => 'no',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return $stats;
    }

    private function cdxLatestRows(string $url): array
    {
        $key = 'pds_cdx_rows_' . md5($url);

        return Cache::remember($key, now()->addDays(7), function () use ($url) {
            try {
                $resp = Http::timeout(45)
                    ->retry(2, 500)
                    ->withHeaders(['User-Agent' => $this->ua()])
                    ->get(self::CDX_ENDPOINT, [
                        'url' => $url,
                        'matchType' => 'prefix',
                        'limit' => 500,
                        'collapse' => 'urlkey',
                        'fl' => 'timestamp,original',
                        'filter' => 'statuscode:200',
                    ]);

                if (!$resp->ok()) {
                    return [];
                }

                $lines = [];
                foreach (explode("\n", str_replace("\r", '', trim($resp->body()))) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = explode(' ', $line);
                    if (count($parts) >= 2) {
                        $lines[] = ['ts' => $parts[0], 'original' => $parts[1]];
                    }
                }

                return $lines;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function persistEntry(string $pn, array $entry, string $kategori, bool $withPdf, int $maxPdf, array &$stats): array
    {
        $jenis = $this->jenisFromSlug($entry['court_slug'] ?? null);
        $tanggalPutus = $this->parseDate($entry['putus'])
            ?? $this->parseDate($entry['register'])
            ?? $this->parseDate($entry['upload']);
        $tanggalRegister = $this->parseDate($entry['register']) ?? $tanggalPutus;

        if ($tanggalPutus === null) {
            return [
                'nomor' => $entry['nomor_clean'] ?? $entry['nomor'],
                'action' => 'skipped',
                'text' => 'no',
                'error' => 'tanggal putusan/register/upload tidak tersedia',
            ];
        }

        $golongan = $kategori !== '' ? $this->categoryLabel($kategori) : ($entry['kategori'] ?? $this->categoryLabel(''));

        $tingkat = 'Pertama';
        $kategoriLower = mb_strtolower((string) $golongan);
        if (str_contains($kategoriLower, 'kasasi')) {
            $tingkat = 'Kasasi';
        } elseif (str_contains($kategoriLower, 'banding')) {
            $tingkat = 'Banding';
        } elseif (str_contains($kategoriLower, 'peninjauan')) {
            $tingkat = 'Peninjauan Kembali';
        }

        $courtName = $entry['court_name'] ?? ('PN ' . ucwords(str_replace('-', ' ', $pn)));
        $values = [
            'jenis_pengadilan' => $jenis ?? 'PN',
            'nama_pengadilan' => $courtName,
            'tingkat_pengadilan' => $tingkat,
            'golongan_perkara' => $golongan,
            'klasifikasi_perkara' => $entry['kategori'] ?? $golongan,
            'tanggal_putusan' => $tanggalPutus,
            'tanggal_register' => $tanggalRegister,
            'para_pihak' => $entry['snippet'] ?: null,
            'ringkasan_putusan' => $entry['snippet'] ?: null,
            'status_putusan' => 'Berlaku',
            'sumber_url' => $entry['detail_url'],
            'is_published' => false,
        ];
        $values = array_filter($values, fn ($v) => $v !== null && $v !== '');

        $nomor = $entry['nomor_clean'] ?? $entry['nomor'];
        $model = \App\Models\Putusan::updateOrCreate(['nomor_putusan' => $nomor], $values);
        $action = $model->wasRecentlyCreated ? 'created' : 'updated';
        if ($model->wasRecentlyCreated) {
            $stats['created']++;
        } else {
            $stats['updated']++;
        }

        $withText = 'no';
        if ($withPdf && $stats['pdf_fetched'] < $maxPdf && $entry['detail_url']) {
            $pdfUrl = null;
            $detailSnap = $this->cdxLatest($entry['detail_url'], 'exact');
            if ($detailSnap) {
                $detailHtml = $this->fetchArchived($entry['detail_url'], $detailSnap['ts']);
                if ($detailHtml) {
                    $pdfUrl = $this->extractPdfUrl($detailHtml);
                }
            }

            if ($pdfUrl) {
                $pdfSnap = $this->cdxLatest($pdfUrl, 'exact');
                if ($pdfSnap) {
                    $bytes = $this->fetchArchived($pdfUrl, $pdfSnap['ts']);
                    if ($bytes !== null && str_starts_with($bytes, '%PDF')) {
                        $text = $this->pdfToText($bytes);
                        if (mb_strlen($text) > 300) {
                            $model->forceFill([
                                'isi_putusan' => $text,
                                'hash_content' => sha1($text),
                            ])->save();
                            $stats['pdf_fetched']++;
                            $withText = 'yes';
                        } else {
                            $stats['no_text']++;
                        }
                    }
                }
            }

            if ($withText === 'no') {
                $stats['no_text']++;
            } else {
                $stats['with_text']++;
            }
        } elseif (!$withPdf) {
            $stats['no_text']++;
        }

        return [
            'nomor' => $nomor,
            'action' => $action,
            'text' => $withText,
            'error' => null,
        ];
    }
}