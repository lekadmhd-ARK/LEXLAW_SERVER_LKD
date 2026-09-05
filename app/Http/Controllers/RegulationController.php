<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class RegulationController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // 1. Jika ada query pencarian
        $localResults = collect();
        if ($request->filled('q')) {
            $q = $request->q;
            $localResults = Regulation::where('tenant_id', $tenantId)->where(function($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('number', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('short_description', 'like', "%{$q}%")
                  ->orWhere('content_text', 'like', "%{$q}%");
            })->get();
        } else {
            // Jika tidak ada pencarian, tampilkan semua dari DB
            $localResults = Regulation::where('tenant_id', $tenantId)->latest()->get();
        }

        // 2. Jika ada query 'q' dan lokal kosong, cari via 9Router gateway
        $webSearchPerformed = false;
        if ($request->filled('q') && $localResults->isEmpty()) {
            $webSearchPerformed = true;
            $webResults = $this->searchVia9Router($request->q);

            // Simpan hasil 9Router ke DB
            foreach ($webResults as $w) {
                $exists = Regulation::where('tenant_id', $tenantId)
                    ->where('title', $w['title'])
                    ->where('number', $w['number'])
                    ->where('year', $w['year'])
                    ->exists();

                if (!$exists) {
                    Regulation::create(array_merge($w, [
                        'tenant_id' => $tenantId,
                        'company_id' => 1,
                        'created_by' => 1,
                        'is_active' => true,
                    ]));
                }
            }
            // Fetch ulang dari DB agar semua berupa Model instance (bukan array)
            $localResults = Regulation::where('tenant_id', $tenantId)->where(function($w2) use ($request) {
                $w2->where('title', 'like', "%{$request->q}%")
                   ->orWhere('short_description', 'like', "%{$request->q}%");
            })->get();
        }

        // 3. Collection hanya berisi Model (semua dari DB) — tidak ada merge array mentah
        $allResults = $localResults;

        // 4. Filter Hierarki
        if ($request->filled('hierarchy')) {
            $allResults = $allResults->where('hierarchy_level', $request->hierarchy);
        }

        // 5. Filter Sektor
        if ($request->filled('sector')) {
            $allResults = $allResults->where('category_sector', $request->sector);
        }

        // 6. Filter Status Active
        if ($request->has('active') && $request->active !== '') {
            $allResults = $allResults->where('is_active', (bool)$request->active);
        }

        // 7. Manual Pagination untuk Collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentItems = $allResults->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $regulations = new LengthAwarePaginator(
            $currentItems,
            $allResults->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Header Stats
        $stats = [
            'total' => Regulation::where('tenant_id', $tenantId)->count(),
            'uu' => Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '1')->count(),
            'pp' => Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '2')->count(),
            'perpres' => Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '3')->count(),
            'active' => Regulation::where('tenant_id', $tenantId)->where('is_active', true)->count(),
        ];

        return view('regulations.index', compact('regulations', 'stats', 'webSearchPerformed') + ['searchQuery' => $request->q ?? '']);
    }

    protected function searchVia9Router($query)
    {
        try {
            $key = env('AI_API_KEY');
            $base = env('AI_BASE_URL', 'http://127.0.0.1:20128/v1');
            // Coba beberapa model yang reliable
            $models = ['gemini-3.5-flash', 'gemini-3.6-flash', 'gemini-3-flash-preview', 'gemini-3.1-flash-lite-preview', 'gemini-3-6-flash'];

            foreach ($models as $model) {
                $response = Http::timeout(60)->withToken($key)
                    ->post($base . '/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda adalah asisten hukum Indonesia. Cari peraturan perundang-undangan Indonesia yang terkait query. Kembalikan HANYA dalam format JSON array (tanpa markdown codeblock) dengan object berisi field: title, number, year, hierarchy_level (1=UU, 2=PP, 3=Perpres, 4=PerMen, 5=Perda), category_sector (ketenagakerjaan/perpajakan/perusahaan/agraria/teknologi/lainnya), short_description, source_url. Maksimal 3 hasil.'],
                            ['role' => 'user', 'content' => "Cari peraturan terkait: \"{$query}\". Berikan jawaban dalam format JSON array saja."],
                        ],
                        'temperature' => 0.3,
                        'stream' => false,
                        'max_tokens' => 2000,
                    ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content') ?? '';
                    if (empty(trim($content))) continue;

                    // Bersihkan markdown jika ada
                    $content = preg_replace('/^```json\s*/i', '', $content);
                    $content = preg_replace('/```$/i', '', $content);
                    $content = trim($content);

                    $results = json_decode($content, true);

                    if (is_array($results) && count($results) > 0) {
                        return collect($results)->map(function($r) use ($query) {
                            $hl = $r['hierarchy_level'] ?? '5';
                            if (is_string($hl)) {
                                if (str_contains(strtolower($hl), 'undang')) $hl = '1';
                                elseif (str_contains(strtolower($hl), 'pemerintah')) $hl = '2';
                                elseif (str_contains(strtolower($hl), 'presiden')) $hl = '3';
                                elseif (str_contains(strtolower($hl), 'menteri')) $hl = '4';
                                else $hl = '5';
                            }
                            return [
                                'title' => $r['title'] ?? $query,
                                'number' => (string)($r['number'] ?? preg_replace('/\D+/', '', $query)),
                                'year' => (string)($r['year'] ?? date('Y')),
                                'category_sector' => strtolower($r['category_sector'] ?? 'lainnya'),
                                'hierarchy_level' => (string)$hl,
                                'is_active' => true,
                                'short_description' => $r['short_description'] ?? "Ditemukan via 9Router: {$query}",
                                'source_url' => $r['source_url'] ?? null,
                            ];
                        });
                    }
                }
            }
        } catch (\Exception $e) {
            // Return empty jika gagal
        }

        return collect();
    }

    /**
     * Fetch regulasi metadata dari URL JDIH / Situs Resmi
     * Strategi: scrape HTML dulu (akurat untuk title/num/year/date/status/pdf),
     * lalu kirim teks bersih ke AI untuk abstrak + isi + kategori sektor.
     */
    public function fetchFromJdihUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:500',
        ]);

        $url = $request->url;

        // 1. Scrape HTML langsung dari sumber
        $html = $this->scrapeHtml($url);

        if (empty($html)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengakses URL. Pastikan URL publik dan dapat diakses.',
            ], 500);
        }

        // 2. Ekstrak metadata dari HTML (heuristik, tanpa AI)
        $meta = $this->extractMetadataFromHtml($html, $url);

        // 3. Ekstrak abstrak & isi via AI (pakai teks HTML yang sudah dibersihkan)
        $text = $this->stripHtml($html);
        $text = mb_substr($text, 0, 12000); // batasi panjang

        $ai = $this->extractContentViaAi($text, $url);

        // 4. Gabungkan: metadata heuristik lebih akurat, AI untuk konten
        $data = array_merge($meta, [
            'short_description' => $ai['short_description'] ?? $meta['short_description'],
            'content_text' => $ai['content_text'] ?? ($meta['content_text'] ?? ''),
            'category_sector' => $ai['category_sector'] ?? $meta['category_sector'],
            'is_active' => $ai['is_active'] ?? $meta['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Scrape HTML dari URL dengan beberapa fallback user-agent
     */
    protected function scrapeHtml($url)
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        ];

        foreach ($agents as $agent) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['User-Agent' => $agent, 'Accept' => 'text/html'])
                    ->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    if (strlen($html) > 200) {
                        return $html;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return '';
    }

    /**
     * Ekstrak metadata dari HTML (title, nomor, tahun, tanggal, status, pdf)
     */
    protected function extractMetadataFromHtml($html, $url)
    {
        $text = $this->stripHtml($html);
        $meta = [
            'title' => '',
            'number' => '',
            'year' => '',
            'hierarchy_level' => '5',
            'category_sector' => 'lainnya',
            'penetapan_date' => null,
            'pengundangan_date' => null,
            'is_active' => true,
            'short_description' => '',
            'content_text' => '',
            'source_url' => $url,
            'pdf_url' => null,
        ];

        // Title: dari <title> atau meta og:title
        if (preg_match('/<title[^>]*>\s*(.*?)\s*<\/title>/is', $html, $m)) {
            $meta['title'] = trim(html_entity_decode($m[1]));
        } elseif (preg_match('/property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
            $meta['title'] = trim(html_entity_decode($m[1]));
        }

        // Description: dari meta description
        if (preg_match('/name="description"\s+content="([^"]+)"/i', $html, $m)) {
            $meta['short_description'] = trim(html_entity_decode($m[1]));
        } elseif (preg_match('/property="og:description"\s+content="([^"]+)"/i', $html, $m)) {
            $meta['short_description'] = trim(html_entity_decode($m[1]));
        }

        // Nomor: pola "No. X" atau "Nomor X"
        if (preg_match('/\b(?:No\.?|Nomor)\s*(\d+)\s*Tahun\s*(\d{4})/i', $text, $m)) {
            $meta['number'] = $m[1];
            $meta['year'] = $m[2];
        } elseif (preg_match('/\b(?:UU|PP|Perpres|Peraturan)\s*(?:No\.?\s*)?(\d+)\s*Tahun\s*(\d{4})/i', $text, $m)) {
            $meta['number'] = $m[1];
            $meta['year'] = $m[2];
        }

        // Hierarki: dari judul / teks
        if (preg_match('/Undang-?Undang|UU\s*No/i', $text)) {
            $meta['hierarchy_level'] = '1';
        } elseif (preg_match('/Peraturan\s+Pemerintah|\bPP\s*No/i', $text)) {
            $meta['hierarchy_level'] = '2';
        } elseif (preg_match('/Peraturan\s+Presiden|Perpres/i', $text)) {
            $meta['hierarchy_level'] = '3';
        } elseif (preg_match('/Peraturan\s+Menteri|Keputusan\s+Menteri|Permen|PerMen/i', $text)) {
            $meta['hierarchy_level'] = '4';
        } else {
            $meta['hierarchy_level'] = '5';
        }

        // Tanggal penetapan/pengundangan - ambil semua teks setelah label hingga tag HTML
        if (preg_match('/Tanggal\s+Penetapan\b[^<]*<\/div>\s*<div[^>]*>.*?<[^>]*>\s*([^<\r\n]+(?:\s+[^<\r\n]+)*)/is', $html, $m)) {
            $meta['penetapan_date'] = $this->parseDate(trim($m[1]));
        }
        if (preg_match('/Tanggal\s+Pengundangan\b[^<]*<\/div>\s*<div[^>]*>.*?<[^>]*>\s*([^<\r\n]+(?:\s+[^<\r\n]+)*)/is', $html, $m)) {
            $meta['pengundangan_date'] = $this->parseDate(trim($m[1]));
        }
        // Fallback: cari pola tanggal langsung di seluruh teks
        if (!$meta['penetapan_date'] && preg_match('/(\d{1,2}\s+(?:[A-Za-z]*uari|[A-Z][a-z]+)\s+\d{4})/', $text, $m)) {
            $meta['penetapan_date'] = $this->parseDate($m[1]);
        }

        // Status
        if (preg_match('/(dicabut|tidak\s+berlaku|diubah\s+oleh)/i', $text)) {
            $meta['is_active'] = false;
        }

        // PDF URL: dari link .pdf
        if (preg_match('/href="([^"]+\.pdf)"[^>]*/i', $html, $m)) {
            $pdf = html_entity_decode($m[1]);
            if (str_starts_with($pdf, '/')) {
                $parsed = parse_url($url);
                $pdf = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $pdf;
            }
            $meta['pdf_url'] = $pdf;
        } elseif (preg_match('/\b(https?:\/\/[^"\s]+\.pdf)/i', $html, $m)) {
            $meta['pdf_url'] = $m[1];
        }

        return $meta;
    }

    /**
     * Parse tanggal ke format Y-m-d (mendukung "17 Juni 2026", "17/06/2026", "2026-06-17")
     */
    protected function parseDate($str)
    {
        $str = trim($str);
        $bulan = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        // Format: 17 Juni 2026
        if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $str, $m)) {
            $bulanNum = $bulan[strtolower($m[2])] ?? null;
            if ($bulanNum) {
                return sprintf('%04d-%02d-%02d', $m[3], $bulanNum, $m[1]);
            }
        }

        // Format: 17/06/2026 atau 17-06-2026
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Format: 2026-06-17
        if (preg_match('/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // Format: 17 Juni 2026 (dari HTML BPK)
        if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $str, $m)) {
            $bulanNum = $bulan[strtolower($m[2])] ?? null;
            if ($bulanNum) {
                return sprintf('%04d-%02d-%02d', $m[3], $bulanNum, $m[1]);
            }
        }

        return null;
    }

    /**
     * Bersihkan HTML menjadi teks
     */
    protected function stripHtml($html)
    {
        $text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html);
        $text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $text);
        $text = preg_replace('/<[^>]+>/', ' ', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Ekstrak abstrak & isi via AI (9Router)
     */
    protected function extractContentViaAi($text, $url)
    {
        if (empty($text)) {
            return ['short_description' => '', 'content_text' => '', 'category_sector' => 'lainnya', 'is_active' => true];
        }

        $key = env('AI_API_KEY');
        $base = env('AI_BASE_URL', 'http://127.0.0.1:20128/v1');
        $models = ['gemini-3.5-flash', 'gemini-3.6-flash', 'gemini-3-flash-preview', 'gemini-3.1-flash-lite-preview', 'gemini-3-6-flash'];

        foreach ($models as $model) {
            try {
                $response = Http::timeout(60)->withToken($key)
                    ->post($base . '/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda adalah asisten hukum Indonesia. Dari teks halaman peraturan berikut, ekstrak dan kembalikan HANYA format JSON object dengan field: short_description (abstrak/ringkasan 1-2 kalimat), content_text (isi pokok/batang tubuh peraturan, boleh diringkas tapi tetap akurat), category_sector (ketenagakerjaan/perpajakan/perusahaan/agraria/teknologi/lainnya), is_active (boolean, true jika masih berlaku). Gunakan bahasa Indonesia.'],
                            ['role' => 'user', 'content' => "Teks halaman (URL: {$url}):\n\n{$text}"],
                        ],
                        'temperature' => 0.2,
                        'stream' => false,
                        'max_tokens' => 2500,
                    ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content') ?? '';
                    if (empty(trim($content))) continue;

                    $content = preg_replace('/^```json\s*/i', '', $content);
                    $content = preg_replace('/```$/i', '', $content);
                    $content = trim($content);

                    $data = json_decode($content, true);
                    if (is_array($data)) {
                        return [
                            'short_description' => $data['short_description'] ?? '',
                            'content_text' => $data['content_text'] ?? '',
                            'category_sector' => strtolower($data['category_sector'] ?? 'lainnya'),
                            'is_active' => $data['is_active'] ?? true,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return ['short_description' => '', 'content_text' => '', 'category_sector' => 'lainnya', 'is_active' => true];
    }

    public function create()
    {
        $allRegs = Regulation::where('tenant_id', auth()->user()->tenant_id)->get();
        return view('regulations.create', compact('allRegs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'number' => 'nullable|max:100',
            'year' => 'nullable|digits:4',
            'hierarchy_level' => 'required|in:1,2,3,4,5',
            'category_sector' => 'nullable|in:ketenagakerjaan,perpajakan,perusahaan,agraria,teknologi,lainnya',
            'status' => 'required|in:draft,active,archived,revoked',
            'is_active' => 'boolean',
            'derogat_legi_id' => 'nullable|exists:regulations,id',
            'penetapan_date' => 'nullable|date',
            'pengundangan_date' => 'nullable|date',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'source_url' => 'nullable|url|max:500',
            'pdf_url' => 'nullable|url|max:500',
            'content_text' => 'nullable|string',
        ]);

        Regulation::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'is_active' => $request->has('is_active'),
        ]));

        return redirect('/regulations')->with('success', 'Regulasi berhasil ditambahkan.');
    }

    public function show(Regulation $regulation)
    {
        $regulation->load(['derogatLegi', 'revokedBy']);
        return view('regulations.show', compact('regulation'));
    }

    /**
     * Download PDF regulasi — format resmi keputusan/undang-undang
     */
    public function downloadPdf(Regulation $regulation)
    {
        if ($regulation->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $html = view('regulations.pdf-preview', compact('regulation'))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'serif',
            'dpi' => 120,
        ]);

        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $regulation->hierarchy_label.'_'.$regulation->number.'_'.$regulation->year);
        $filename = 'regulasi_'.$filename.'.pdf';

        return $pdf->download($filename);
    }

    public function edit(Regulation $regulation)
    {
        $allRegs = Regulation::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', '!=', $regulation->id)->get();
        return view('regulations.edit', compact('regulation', 'allRegs'));
    }

    public function update(Request $request, Regulation $regulation)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'number' => 'nullable|max:100',
            'year' => 'nullable|digits:4',
            'hierarchy_level' => 'required|in:1,2,3,4,5',
            'category_sector' => 'nullable|in:ketenagakerjaan,perpajakan,perusahaan,agraria,teknologi,lainnya',
            'status' => 'required|in:draft,active,archived,revoked',
            'is_active' => 'boolean',
            'derogat_legi_id' => 'nullable|exists:regulations,id',
            'penetapan_date' => 'nullable|date',
            'pengundangan_date' => 'nullable|date',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'source_url' => 'nullable|url|max:500',
            'pdf_url' => 'nullable|url|max:500',
            'content_text' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $regulation->update($validated);

        return redirect("/regulations/{$regulation->id}")->with('success', 'Regulasi berhasil diperbarui.');
    }

    public function destroy(Regulation $regulation)
    {
        $regulation->delete();
        return redirect('/regulations')->with('success', 'Regulasi berhasil dihapus.');
    }
}