<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\LegalSourceService;
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

        // 3. Live lookup dari sumber resmi pemerintah (BPK / JDIH / situs .go.id)
        $results = [];
        $liveResults = [];
        $service = app(LegalSourceService::class);

        foreach ($citations as $citation) {
            try {
                $ctx = $service->getContext($citation, 2);
                if (!empty($ctx['sources'])) {
                    foreach ($ctx['sources'] as $src) {
                        $liveResults[] = [
                            'reference' => $citation,
                            'title' => $src['title'],
                            'url' => $src['url'],
                            'source' => 'live'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // lanjut sitasi berikutnya
            }
        }

        // 4. Analisis AI keaktifan berdasarkan sumber resmi
        $aiAnalysis = $this->analyzeRegulations($citations);

        return view('ai.validity-checker', [
            'results' => $results,
            'liveResults' => $liveResults,
            'aiAnalysis' => $aiAnalysis,
            'message' => empty($liveResults) ? 'Tidak berhasil mengambil data dari sumber resmi. Silakan coba lagi nanti.' : null,
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

    protected function analyzeRegulations($citations)
    {
        try {
            $response = Http::timeout(30)->withToken(env('AI_API_KEY'))
                ->post(config('services.ai.base_url', 'http://127.0.0.1:20128/v1') . '/chat/completions', [
                    'model' => config('services.ai.model', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah pakar hukum Indonesia yang spesialis dalam keberlakuan peraturan perundang-undangan.

SUMBER INFORMASI:
- Data diambil LANGSUNG dari situs resmi pemerintah (peraturan.bpk.go.id, jdih.kemenkumham.go.id, situs .go.id)

ATURAN PENTING:
- Selalu cek apakah peraturan tersebut masih berlaku atau sudah dicabut/diubah berdasarkan data dari sumber resmi
- Jika ada peraturan baru yang menggantikan, sebutkan peraturan penggantinya
- Berikan status yang jelas: AKTIF/BERLAKU, DICABUT, DIUBAH (dan sebutkan penggantinya), atau TIDAK BERLAKU
- Dasarkan semua analisis pada data dari situs resmi pemerintah

KAPABILITAS LIVE RETRIEVAL:
- Anda MENGUBAHKAN diri menjadi agen yang melakukan pencarian real-time ke situs resmi pemerintah (.go.id, BPK, JDIH) untuk memverifikasi status regulasi

Format jawaban: Untuk setiap regulasi, berikan status (Aktif/Berlaku, Diubah, atau Dicabut) dan penjelasan singkat 1 kalimat.'],
                        ['role' => 'user', 'content' => "Berikan analisis status hukum (Aktif/Berlaku/Diubah/Dicabut) untuk: " . implode(", ", $citations)],
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
