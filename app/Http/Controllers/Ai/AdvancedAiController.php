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

    public function downloadResult(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'type' => 'required|in:pdf,docx',
            'content' => 'required|string',
            'generated_at' => 'nullable|string',
        ]);

        $uid = $request->input('uid');
        $type = $request->input('type');
        $content = $request->input('content');
        $generatedAt = $request->input('generated_at', now()->setTimezone('Asia/Jakarta')->locale('id_ID')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB');
        $filename = 'lexlaw-contract-analysis-' . $uid;

        if ($type === 'pdf') {
            return $this->generatePdf($content, $uid, $generatedAt, $filename);
        }

        return $this->generateDocx($content, $uid, $generatedAt, $filename);
    }

    private function generatePdf($content, $uid, $generatedAt, $filename)
    {
        $html = $this->buildHtml($content, $uid, $generatedAt);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html);
        $pdf->setPaper('a4');
        return $pdf->stream($filename . '.pdf', ['Attachment' => true]);
    }

    private function generateDocx($content, $uid, $generatedAt, $filename)
    {
        $section = new \PhpOffice\PhpWord\Section();
        $section->addTitle('Laporan Analisis Kontrak', 20);
        $section->addParagraph('UID: ' . $uid);
        $section->addParagraph('Tanggal: ' . $generatedAt);
        $section->addParagraph('');

        foreach (explode("\n", $content) as $line) {
            if (empty(trim($line))) {
                $section->addParagraph('');
            } elseif (substr($line, 0, 2) === '##') {
                $section->addTitle(substr($line, 3), 16);
            } elseif (substr($line, 0, 1) === '#') {
                $section->addTitle(substr($line, 2), 18);
            } else {
                $section->addParagraph($line);
            }
        }

        $section->addParagraph('');
        $section->addParagraph('Disclaimer: Analisis ini dihasilkan AI untuk referensi awal dan bukan nasihat hukum resmi.');
        $section->addParagraph('UID: ' . $uid);

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter(new \PhpOffice\PhpWord\PhpWord(), 'Word2007');
        $objPhpWord = $writer->getPhpWord();
        $objPhpWord->addSection()->merge();
        $objPhpWord->sections = [$section];

        $tempFile = tempnam(sys_get_temp_dir(), 'lawlex_');
        $writer->save($tempFile);

        $content2 = file_get_contents($tempFile);
        @unlink($tempFile);

        return response($content2)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', 'attachment; filename= . \$filename . .docx');
    }

    private function buildHtml($content, $uid, $generatedAt)
    {
        $mdHtml = nl2br(e($content));
        return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10pt;line-height:1.6;padding:20mm;color:#1a1a1a;}
h1{font-size:16pt;color:#2563eb;border-bottom:2px solid #2563eb;padding-bottom:8px;margin:0 0 16px 0;}
.meta{font-size:9pt;color:#666;margin-bottom:24px;}
.content{white-space:pre-wrap;text-align:justify;}
.footer{margin-top:40px;padding-top:12px;border-top:1px solid #ccc;font-size:8pt;color:#999;}
</style>
</head>
<body>
<h1>Laporan Analisis Kontrak</h1>
<div class="meta">UID: ' . $uid . ' | Tanggal: ' . $generatedAt . '</div>
<div class="content">' . $mdHtml . '</div>
<div class="footer">Disclaimer: Analisis ini dihasilkan AI untuk referensi awal dan bukan nasihat hukum resmi. Konsultasikan dengan pengacara berkualifikasi untuk keputusan hukum yang mengikat. UID: ' . $uid . '<br>© 2026 LEXLAW v2 - AI Legal Analysis</div>
</body>
</html>';
    }
}
