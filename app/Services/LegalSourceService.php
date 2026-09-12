<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Live retrieval dari sumber-sumber hukum resmi Indonesia (pemerintah pusat & daerah).
 *
 * Strategi (multi-sumber dengan validasi silang):
 * 1. peraturan.bpk.go.id (search HTML + halaman detail)
 * 2. Google Search dengan filter site:go.id (pembanding/validasi silang)
 * 3. DuckDuckGo HTML dengan filter site:go.id (fallback)
 *
 * Hasil di-cache 7 hari agar tidak membebani situs pemerintah.
 *
 * PENTING: Kelas ini TIDAK menggunakan database lokal aplikasi.
 * Semua regulasi HARUS berasal dari website resmi pemerintah.
 */
class LegalSourceService
{
    /** Jeda antar request ke situs pemerintah (ms) untuk bersikap sopan. */
    private const POLITE_DELAY = 300;

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
    ];

    /**
     * Ambil konteks hukum live untuk sebuah pertanyaan.
     * Semua data berasal dari website resmi pemerintah.
     *
     * @return array{context: string, sources: array<int, array{url:string,title:string,status:string|null}>}
     */
    public function getContext(string $query, int $maxResults = 4): array
    {
        $results = $this->search($query, $maxResults + 3);

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

            $title = $detail['meta']['title'] ?? $r['title'];
            $status = $detail['meta']['status'] ?? null;
            $header = trim($title . ($status ? " [Status: $status]" : ''));

            $context[] = "\n[SUMBER RESMI: " . $header . "]\nURL: {$r['url']}\n"
                . mb_substr($detail['text'], 0, 8000);

            $sources[] = [
                'url' => $r['url'],
                'title' => $title,
                'status' => $status,
            ];
            $fetched++;
        }

        return [
            'context' => implode("\n", $context),
            'sources' => $sources,
        ];
    }

    /**
     * Cari daftar regulasi dari MULTI-SUMBER dengan validasi silang.
     * Sumber: BPK (terstruktur), DuckDuckGo (fallback)
     *
     * @return array<int, array{title:string,url:string,source:string}>
     */
    public function search(string $query, int $limit = 6): array
    {
        $cacheKey = 'legalsrc_search_v2_' . md5(mb_strtolower(trim($query)));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($query, $limit) {
            $collected = [];

            // Sumber 1: BPK (terstruktur)
            try {
                usleep(self::POLITE_DELAY * 1000);
                foreach ($this->searchBpk($this->normalizeQuery($query)) as $r) {
                    $r['_src'] = 'bpk';
                    $r['_confidence'] = 3; // tinggi = sumber terstruktur
                    $collected[] = $r;
                }
            } catch (\Exception $e) {
                Log::debug('LegalSourceService: BPK search failed - ' . $e->getMessage());
            }

            // Sumber 2: DuckDuckGo (fallback)
            try {
                usleep(self::POLITE_DELAY * 1000);
                foreach ($this->searchDuckDuckGo($query) as $r) {
                    $r['_src'] = 'ddg';
                    $r['_confidence'] = 1; // fallback
                    $collected[] = $r;
                }
            } catch (\Exception $e) {
                Log::debug('LegalSourceService: DDG search failed - ' . $e->getMessage());
            }

            // Deduplicate by URL
            $seen = [];
            $deduped = [];
            foreach ($collected as $r) {
                $url = $this->normalizeUrl($r['url'] ?? '');
                if ($url === '' || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;
                $deduped[] = $r;
            }

            // Ranking: confidence tinggi dulu, lalu prioritas untuk peraturan bernama
            $isNamed = preg_match('/\b(?:No\.?|Nomor|UU|PP|PERMEN|PERDA|PERPRES|UU\.?)\b/i', $query);

            usort($deduped, function ($a, $b) use ($isNamed) {
                // Confidence desc
                $confA = $a['_confidence'] ?? 0;
                $confB = $b['_confidence'] ?? 0;
                if ($confA !== $confB) {
                    return $confB <=> $confA;
                }
                return 0;
            });

            $out = [];
            foreach ($deduped as $r) {
                if (count($out) >= $limit) {
                    break;
                }
                $out[] = [
                    'title' => trim($r['title'] ?? $r['url']),
                    'url' => trim($r['url']),
                    'source' => $r['_src'] ?? 'unknown',
                ];
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
        $cacheKey = 'legalsrc_fetch_v2_' . md5($url);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($url) {
            $html = $this->scrapeHtml($url);
            if (empty($html)) {
                return ['text' => '', 'meta' => []];
            }

            $meta = $this->extractMetaFromHtml($html, $url);
            $text = $this->stripHtml($html);

            // Fokus pada area konten substantif
            $contentFocus = $this->extractContentArea($html, $text);

            return [
                'text' => mb_substr($contentFocus, 0, 8000),
                'meta' => $meta,
            ];
        });
    }

    /**
     * Normalisasi URL untuk deduplicasi
     */
    protected function normalizeUrl(string $url): string
    {
        return strtolower(trim($url));
    }

    /**
     * Bersihkan query untuk sumber terstruktur (BPK).
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
            if (isset($seen[$id]) || count($links) >= 10) {
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
     * Cari via Google Search dengan filter site:go.id.
     * DIAKTIFKAN untuk validasi silang (tersedia jika tidak diblokir).
     */
    protected function searchGoogle(string $query): array
    {
        $q = 'site:*.go.id ' . $query . ' (peraturan OR undang-undang OR perda OR permendagri OR permen OR pp OR perpres)';
        $url = 'https://www.google.com/search?q=' . urlencode($q) . '&num=10&hl=id';

        $html = $this->scrapeHtml($url);
        if (empty($html)) {
            return [];
        }

        $links = [];

        // Google search result links
        if (preg_match_all('/<a\s+href="\/url\?q=([^"&]+)[^"]*"/', $html, $m)) {
            foreach ($m[1] as $enc) {
                $target = urldecode($enc);
                // Hanya situs resmi pemerintah .go.id
                if (!preg_match('/^https?:\/\/[a-z0-9.-]*\.go\.id/i', $target)
                    && !str_contains($target, '.go.id/')) {
                    continue;
                }
                if (count($links) >= 10) {
                    break;
                }
                $links[] = ['title' => $target, 'url' => $target];
            }
        }

        // Extract titles from Google results
        if (!empty($links) && preg_match_all('/<h3[^>]*>(.*?)<\/h3>/is', $html, $tm)) {
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
     * Cari di DuckDuckGo HTML dengan filter situs resmi pemerintah (site:go.id).
     * Fallback jika Google gagal.
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
                if (!preg_match('/^https?:\/\/[a-z0-9.-]*\.go\.id/i', $target)
                    && !str_contains($target, '.go.id/')) {
                    continue;
                }
                if (count($links) >= 8) {
                    break;
                }
                $links[] = ['title' => $target, 'url' => $target];
            }
        }

        // Extract titles
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
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => $agent,
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'DNT' => '1',
                        'Connection' => 'keep-alive',
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
     * Ekstrak metadata termasuk status peraturan (Berlaku/Dicabut).
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

        // Status peraturan - berbagai pattern dari situs JDIH/BPK
        $statusPatterns = [
            '/Status\s*Peraturan\s*:?\s*<\/[^>]+>\s*([^<\n]{3,80})/is',
            '/Status\s*:?\s*<\/[^>]+>\s*<(?:td|span|div)[^>]*>\s*(Berlaku|Dicabut|Tidak\s*Berlaku|Diubah)/is',
            '/>(\s*Berlaku\s*|\s*Dicabut\s*|\s*Tidak\s*Berlaku\s*|\s*Diubah\s*)</i',
            '/class="[^"]*status[^"]*"[^>]*>\s*(Berlaku|Dicabut|Tidak\s*Berlaku|Diubah)/i',
            '/>\s*(Berlaku|Dicabut|Tidak\s*Berlaku|Diubah)\s*<\/span>/i',
        ];

        foreach ($statusPatterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $status = trim(strip_tags($m[1]));
                if (in_array(strtolower($status), ['berlaku', 'dicabut', 'tidak berlaku', 'diubah'])) {
                    $meta['status'] = $status;
                    break;
                }
            }
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
     * Fokus pada area substantif halaman detail regulasi.
     */
    protected function extractContentArea(string $html, string $fullText): string
    {
        $focus = '';

        // Coba berbagai pattern konten
        $patterns = [
            '/<section[^>]*(?:detail-hukum|section-content|content)[^>]*>(.*?)<\/section>/is',
            '/<div[^>]*class="[^"]*(?:detail-hukum|table-document|content|entry-content)[^"]*"[^>]*>(.*?)(?:<div\s+class="row\s+share-only"|<\/body>|<footer)/is',
            '/<article[^>]*>(.*?)<\/article>/is',
            '/<main[^>]*>(.*?)<\/main>/is',
            '/<div[^>]*id="[^"]*(?:content|main)[^"]*"[^>]*>(.*?)(?:<\/div>\s*){0,3}<footer/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $focus = $this->stripHtml($m[1]);
                if (mb_strlen($focus) > 200) {
                    break;
                }
            }
        }

        if (mb_strlen($focus) < 200) {
            $focus = $fullText;
        }

        return $focus;
    }
}
