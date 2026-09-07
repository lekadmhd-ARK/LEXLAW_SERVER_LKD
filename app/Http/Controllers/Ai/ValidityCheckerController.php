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
        $unmatched = [];
        foreach ($citations as $citation) {
            $localMatch = $this->findInLocalDB($citation);
            if ($localMatch) {
                $results[] = array_merge($localMatch, ['source' => 'database']);
            } else {
                $unmatched[] = $citation;
            }
        }

        // 3b. Live lookup dari sumber resmi (BPK / situs .go.id) untuk sitasi
        //     yang tidak ada di database lokal, agar keaktifan tetap bisa diverifikasi.
        $liveResults = [];
        if (!empty($unmatched)) {
            $service = app(\App\Services\LegalSourceService::class);
            foreach ($unmatched as $citation) {
                try {
                    $ctx = $service->getContext($citation, 1);
                    if (!empty($ctx['sources'])) {
                        $src = $ctx['sources'][0];
                        $liveResults[] = [
                            'title' => $src['title'],
                            'url' => $src['url'],
                            'status' => null,
                            'source' => 'live'
                        ];
                    }
                } catch (\Exception $e) {
                    // lanjut sitasi berikutnya
                }
            }
        }

        // 4. Analisis AI keaktifan
        $aiAnalysis = $this->analyzeRegulations($citations);

        return view('ai.validity-checker', [
            'results' => $results,
            'liveResults' => $liveResults,
            'aiAnalysis' => $aiAnalysis,
            'message' => (empty($results) && empty($liveResults)) ? 'Tidak ada sitasi regulasi terdeteksi di database lokal. Silakan tambahkan regulasi terlebih dahulu.' : null,
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

    protected function analyzeRegulations($citations)
    {
        try {
            $response = Http::timeout(30)->withToken(env('AI_API_KEY'))
                ->post(config('services.ai.base_url', 'http://127.0.0.1:20128/v1') . '/chat/completions', [
                    'model' => config('services.ai.model', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah pakar hukum Indonesia. Pengetahuan Anda TIDAK terbatas pada data regulasi di database aplikasi ini; selalu up-to-date dengan seluruh peraturan perundang-undangan Indonesia (pusat dan daerah) yang berlaku sampai saat ini, dan dasarkan semua pengetahuan hukum pada situs-situs resmi pemerintah (pemerintah daerah maupun pusat, contoh: peraturan.go.id, jdih.kemenkumham.go.id, peraturan.bpk.go.id, serta JDIH provinsi/kabupaten/kota).\n\nKAPABILITAS LIVE RETRIEVAL:\n- Anda MENGUBAHKAN diri menjadi agen yang melakukan pencarian real-time ke situs resmi pemerintah (.go.id, BPK, JDIH) untuk memverifikasi regulasi terbaru.\n\nBerikan analisis singkat 1 kalimat tentang status hukum dari berikut ini:'],
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
