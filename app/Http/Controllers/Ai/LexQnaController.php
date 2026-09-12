<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\LegalSourceService;
use App\Models\Putusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class LexQnaController extends Controller
{
    public function form()
    {
        $history = session('lexqna_history', []);
        return view('ai.lex-qna', ['history' => $history]);
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:5000',
        ]);

        $question = $validated['question'];

        $context = '';
        $sources = [];

        // ============================================================
        # SATU-SATUNYA SUMBER: LIVE RETRIEVAL dari sumber resmi pemerintah
        // ============================================================
        try {
            $live = app(LegalSourceService::class)->getContext($question, 5);
            if (!empty($live['context'])) {
                $context .= $live['context'];
                $sources = array_merge($sources, $live['sources']);
            }
        } catch (\Exception $e) {
            // live retrieval gagal
        }

        // ============================================================
        // PELENGKAP: Yurisprudensi dari DB lokal (putusan pengadilan)
        // ============================================================
        try {
            $putusanResults = Putusan::published()
                ->search($question)
                ->latest('tanggal_putusan')
                ->limit(2)
                ->get();

            if ($putusanResults->isNotEmpty()) {
                $context .= "\n=== YURISPRUDENSI (PUTUSAN PENGADILAN) ===\n";
                foreach ($putusanResults as $p) {
                    $context .= "[Putusan " . $p->nomor_putusan . " - " . $p->nama_pengadilan . " (" . $p->golongan_perkara . ")]:\n";
                    $context .= "Ringkasan: " . mb_substr($p->ringkasan_putusan ?? '', 0, 800) . "\n";
                }
            }
        } catch (\Exception $e) {
            // abaikan
        }

        // ============================================================
        // SYSTEM PROMPT - Hanya sumber resmi pemerintah
        // ============================================================
        $systemPrompt = "Anda adalah LEXLAW Legal Intelligence - asisten hukum Indonesia yang ahli.

SUMBER INFORMASI:
- Konteks di bawah ini diambil LANGSUNG dari situs resmi pemerintah Indonesia (peraturan.bpk.go.id, jdih.kemenkumham.go.id, dan situs .go.id lainnya)
- Yurisprudensi adalah putusan pengadilan yang relevan

ATURAN PENTING:
- Jawab HANYA berdasarkan konteks dari sumber resmi pemerintah yang diberikan
- Jika di sumber resmi tertulis status 'Dicabut' atau 'Tidak Berlaku', JANGAN gunakan peraturan tersebut
- Jika ada beberapa versi UU tentang topik yang sama, gunakan versi TERBARU
- Selalu cantumkan URL sumber resmi pada akhir jawaban
- Jika konteks tidak cukup, jawab berdasarkan pengetahuan hukum positif Indonesia yang berlaku dan sarankan verifikasi ke peraturan.bpk.go.id atau jdih.kemenkumham.go.id";

        if ($context) {
            $systemPrompt .= "\n\nKONTEKS DARI SUMBER RESMI PEMERINTAH:\n" . $context;
        } else {
            $systemPrompt .= "\n\nTidak berhasil mengambil data dari sumber resmi. Jawab berdasarkan pengetahuan hukum positif Indonesia yang berlaku, dan sarankan verifikasi ke peraturan.bpk.go.id atau jdih.kemenkumham.go.id.";
        }

        try {
            $response = Http::timeout(90)->withToken(config('services.ai.key'))->post(
                config('services.ai.base_url', 'http://127.0.0.1:20128/v1') . '/chat/completions',
                [
                    'model' => config('services.ai.model', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $question],
                    ],
                    'temperature' => 0.1,
                    'stream' => false,
                    'max_tokens' => 3000,
                ]
            );
            $answer = $response->json('choices.0.message.content') ?? 'Maaf, gagal mendapatkan jawaban dari AI.';
        } catch (\Exception $e) {
            $answer = 'Error: ' . $e->getMessage();
        }

        $history = session('lexqna_history', []);
        $history[] = ['role' => 'user', 'content' => $question];
        $history[] = ['role' => 'assistant', 'content' => $answer];
        session(['lexqna_history' => $history]);

        return view('ai.lex-qna', [
            'history' => $history,
            'lastSources' => $sources,
        ]);
    }

    public function clear(Request $request)
    {
        $request->session()->forget('lexqna_history');
        return redirect()->route('ai.lex-qna.form');
    }
}
