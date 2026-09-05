<?php

namespace App\Http\Controllers\Ai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ContractReviewerController extends Controller
{
    public function index()
    {
        return view('ai.contract-reviewer');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'contract_text' => 'nullable|string',
            'contract_file' => 'nullable|file|mimes:pdf,docx,txt|max:5120',
        ]);

        // Ekstrak teks dari file jika ada
        $contractText = $request->contract_text;
        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getPathname());
                $contractText = $pdf->getText();
            } elseif ($extension === 'docx') {
                $phpWord = IOFactory::load($file->getPathname());
                $contractText = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $contractText .= $element->getText();
                        }
                    }
                }
            } elseif ($extension === 'txt') {
                $contractText = file_get_contents($file->getPathname());
            }
        }

        // Validasi minimal teks
        if (empty($contractText) || Str::wordCount($contractText) < 50) {
            return back()->withErrors(['contract_text' => 'Kontrak terlalu pendek atau kosong. Minimal 50 kata.']);
        }

        // Kirim ke AI Gateway
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('AI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post(env('AI_BASE_URL'), [
                'model' => 'gemini-3.5-flash',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Anda adalah ahli hukum Indonesia yang ahli dalam mengevaluasi kontrak bisnis. Analisis kontrak berikut ini secara mendalam dan berikan hasil dalam format JSON dengan struktur:

{
  \"risk_score\": \"Rendah/Sedang/Tinggi\",
  \"risk_items\": [
    {
      \"pasal\": \"...\",
      \"issue\": \"...\",
      \"suggestion\": \"...\"
    }
  ],
  \"compliance_checklist\": [
    {
      \"item\": \"...\",
      \"status\": \"Ada/Tidak Ada\",
      \"note\": \"...\"
    }
  ],
  \"executive_summary\": \"...\"
}

Analisis dengan fokus pada:
1. Klausul tidak seimbang
2. Risiko hukum yang tidak jelas
3. Kewajiban yang ambigu
4. Denda yang tidak proporsional
5. Klausul force majeure yang lemah
6. Termination clause
7. Governing law
8. Dispute resolution"
                    ],
                    [
                        'role' => 'user',
                        'content' => $contractText
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return view('ai.contract-reviewer-result', compact('result'));
            } else {
                return back()->withErrors(['contract_text' => 'Gagal memproses kontrak: ' . $response->body()]);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['contract_text' => 'Error: ' . $e->getMessage()]);
        }
    }
}