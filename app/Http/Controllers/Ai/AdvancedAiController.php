<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Regulation;

class AdvancedAiController extends Controller
{
    public function contractReview(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('ai.contract-reviewer');
        }

        $text = $request->input('contract_text', '');
        $fileContent = '';

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['pdf'])) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $fileContent = $pdf->getText();
            } elseif (in_array($ext, ['doc', 'docx'])) {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
                $fileContent = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                            $fileContent .= $element->getText() . "\n";
                        }
                    }
                }
            } elseif (in_array($ext, ['txt'])) {
                $fileContent = file_get_contents($file->getRealPath());
            }
        }

        $combined = trim($text . "\n\n" . $fileContent);

        if (mb_strlen($combined) < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Teks terlalu pendek. Minimal 50 karakter. Silakan paste teks kontrak yang lebih lengkap atau upload file.',
            ], 422);
        }

        $systemPrompt = "Anda adalah pengacara hukum bisnis Indonesia senior dengan pengalaman 20+ tahun. Analisis kontrak berikut secara mendalam dan berikan hasil dalam format markdown dengan section-section berikut:\n\n## Ringkasan Kontrak\n(Brief summary, pihak, subjek, nilai, durasi)\n\n## Klausa Bermasalah\n(Daftar klausa yang berisiko/merugikan salah satu pihak, dengan nomor pasal/bagian)\n\n## Analisis Risiko\n(Rating risiko: Rendah/Sedang/Tinggi per klausa, penjelasan)\n\n## Checklist Hukum\n(Cek apakah memenuhi KUH Perdata, UU No. 40/2007 PT, UU No. 13/2003 Ketenagakerjaan, dll)\n\n## Rekomendasi Klausul\n(Draft klausul perbaikan untuk setiap masalah)\n\n## Kesimpulan & Saran\n(Rekomendasi akhir, apakah layak ditandatangani atau perlu renovasi)\n\nGunakan bahasa Indonesia yang profesional. Sertakan referensi pasal/undang-undang yang relevan.";

        $apiKey = config('services.ai.api_key');
        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.model', 'gemini/gemini-3.5-flash');

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'AI gateway belum dikonfigurasi.',
            ], 500);
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Analisis kontrak berikut:\n\n" . mb_substr($combined, 0, 30000)],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                    'stream' => false,
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $retryAfter = $response->header('Retry-After', 60);
                if ($status === 429) {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI sedang sibuk. Silakan coba lagi dalam ' . $retryAfter . ' detik.',
                        'retry_after' => (int) $retryAfter,
                    ], 429);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'AI gateway error (' . $status . '). Silakan coba lagi.',
                ], 500);
            }

            $body = $response->json();

            $content = '';
            if (isset($body['choices'][0]['message']['content'])) {
                $content = $body['choices'][0]['message']['content'];
            } elseif (isset($body['choices'][0]['delta']['content'])) {
                $fullContent = '';
                foreach ($body['choices'] as $choice) {
                    if (isset($choice['delta']['content'])) {
                        $fullContent .= $choice['delta']['content'];
                    }
                }
                $content = $fullContent;
            }

            if (empty($content)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak menghasilkan respons. Silakan coba lagi.',
                ], 500);
            }

            $uid = 'LEX-CR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            return response()->json([
                'success' => true,
                'uid' => $uid,
                'answer' => $content,
                'generated_at' => now()->setTimezone('Asia/Jakarta')->locale('id_ID')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke AI gateway gagal. Periksa koneksi internet.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}