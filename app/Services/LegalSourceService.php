<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Live retrieval dari sumber-sumber hukum resmi Indonesia (pemerintah pusat & daerah).
 *
 * Strategi (luas -> deterministik -> selalu ada jawaban):
 * 1. peraturan.bpk.go.id (search HTML + halaman detail + PDF)
 * 2. DuckDuckGo HTML dengan filter site:go.id (mengindeks seluruh situs resmi,
 *    termasuk jdih kementerian/lembaga dan JDIH daerah yang tidak teragregasi)
 *
 * Hasil di-cache 7 hari agar tidak membebani situs pemerintah.
 */
class LegalSourceService
{
    /** Jeda antar request ke situs pemerintah (ms) untuk bersikap sopan. */
    private const POLITE_DELAY = 250;

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0 Safari/537.36',
    ];

    /**
     * Ambil konteks hukum live untuk sebuah pertanyaan.
     *
     * @return array{context: string, sources: array<int, array{url:string,title:string}>}
     */
    public function getContext(string $query, int $maxResults = 3): array
    {
        $results = $this->search($query, $maxResults + 2);

        $context = [];
        $sources = [];
        $fetched = 0;

        foreach ($results as $r) {
            if ($fetched >= $maxResults) {
                break;
            }

            $detail = $this->fetch($r['url']);
            if (empty($detail['text']) && empty($detail['meta'])) {
                continue;
            }

            $header = trim(($detail['meta']['title'] ?? $r['title']) . ' ' . ($detail['meta']['status'] ?? ''))
                ?: ($r['title'] ?? $r['url']);

            $context[] = "\n[SUMBER RESMI: " . $header . "]\nSumber: {$r['url']}\n"
                . mb_substr($detail['text'], 0, 6000);

            $sources[] = ['url' => $r['url'], 'title' => $header];
            $fetched++;
        }

        return [
            'context' => implode("\n", $context),
            'sources' => $sources,
        ];
    }

    /**
     * Cari daftar regulasi dari beberapa sumber resmi.
     *
     * @return array<int, array{title:string,url:string}>
     */
    public function search(string $query, int $limit = 5): array
    {
        $cacheKey = 'legalsrc_search_' . md5(mb_strtolower(trim($query)));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($query, $limit) {
            // Selalu jalankan SEMUA sumber (jangan berhenti di sumber pertama)
            // agar hasil dari situs resmi non-agregator (via web search) ikut terjaring.
            $collected = [];

            foreach ([
                ['src' => 'web', 'terms' => $query, 'callable' => fn ($t) => $this->searchDuckDuckGo($t)],
                ['src' => 'bpk', 'terms' => $this->normalizeQuery($query), 'callable' => fn ($t) => $this->searchBpk($t)],
            ] as $item) {
                try {
                    usleep(self::POLITE_DELAY * 1000);
                    foreach ($item['callable']($item['terms']) as $r) {
                        $r['_src'] = $item['src'];
                        $collected[] = $r;
                    }
                } catch (\Exception $e) {
                    // sumber ini gagal -> lanjut ke sumber berikutnya
                }
            }

            // dedupe by URL (pertahankan yang pertama muncul)
            $seen = [];
            $deduped = [];
            foreach ($collected as $r) {
                $url = trim($r['url'] ?? '');
                if ($url === '' || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;
                $deduped[] = $r;
            }

            // Ranking: hasil web (.go.id dari mesin pencari) diutamakan karena lebih
            // akurat untuk peraturan dengan nama spesifik; BPK tetap disertakan sebagai
            // sumber terstruktur. Untuk query normal (bukan nama peraturan) BPK diutamakan.
            $isNamed = preg_match('/\b(?:No\.?|Nomor|UU|PP|PERMEN|PERDA|PERPRES)\b/i', $query);
            $score = function ($r) use ($isNamed) {
                $isWeb = ($r['_src'] ?? '') === 'web';
                return $isNamed && $isWeb ? 0 : ($isWeb ? 1 : 2);
            };

            usort($deduped, fn ($a, $b) => $score($a) <=> $score($b));

            $out = [];
            foreach ($deduped as $r) {
                if (count($out) >= $limit) {
                    break;
                }
                $out[] = ['title' => trim($r['title'] ?? $r['url']), 'url' => trim($r['url'])];
            }

            return $out;
        });
    }

    /**
     * Ambil teks + metadata sebuah halaman detail regulasi resmi.
     *
     * @return array{text:string, meta:array<string,string>}
     */
    public function fetch(string $url): array
    {
        $cacheKey = 'legalsrc_fetch_' . md5($url);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($url) {
            $html = $this->scrapeHtml($url);
            if (empty($html)) {
                return ['text' => '', 'meta' => []];
            }

            $meta = $this->extractMetaFromHtml($html, $url);
            $text = $this->stripHtml($html);

            // Fokus pada area konten bila halaman memiliki struktur tabel metadata
            $contentFocus = $this->extractContentArea($html, $text);

            return [
                'text' => mb_substr($contentFocus, 0, 6000),
                'meta' => $meta,
            ];
        });
    }

    /**
     * Bersihkan query untuk sumber terstruktur (BPK): buang kata tanya saja,
     * pertahankan seluruh konteks agar peringkat BPK/DDG akurat.
     */
    protected function normalizeQuery(string $query): string
    {
        $terms = preg_replace('/\b(apa|apakah|bagaimana|mana|isi|pada|dari|yang|untuk|dengan|kepada|kapan|siapa|mengapa|kenapa|dimana|di\s+mana|jelaskan|sebutkan|tolong|saya|anda|kami|jelaskan\s+dengan|hal\s+apa|tentang\s+apa)\b/i', ' ', $query);
        $terms = preg_replace('/\s+/', ' ', trim($terms));

        return $terms !== '' ? $terms : $query;
    }

    /**
     * Cari di peraturan.bpk.go.id (scrape HTML halaman search).
     */
    protected function searchBpk(string $query): array
    {
        $url = 'https://peraturan.bpk.go.id/Search?keywords=' . urlencode($query);
        $html = $this->scrapeHtml($url);
        if (empty($html)) {
            return [];
        }

        $links = [];
        preg_match_all('/href="\/Details\/(\d+)\/([^"]+)"/', $html, $m);
        if (empty($m[1])) {
            return [];
        }

        $seen = [];
        foreach ($m[1] as $i => $id) {
            if (isset($seen[$id]) || count($links) >= 8) {
                continue;
            }
            $seen[$id] = true;
            $slug = rawurldecode($m[2][$i] ?? '');
            $title = ucwords(str_replace('-', ' ', $slug));
            $links[] = [
                'title' => $title,
                'url' => 'https://peraturan.bpk.go.id/Details/' . $id . '/' . $slug,
            ];
        }

        return $links;
    }

    /**
     * Cari di DuckDuckGo HTML dengan filter situs resmi pemerintah (site:go.id).
     */
    protected function searchDuckDuckGo(string $query): array
    {
        $q = 'site:*.go.id ' . $query . ' (peraturan OR undang-undang OR perda OR permendagri OR permen)';
        $url = 'https://html.duckduckgo.com/html/?q=' . urlencode($q);

        $html = $this->scrapeHtml($url);
        if (empty($html)) {
            return [];
        }

        $links = [];
        if (preg_match_all('/uddg=([^"&]+)/', $html, $m)) {
            foreach ($m[1] as $enc) {
                $target = urldecode($enc);
                // hanya situs resmi pemerintah go.id
                if (
                    !preg_match('/^https?:\/\/[a-z0-9.-]*\.go\.id/i', $target)
                    && !str_contains($target, '.go.id/')
                ) {
                    continue;
                }
                if (count($links) >= 8) {
                    break;
                }
                $links[] = ['title' => $target, 'url' => $target];
            }
        }

        // isi judul dari result title bila tersedia
        if (!empty($links) && preg_match_all('/<a[^>]+class="result__a"[^>]*>(.*?)<\/a>/is', $html, $tm)) {
            foreach ($tm[1] as $i => $rawTitle) {
                if (!isset($links[$i])) {
                    break;
                }
                $links[$i]['title'] = trim(strip_tags($rawTitle));
            }
        }

        return $links;
    }

    /**
     * Scrape HTML dengan beberapa fallback user-agent.
     */
    protected function scrapeHtml(string $url): string
    {
        foreach ($this->userAgents as $agent) {
            try {
                $response = Http::timeout(12)
                    ->withHeaders([
                        'User-Agent' => $agent,
                        'Accept' => 'text/html,application/xhtml+xml',
                        'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $body = $response->body();
                    if (str_contains(mb_strtolower($body), '<html') || str_contains(mb_strtolower($body), '<!doctype')) {
                        return $body;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return '';
    }

    /**
     * Ekstrak metadata sederhana (title/webpage_name/og:description).
     *
     * @return array<string,string>
     */
    protected function extractMetaFromHtml(string $html, string $url): array
    {
        $meta = [];
        if (preg_match('/<title[^>]*>\s*(.*?)\s*<\/title>/is', $html, $m)) {
            $meta['title'] = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
        }
        if (preg_match('/<meta\s+name="[^"]*description[^"]*"\s+content="([^"]*)"/is', $html, $m)) {
            $meta['description'] = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
        }
        if (preg_match('/og:description"\s+content="([^"]*)"/is', $html, $m)) {
            $meta['description'] = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
        }
        // Status peraturan (Berlaku/Dicabut) di JDIH
        if (preg_match('/Status\s*Peraturan\s*:?\s*<\/[^>]+>\s*([^<\n]{3,80})/is', $html, $m)) {
            $meta['status'] = trim(strip_tags($m[1]));
        } elseif (preg_match('/>(\s*Berlaku\s*|\s*Dicabut\s*|\s*Tidak\s*Berlaku\s*)</i', $html, $m)) {
            $meta['status'] = trim(strip_tags($m[1]));
        }
        return $meta;
    }

    /**
     * Buang script/style/tag -> teks bersih.
     */
    protected function stripHtml(string $html): string
    {
        $text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html);
        $text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $text);
        $text = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', ' ', $text);
        $text = preg_replace('/<!--.*?-->/s', ' ', $text);
        $text = preg_replace('/<(br|p|div|tr|li|h[1-6])[^>]*>/i', "\n", $text);
        $text = preg_replace('/<[^>]+>/', ' ', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Fokus pada area substantif halaman detail regulasi (judul metadata + abstrak).
     */
    protected function extractContentArea(string $html, string $fullText): string
    {
        $focus = '';
        if (preg_match('/<section[^>]*(?:detail-hukum|section-content)[^>]*>(.*?)<\/section>/is', $html, $m)
            || preg_match('/<div[^>]*class="[^"]*(?:detail-hukum|table-document)[^"]*"[^>]*>(.*?)(?:<div\s+class="row\s+share-only"|<\/body>)/is', $html, $m)
        ) {
            $focus = $this->stripHtml($m[1]);
        }
        if (mb_strlen($focus) < 200) {
            $focus = $fullText;
        }
        return $focus;
    }
}