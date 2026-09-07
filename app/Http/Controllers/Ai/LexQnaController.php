<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\LegalSourceService;
use App\Models\Putusan;
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
        $sources = [];
        try {
            $driver = DB::getDriverName();

            // 1) RETRIEVE dari database lokal: regulation_contents (pasal) +
            //    fallback content_text regulasi (tabel contents kerap kosong).
            $contextResults = collect();

            if ($driver === 'pgsql') {
                try {
                    $ctxTmp = DB::select("
                        SELECT r.title, r.number, r.year, r.category, rc.article_number, rc.content
                        FROM regulation_contents rc
                        JOIN regulations r ON r.id = rc.regulation_id
                        WHERE to_tsvector('english', coalesce(rc.content, '')) @@ plainto_tsquery(?)
                        OR r.title LIKE ?
                        LIMIT 5
                    ", [$question, "%$question%"]);
                    $contextResults = collect($ctxTmp);
                } catch (\Exception $e) {
                    $contextResults = collect();
                }

                if ($contextResults->count() < 3) {
                    try {
                        $regDocs = DB::select("
                            SELECT r.title, r.number, r.year, r.category,
                                   NULL AS article_number, r.content_text AS content
                            FROM regulations r
                            WHERE to_tsvector('simple', coalesce(r.title,'') || ' ' || coalesce(r.content_text,''))
                                  @@ plainto_tsquery('simple', ?)
                            ORDER BY r.year DESC
                            LIMIT 3
                        ", [$question]);
                        foreach ($regDocs as $row) {
                            $exists = $contextResults->contains(fn($c) => $c->title === $row->title && $c->year === $row->year);
                            if (!$exists) {
                                $contextResults->add($row);
                            }
                        }
                    } catch (\Exception $e) {
                        // fallback like
                        try {
                            foreach (DB::select("
                                SELECT r.title, r.number, r.year, r.category,
                                       NULL AS article_number,
                                       substring(r.content_text for 3000) AS content
                                FROM regulations r
                                WHERE r.title ILIKE ? OR r.content_text ILIKE ?
                                ORDER BY r.year DESC
                                LIMIT 3
                            ", ["%$question%", "%$question%"]) as $row) {
                                $contextResults->add($row);
                            }
                        } catch (\Exception $e2) {
                            // abaikan
                        }
                    }
                }
            } elseif ($driver === 'mysql') {
                $contextResults = collect(DB::select("
                    SELECT r.title, r.number, r.year, r.category, rc.article_number, rc.content
                    FROM regulation_contents rc
                    JOIN regulations r ON r.id = rc.regulation_id
                    WHERE MATCH(rc.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                    OR r.title LIKE ?
                    LIMIT 5
                ", [$question, "%$question%"]));
            } else {
                $contextResults = collect(DB::select("
                    SELECT r.title, r.number, r.year, r.category, rc.article_number, rc.content
                    FROM regulation_contents rc
                    JOIN regulations r ON r.id = rc.regulation_id
                    WHERE rc.content LIKE ?
                    OR r.title LIKE ?
                    LIMIT 5
                ", ["%$question%", "%$question%"]));
            }

            foreach ($contextResults as $row) {
                $context .= "\n[" . $row->category . " " . $row->number . "/" . $row->year . " - " . $row->title . " " . ($row->article_number ?? '') . "]:\n";
                $context .= mb_substr($row->content ?? '', 0, 2000) . "\n";
            }

            // 1b) RETRIEVE dari database lokal: putusan (yurisprudensi)
            $putusanResults = Putusan::published()
                ->search($question)
                ->latest('tanggal_putusan')
                ->limit(3)
                ->get();

            foreach ($putusanResults as $p) {
                $context .= "\n[Putusan " . $p->nomor_putusan . " - " . $p->nama_pengadilan . " (" . $p->golongan_perkara . ")]:\n";
                $context .= "Ringkasan: " . mb_substr($p->ringkasan_putusan ?? '', 0, 1500) . "\n";
                if ($p->isi_putusan) {
                    $context .= "Isi: " . mb_substr($p->isi_putusan, 0, 2000) . "\n";
                }
            }

            // 2) RETRIEVE live dari sumber resmi (peraturan.bpk.go.id + situs .go.id via search)
            $live = app(LegalSourceService::class)->getContext($question, 2);
            if (!empty($live['context'])) {
                $context .= "\n\n--- KONTEKS LANGSUNG DARI SUMBER RESMI PEMERINTAH (live retrieval) ---\n"
                    . $live['context'];
                $sources = array_merge($sources, $live['sources']);
            }
        } catch (\Exception $e) {
            $contextResults = [];
        }

        $systemPrompt = "Anda adalah LEXLAW Legal Intelligence. Jawab pertanyaan hukum Indonesia berdasarkan konteks regulasi. Berikan sitasi pasal.\n\nPENGETAHUAN AI:\n- Pengetahuan Anda TIDAK terbatas pada data regulasi di database aplikasi ini saja.\n- Selalu up-to-date dengan seluruh peraturan perundang-undangan Indonesia (pusat dan daerah) yang berlaku sampai saat ini.\n- Dasarkan semua pengetahuan hukum pada situs-situs resmi pemerintah, baik pemerintah daerah maupun pemerintah pusat (contoh: peraturan.go.id, jdih.kemenkumham.go.id, peraturan.bpk.go.id, serta JDIH provinsi/kabupaten/kota).\n- Jika tidak ada di konteks, jawab berdasarkan pengetahuan hukum umum Indonesia yang akurat dan terbaru.\n\nWAJIB: Jika jawaban memakai informasi dari KONTEKS LANGSUNG DARI SUMBER RESMI atau konteks regulasi, cantumkan URL sumber resmi (link) pada akhir jawaban.";
        if ($context) {
            $systemPrompt .= "\n\nKONTEKS:\n" . $context;
        }

        try {
            $response = Http::timeout(60)->withToken(config('services.ai.key'))->post(
                config('services.ai.base_url', 'http://127.0.0.1:20128/v1') . '/chat/completions',
                [
                    'model' => config('services.ai.model', 'ARK'),
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
