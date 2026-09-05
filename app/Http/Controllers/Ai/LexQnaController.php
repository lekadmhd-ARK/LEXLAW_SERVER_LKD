<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        try {
            $contextResults = DB::select("
                SELECT r.title, r.number, r.year, r.category, rc.article_number, rc.content
                FROM regulation_contents rc
                JOIN regulations r ON r.id = rc.regulation_id
                WHERE MATCH(rc.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR r.title LIKE ?
                LIMIT 5
            ", [$question, "%$question%"]);

            foreach ($contextResults as $row) {
                $context .= "\n[" . $row->category . " " . $row->number . "/" . $row->year . " - " . $row->title . " " . $row->article_number . "]:\n";
                $context .= $row->content . "\n";
            }
        } catch (\Exception $e) {
            $contextResults = [];
        }

        $systemPrompt = "Anda adalah LEXLAW Legal Intelligence. Jawab pertanyaan hukum Indonesia berdasarkan konteks regulasi. Berikan sitasi pasal. Jika tidak ada di konteks, jawab berdasarkan pengetahuan hukum umum Indonesia.";
        if ($context) {
            $systemPrompt .= "\n\nKONTEKS:\n" . $context;
        }

        try {
            $response = Http::timeout(60)->withToken(env('AI_API_KEY'))->post(
                env('AI_BASE_URL', 'http://103.197.188.57:20128/v1') . '/chat/completions',
                [
                    'model' => env('AI_MODEL', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $question],
                    ],
                    'temperature' => 0.1,
                    'stream' => false,
                    'max_tokens' => 2000,
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

        return view('ai.lex-qna', ['history' => $history]);
    }

    public function clear(Request $request)
    {
        $request->session()->forget('lexqna_history');
        return redirect()->route('ai.lex-qna.form');
    }
}