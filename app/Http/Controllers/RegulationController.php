<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RegulationController extends Controller
{
    public function index(Request $request)
    {
        // Data regulasi bersifat publik: semua pengguna melihat seluruh regulasi.

        // RAG: RETRIEVE hanya dari DB lokal (jangan minta AI mengarang data baru).
        // Jika hasil kosong, tampilkan kosong — jangan fallback ke AI hallucination.
        $localResults = collect();
        if ($request->filled('q')) {
            $q = trim($request->q);
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                try {
                    // ponytail: 'simple' tanpa stemming Inggris; upgrade: embedding pgvector jika data >10k.
                    $localResults = \Illuminate\Support\Facades\DB::select("
                        SELECT r.*,
                               ts_rank(
                                   to_tsvector('simple', coalesce(r.title,'') || ' ' || coalesce(r.short_description,'') || ' ' || coalesce(r.content_text,'') || ' ' || coalesce(r.number,'')),
                                   plainto_tsquery('simple', ?)
                               ) AS rank
                        FROM regulations r
                        WHERE to_tsvector('simple', coalesce(r.title,'') || ' ' || coalesce(r.short_description,'') || ' ' || coalesce(r.content_text,'') || ' ' || coalesce(r.number,''))
                              @@ plainto_tsquery('simple', ?)
                        ORDER BY rank DESC, r.year DESC
                        LIMIT 100
                    ", [$q, $q]);
                } catch (\Exception $e) {
                    // fallback LIKE jika tsquery error (karakter aneh)
                    $localResults = Regulation::where(function($w) use ($q) {
                        $w->where('title', 'ilike', "%{$q}%")
                          ->orWhere('number', 'ilike', "%{$q}%")
                          ->orWhere('short_description', 'ilike', "%{$q}%")
                          ->orWhere('content_text', 'ilike', "%{$q}%");
                    })->limit(100)->get();
                }
                // DB::select menghasilkan array<stdClass> — hydrate ke Regulation Model agar accessor (hierarchy_label, sector_label) jalan
                if (is_array($localResults) || ($localResults instanceof \Illuminate\Support\Collection && count($localResults) > 0 && !($localResults->first() instanceof Regulation))) {
                    $items = collect($localResults)->map(fn($r) => (array)$r)->all();
                    $localResults = Regulation::hydrate($items);
                } else {
                    $localResults = collect($localResults);
                }
            } elseif ($driver === 'mysql') {
                try {
                    $localResults = \Illuminate\Support\Facades\DB::select("
                        SELECT r.* FROM regulations r
                        WHERE (MATCH(r.title, r.short_description) AGAINST(? IN NATURAL LANGUAGE MODE) OR r.content_text LIKE ? OR r.title LIKE ?)
                        LIMIT 100
                    ", [$q, "%{$q}%", "%{$q}%"]);
                    $localResults = collect($localResults);
                } catch (\Exception $e) {
                    $localResults = Regulation::where(function($w) use ($q) {
                        $w->where('title', 'like', "%{$q}%")
                          ->orWhere('number', 'like', "%{$q}%")
                          ->orWhere('short_description', 'like', "%{$q}%")
                          ->orWhere('content_text', 'like', "%{$q}%");
                    })->limit(100)->get();
                }
            } else {
                $localResults = Regulation::where(function($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('number', 'like', "%{$q}%")
                      ->orWhere('short_description', 'like', "%{$q}%")
                      ->orWhere('content_text', 'like', "%{$q}%");
                })->limit(100)->get();
            }
        } else {
            $localResults = Regulation::latest()->get();
        }

        // Tidak ada auto-insert dari AI di path pencarian. Data baru hanya via fetchFromJdihUrl
        // yang menscrape HTML asli terlebih dahulu.
        $webSearchPerformed = false;

        // Saran live dari sumber resmi (peraturan.bpk.go.id / situs .go.id) ketika
        // hasil lokal kosong — hanya ditampilkan sebagai link, TIDAK disimpan ke DB.
        $liveSuggestions = [];
        if ($request->filled('q') && $localResults->count() === 0) {
            try {
                $live = app(\App\Services\LegalSourceService::class)->search($request->q, 5);
                $liveSuggestions = $live;
            } catch (\Exception $e) {
                $liveSuggestions = [];
            }
        }

        // 3) Filter koleksi (hierarchy/sector/active) tetap di collection.
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
            'total' => Regulation::count(),
            'uu' => Regulation::where('hierarchy_level', '1')->count(),
            'pp' => Regulation::where('hierarchy_level', '2')->count(),
            'perpres' => Regulation::where('hierarchy_level', '3')->count(),
            'active' => Regulation::where('is_active', true)->count(),
        ];

        return view('regulations.index', compact('regulations', 'stats', 'webSearchPerformed', 'liveSuggestions') + ['searchQuery' => $request->q ?? '']);
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
                            ['role' => 'system', 'content' => 'Anda adalah asisten hukum Indonesia. Pengetahuan Anda TIDAK terbatas pada data regulasi di database aplikasi ini; selalu up-to-date dengan seluruh peraturan perundang-undangan Indonesia (pusat dan daerah) yang berlaku sampai saat ini, dan dasarkan semua pengetahuan hukum pada situs-situs resmi pemerintah (pemerintah daerah maupun pusat, contoh: peraturan.go.id, jdih.kemenkumham.go.id, peraturan.bpk.go.id, serta JDIH provinsi/kabupaten/kota). Cari peraturan perundang-undangan Indonesia yang terkait query. Kembalikan HANYA dalam format JSON array (tanpa markdown codeblock) dengan object berisi field: title, number, year, hierarchy_level (1=UU, 2=PP, 3=Perpres, 4=PerMen, 5=Perda), category_sector (ketenagakerjaan/perpajakan/perusahaan/agraria/teknologi/lainnya), short_description, source_url. Maksimal 3 hasil. Prioritas peraturan yang masih berlaku dan terbaru.'],
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
                            ['role' => 'system', 'content' => 'Anda adalah asisten hukum Indonesia. Pengetahuan Anda TIDAK terbatas pada data regulasi di database aplikasi ini; selalu up-to-date dengan seluruh peraturan perundang-undangan Indonesia (pusat dan daerah) yang berlaku sampai saat ini, dan dasarkan semua pengetahuan hukum pada situs-situs resmi pemerintah (pemerintah daerah maupun pusat, contoh: peraturan.go.id, jdih.kemenkumham.go.id, peraturan.bpk.go.id, serta JDIH provinsi/kabupaten/kota). Dari teks halaman peraturan berikut, ekstrak dan kembalikan HANYA format JSON object dengan field: short_description (abstrak/ringkasan 1-2 kalimat), content_text (isi pokok/batang tubuh peraturan, boleh diringkas tapi tetap akurat), category_sector (ketenagakerjaan/perpajakan/perusahaan/agraria/teknologi/lainnya), is_active (boolean, true jika masih berlaku). Gunakan bahasa Indonesia.'],
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
        $allRegs = Regulation::get();
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

        // Cegah double data: judul yang sama (case-insensitive) tidak boleh dibuat ulang.
        $exists = Regulation::whereRaw('lower(trim(title)) = ?', [mb_strtolower(trim($validated['title']))])->first();
        if ($exists) {
            return back()->withInput()->withErrors([
                'title' => 'Regulasi dengan judul yang sama sudah ada (ID #' . $exists->id . '). Data regulasi bersifat publik & tidak boleh ganda.',
            ]);
        }

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
        $allRegs = Regulation::where('id', '!=', $regulation->id)->get();
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

        // Cegah double data: judul yang sama (case-insensitive) milik regulasi lain tidak boleh dipakai.
        $exists = Regulation::whereRaw('lower(trim(title)) = ?', [mb_strtolower(trim($validated['title']))])
            ->where('id', '!=', $regulation->id)
            ->first();
        if ($exists) {
            return back()->withInput()->withErrors([
                'title' => 'Judul ini sudah dipakai regulasi lain (ID #' . $exists->id . '). Data regulasi bersifat publik & tidak boleh ganda.',
            ]);
        }

        $regulation->update($validated);

        return redirect("/regulations/{$regulation->id}")->with('success', 'Regulasi berhasil diperbarui.');
    }

    public function destroy(Regulation $regulation)
    {
        $regulation->delete();
        return redirect('/regulations')->with('success', 'Regulasi berhasil dihapus.');
    }

    /**
     * Fetch dari BPK Search - scrape HTML, extract metadata, simpan ke DB
     */
    public function searchAndFetchFromBpk(Request $request)
    {
        $request->validate(['q' => 'required|string|max:200']);
        $query = trim($request->q);
        $searchUrl = 'https://peraturan.bpk.go.id/Search?keywords=' . urlencode($query);

        $html = $this->scrapeHtml($searchUrl);
        if (empty($html)) {
            return back()->with('error', 'Gagal mengakses portal BPK');
        }

        // extract detail links (dedupe, keep first 5)
        $links = [];
        preg_match_all('/href="\/Details\/(\d+)\/([^"]+)"/', $html, $m);
        if (!empty($m[1])) {
            $seen = [];
            foreach ($m[1] as $i => $id) {
                if (isset($seen[$id])) continue;
                $seen[$id] = true;
                $links[] = ['id' => $id, 'slug' => $m[2][$i]];
                if (count($links) >= 5) break;
            }
        }

        if (empty($links)) {
            return back()->with('error', 'Tidak ditemukan hasil untuk: ' . $query);
        }

        $saved = 0;
        $titles = [];

        foreach ($links as $link) {
            $detailUrl = 'https://peraturan.bpk.go.id/Details/' . $link['id'] . '/' . $link['slug'];
            usleep(300000);
            $detailHtml = $this->scrapeHtml($detailUrl);
            if (empty($detailHtml)) continue;

            $title = '';
            if (preg_match('/<title[^>]*>\s*(.*?)\s*<\/title>/is', $detailHtml, $tm)) {
                $title = trim(html_entity_decode($tm[1]));
            }
            if (empty($title)) $title = ucwords(str_replace('-', ' ', $link['slug']));

            $pdfUrl = '';
            if (preg_match('/href="(\/Download\/\d+\/[^"]+\.pdf)"/i', $detailHtml, $pm)) {
                $pdfUrl = 'https://peraturan.bpk.go.id' . html_entity_decode($pm[1]);
            }

            $num = '';
            $year = '';
            if (preg_match('/\b(?:No\.?|Nomor)\s*(\d[\d\/A-Z.]*)\s+(?:Tahun\s+)?(\d{4})/i', $title, $nm)) {
                $num = $nm[1];
                $year = $nm[2];
            } elseif (preg_match('/(\d+)\s+(?:Tahun\s+)?(\d{4})/i', $title, $nm)) {
                $num = $nm[1];
                $year = $nm[2];
            }

            $hl = 5;
            $slug = strtolower($link['slug']);
            if (str_starts_with($slug, 'uu-') || str_contains($title, 'Undang-Undang')) $hl = 1;
            elseif (str_starts_with($slug, 'pp-') || str_contains($title, 'Peraturan Pemerintah')) $hl = 2;
            elseif (str_starts_with($slug, 'perpres-') || str_contains($title, 'Peraturan Presiden')) $hl = 3;
            elseif (str_contains($title, 'Peraturan Menteri') || str_contains($title, 'Keputusan Menteri')) $hl = 4;

            $isActive = !str_contains(strtolower($title), 'dicabut') && !str_contains(strtolower($title), 'tidak berlaku');

            $existing = \App\Models\Regulation::whereRaw('lower(trim(title)) = ?', [mb_strtolower(trim($title))])
                ->orWhere(function ($w) use ($num, $year) {
                    if (!empty($num) && !empty($year)) {
                        $w->where('number', $num)->where('year', (int)$year);
                    }
                })
                ->first();
            if ($existing) {
                if (empty($existing->pdf_url) && !empty($pdfUrl)) {
                    $existing->update(['pdf_url' => $pdfUrl]);
                }
                $titles[] = $existing->title . ' (sudah ada)';
                continue;
            }

            $user = $request->user();
            $reg = \App\Models\Regulation::create([
                'tenant_id' => $user->tenant_id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'title' => $title,
                'number' => $num ?: null,
                'year' => $year ? (int)$year : null,
                'hierarchy_level' => $hl,
                'is_active' => $isActive,
                'status' => 'active',
                'source_url' => $detailUrl,
                'pdf_url' => $pdfUrl ?: null,
                'category_sector' => 'lainnya',
            ]);

            $this->parsePassages($reg);
            $titles[] = $title;
            $saved++;
        }

        $msg = $saved > 0
            ? "Berhasil fetch dan simpan $saved regulasi: " . implode(', ', $titles)
            : "Tidak ada regulasi baru. Hasil: " . implode(', ', $titles);

        return redirect('/regulations')->with('success', $msg);
    }

    /**
     * Parse content_text ke regulation_passages (RAG)
     */
    protected function parsePassages($regulation)
    {
        $text = trim($regulation->content_text ?? '');
        if ($text === '') return;

        $parts = preg_split('/\bPasal\s+/i', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) <= 1) {
            DB::table('regulation_passages')->updateOrInsert(
                ['regulation_id' => $regulation->id, 'passage_number' => '1'],
                ['tenant_id' => $regulation->tenant_id, 'passage_type' => 'pasal', 'passage_title' => 'Ketentuan Utuh',
                 'content' => $text, 'hierarchy_path' => '1', 'created_at' => now(), 'updated_at' => now()]
            );
            return;
        }
        foreach ($parts as $idx => $p) {
            $num = (string)($idx + 1);
            $c = trim($p);
            if (strlen($c) < 5) continue;
            DB::table('regulation_passages')->updateOrInsert(
                ['regulation_id' => $regulation->id, 'passage_number' => $num],
                ['tenant_id' => $regulation->tenant_id, 'passage_type' => 'pasal', 'passage_title' => "Pasal $num",
                 'content' => $c, 'hierarchy_path' => $num, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }


    /**
     * Re-fetch data regulasi dari sumber aslinya (BPK/JDIH)
     * Digunakan untuk mengupdate detail naskah dan metadata yang kurang lengkap.
     */
    public function refetchFromBpk(Regulation $regulation)
    {
        $url = $regulation->source_url;
        if (!$url) {
            return back()->with('error', 'URL sumber tidak tersedia untuk regulasi ini.');
        }

        // 1. Scrape HTML terbaru
        $html = $this->scrapeHtml($url);
        if (empty($html)) {
            return back()->with('error', 'Gagal mengakses portal sumber (' . $url . ').');
        }

        // 2. Ekstrak metadata dasar (title, date, pdf, status)
        $meta = $this->extractMetadataFromHtml($html, $url);

        // 3. Ekstrak konten & abstraksi via AI (lebih akurat untuk naskah lengkap)
        $text = $this->stripHtml($html);
        $text = mb_substr($text, 0, 15000); // naikkan limit untuk detail lebih baik
        $ai = $this->extractContentViaAi($text, $url);

        // 4. Update regulasi
        $regulation->update([
            'short_description' => $ai['short_description'] ?? $meta['short_description'] ?? $regulation->short_description,
            'content_text' => $ai['content_text'] ?? $regulation->content_text,
            'category_sector' => $ai['category_sector'] ?? $meta['category_sector'] ?? $regulation->category_sector,
            'is_active' => $ai['is_active'] ?? $meta['is_active'] ?? $regulation->is_active,
            'pdf_url' => $meta['pdf_url'] ?? $regulation->pdf_url,
            'penetapan_date' => $meta['penetapan_date'] ?? $regulation->penetapan_date,
            'pengundangan_date' => $meta['pengundangan_date'] ?? $regulation->pengundangan_date,
        ]);

        // 5. Re-parse passages untuk RAG (hapus yang lama dulu)
        DB::table('regulation_passages')->where('regulation_id', $regulation->id)->delete();
        $this->parsePassages($regulation);

        return back()->with('success', 'Detail regulasi berhasil diperbarui dari sumber resmi.');
    }

}