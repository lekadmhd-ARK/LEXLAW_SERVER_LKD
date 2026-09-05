<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidityCheckerController extends Controller
{
    public function form()
    {
        return view('ai.validity-checker');
    }

    public function check(Request $request)
    {
        $validated = $request->validate(['text' => 'required|string']);
        $text = $validated['text'];

        // 1. Ekstrak sitasi regulasi dari teks
        $citations = $this->extractCitations($text);

        // 2. Jika tidak ada sitasi yang terdeteksi, tampilkan pesan bantu
        if (empty($citations)) {
            return view('ai.validity-checker', [
                'message' => 'Tidak ditemukan pattern sitasi regulasi (UU/PP/Perpres) di dalam teks. Pastikan Anda menulis format yang benar, contoh: "Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (UU ITE)" atau "Peraturan Pemerintah Nomor 71 Tahun 2019 tentang Penyelenggaraan Sistem dan Transaksi Elektronik".',
                'results' => [],
            ]);
        }

        // 3. Cari di database lokal
        $results = [];
        foreach ($citations as $citation) {
            $localMatch = $this->findInLocalDB($citation);
            if ($localMatch) {
                $results[] = array_merge($localMatch, ['source' => 'database']);
            }
        }

        // 4. Jika tidak ditemukan di DB, lakukan web search ke situs resmi
        $unfound = array_diff($citations, array_keys(array_column($results, 'reference' ?? [])));
        foreach ($unfound as $citation) {
            $webResult = $this->searchResmi($citation);
            if ($webResult) {
                // Simpan ke database lokal untuk cache selanjutnya
                Regulation::create([
                    'title' => $webResult['title'],
                    'number' => $webResult['number'],
                    'year' => $webResult['year'],
                    'category' => $webResult['category'],
                    'status' => $webResult['status'],
                    'source_url' => $webResult['source_url'],
                ]);
                $results[] = array_merge($webResult, ['source' => 'web_resmi']);
            }
        }

        // 5. Urutkan hasil: dulu dari DB, baru dari web
        usort($results, function($a, $b) {
            return ($a['source'] ?? '') === 'database' ? -1 : 1;
        });

        // 6. Analisis AI keaktifan
        $aiAnalysis = $this->analyzeRegulations($citations);

        return view('ai.validity-checker', [
            'results' => $results,
            'aiAnalysis' => $aiAnalysis,
            'message' => empty($results) ? 'Tidak ada sitasi regulasi terdeteksi.' : null,
        ]);
    }

    protected function extractCitations($text)
    {
        $patterns = [
            '/(U\.?U\.?\s*No\.?\s*\d+)\s*Tahun\s*(\d{4})/iu',
            '/(Undang-Undang\s*Nomor\s*\d+)\s*Tahun\s*(\d{4})/iu',
            '/(PP\s*No\.?\s*\d+)\s*Tahun\s*(\d{4})/iu',
            '/(Peraturan\s*Pemerintah\s*Nomor\s*\d+)\s*Tahun\s*(\d{4})/iu',
            '/(Peraturan\s*Daerah\s*Nomor\s*\d+)\s*Tahun\s*(\d{4})/iu',
        ];

        $citations = [];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $citations[] = trim($match);
                }
            }
        }

        return array_values(array_unique($citations));
    }

    protected function findInLocalDB($citation)
    {
        $number = preg_replace('/\D+/', '', $citation);
        $yearMatch = [];
        preg_match('/\d{4}/', $citation, $yearMatch);
        $year = $yearMatch[0] ?? null;

        $query = Regulation::query();
        if ($number && $year) {
            $query->where('number', $number)->where('year', $year);
        } else {
            $query->where('title', 'like', "%$citation%");
        }

        $matches = $query->limit(1)->get(['id', 'title', 'number', 'year', 'category', 'status', 'source_url']);
        $item = $matches->first();

        if ($item) {
            return [
                'reference' => $citation,
                'found' => true,
                'year' => $item->year,
                'category' => $item->category,
                'status' => $item->status,
                'source_url' => $item->source_url,
                'database_match' => "{$item->category} No. {$item->number}/{$item->year} - {$item->title}",
            ];
        }
        return null;
    }

    protected function searchResmi($citation)
    {
        $domains = [
            'peraturan.go.id'      => 'Peraturan Pemerintah & Menteri',
            'bpk.go.id'            => 'Badan Pemeriksa Keuangan',
            'kemendagri.go.id'     => 'Kementerian Dalam Negeri',
            'kemensetneg.go.id'    => 'Kementerian Keuangan',
            'kemhan.go.id'         => 'Kementerian Hukum dan HAM',
            'majidonline.com'      => 'Mahkamah Agung',
            'mk.go.id'             => 'Mahkamah Konstitusi',
        ];

        foreach ($domains as $domain => $label) {
            try {
                $searchQuery = $citation . ' site:' . $domain;
                $response = Http::get('https://hermes-agent.nousresearch.com/web_search', [
                    'query' => $searchQuery,
                    'limit' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['web'][0]['url'])) {
                        return [
                            'title' => $citation,
                            'number' => preg_replace('/\D+/', '', $citation),
                            'year' => substr($citation, -4),
                            'category' => $label,
                            'status' => 'perlu_verifikasi',
                            'source_url' => $data['data']['web'][0]['url'],
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    protected function analyzeRegulations($citations)
    {
        try {
            $response = Http::timeout(30)->withToken(env('AI_API_KEY'))
                ->post(env('AI_BASE_URL', 'http://127.0.0.1:20128/v1') . '/chat/completions', [
                    'model' => env('AI_MODEL', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah pakar hukum Indonesia. Berikan analisis singkat 1 kalimat tentang status hukum dari berikut ini:'],
                        ['role' => 'user', 'content' => "Berikan analisis status hukum (Aktif/Diubah/Dicabut) untuk: " . implode(", ", $citations)],
                    ],
                    'temperature' => 0.3,
                    'stream' => false,
                    'max_tokens' => 500,
                ]);

            return $response->json('choices.0.message.content') ?? "Analisis AI gagal.";
        } catch (\Exception $e) {
            return "Analisis AI gagal.";
        }
    }
}