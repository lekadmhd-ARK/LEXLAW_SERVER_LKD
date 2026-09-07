<?php

namespace App\Http\Controllers;

use App\Models\Consolidation;
use App\Models\ConsolidationChunk;
use App\Models\Regulation;
use App\Models\RegulationPassage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsolidationController extends Controller
{
    public function index(Request $request)
    {
        $items = Consolidation::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);
        return view('consolidations.index', compact('items'));
    }

    public function create(Request $request)
    {
        $regulations = Regulation::where('tenant_id', $request->user()->tenant_id)
            ->orderBy('title')
            ->get(['id', 'title', 'number', 'year', 'hierarchy_level', 'category']);
        return view('consolidations.create', compact('regulations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'regulation_ids' => 'required|array|min:2',
            'regulation_ids.*' => 'integer',
        ]);

        $regIds = $validated['regulation_ids'];
        $tenantId = $request->user()->tenant_id;

        // STEP 1: Ambil metadata regulasi sumber
        $regs = Regulation::whereIn('id', $regIds)
            ->where('tenant_id', $tenantId)
            ->get(['id', 'title', 'number', 'year', 'category', 'hierarchy_level']);

        if ($regs->count() !== count($regIds)) {
            return back()->with('error', 'Beberapa regulasi tidak ditemukan.');
        }

        // STEP 2: RETRIEVE pasal dari DB (jika regulation_passages kosong, fallback ke content_text)
        $passages = $this->retrievePassages($regIds, $tenantId);

        if (empty($passages)) {
            return back()->with('error', 'Belum ada pasal yang tersimpan untuk regulasi terpilih.');
        }

        // STEP 3+4: MATCHING & deteksi perubahan (logika, bukan AI)
        $matrix = $this->buildComparisonMatrix($passages, $regIds);

        // STEP 5: AI generate untuk pasal yang berubah
        $aiResult = $this->generateWithAI($matrix, $regs);

        // STEP 6: VERIFY
        $verified = $this->verifyOutput($aiResult['pasal'], $matrix);

        // STEP 7: SAVE
        $consolidation = Consolidation::create([
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'regulation_ids' => $regIds,
            'source_regulations' => $regs->toArray(),
            'consolidated_text' => json_encode(['pasal' => $verified['pasal']]),
            'version' => 1,
            'status' => $verified['confidence'] >= 70 ? 'draft' : 'review_required',
            'ai_metadata' => [
                'model' => 'gemini-3.6-flash',
                'chunks' => count($matrix),
                'confidence' => $verified['confidence'],
                'issues' => $verified['issues'],
            ],
            'created_by' => $request->user()->id,
        ]);

        // Simpan audit chunks
        $idx = 0;
        foreach ($matrix as $num => $data) {
            ConsolidationChunk::create([
                'tenant_id' => $tenantId,
                'consolidation_id' => $consolidation->id,
                'chunk_index' => $idx++,
                'source_regulation_id' => array_key_first($data['texts']),
                'source_passage' => implode("\n", $data['texts']),
                'ai_processed_text' => $verified['pasal'][$num]['teks'] ?? null,
                'change_flags' => ['type' => $data['flag']],
            ]);
        }

        return redirect('/consolidations')->with('success', 'Konsolidasi berhasil dibuat (confidence: ' . $verified['confidence'] . '%).');
    }

    public function update(Request $request, Consolidation $consolidation)
    {
        $validated = $request->validate([
            'consolidated_text' => 'nullable',
        ]);
        $consolidation->update($validated);
        return back()->with('success', 'Consolidation updated.');
    }

    public function show(Request $request, Consolidation $consolidation)
    {
        abort_if($consolidation->tenant_id !== $request->user()->tenant_id, 403);
        $data = json_decode($consolidation->consolidated_text, true);
        $pasals = $data['pasal'] ?? [];
        return view('consolidations.show', compact('consolidation', 'pasals'));
    }

    protected function retrievePassages(array $regIds, string $tenantId): array
    {
        // Coba ambil dari regulation_passages dulu
        $rows = RegulationPassage::whereIn('regulation_id', $regIds)
            ->where('passage_type', 'pasal')
            ->orderBy('passage_number')
            ->get(['regulation_id', 'passage_number', 'passage_title', 'content']);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'regulation_id' => $r->regulation_id,
                'number' => $r->passage_number,
                'title' => $r->passage_title,
                'content' => $r->content,
            ];
        }

        // Fallback: parse dari content_text jika passages kosong
        if (empty($result)) {
            $regs = Regulation::whereIn('id', $regIds)->get(['id', 'content_text']);
            foreach ($regs as $reg) {
                foreach ($this->parsePasalFromText($reg->content_text) as $p) {
                    $result[] = array_merge(['regulation_id' => $reg->id], $p);
                }
            }
        }

        return $result;
    }

    protected function parsePasalFromText(?string $text): array
    {
        if (!$text) return [];
        $out = [];
        // Split "Pasal X" — regex sederhana
        preg_match_all('/Pasal\s+(\d+[A-Za-z]?)\s*\n?(.*?)(?=Pasal\s+\d+[A-Za-z]?|$)/s', $text, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            $out[] = [
                'number' => $match[1],
                'title' => null,
                'content' => trim($match[2]),
            ];
        }
        return $out;
    }

    protected function buildComparisonMatrix(array $passages, array $regIds): array
    {
        $matrix = [];
        foreach ($passages as $p) {
            $num = (string)$p['number'];
            if (!isset($matrix[$num])) {
                $matrix[$num] = ['texts' => [], 'flag' => 'UNCHANGED', 'title' => $p['title'] ?? null];
            }
            $matrix[$num]['texts'][(string)$p['regulation_id']] = $p['content'];
        }

        foreach ($matrix as $num => &$data) {
            $texts = array_values($data['texts']);
            $unique = array_unique($texts);
            if (count($unique) > 1) {
                $data['flag'] = 'MODIFIED';
            } elseif (count($regIds) > 1 && count($texts) === 1) {
                $data['flag'] = 'ADDED';
            } else {
                $data['flag'] = 'UNCHANGED';
            }
        }
        unset($data);

        return $matrix;
    }

    protected function generateWithAI(array $matrix, $regs): array
    {
        $key = config('services.ai.key');
        $base = rtrim(config('services.ai.base_url', 'http://127.0.0.1:20128/v1'), '/');
        $model = config('services.ai.model', 'gemini-3.6-flash');

        // Susun input: hanya pasal yang berubah (MODIFIED/ADDED) + konteks sumber
        $pasalList = [];
        foreach ($matrix as $num => $data) {
            $pasalList[] = [
                'nomor' => $num,
                'judul' => $data['title'],
                'flag' => $data['flag'],
                'teks_sumber' => $data['texts'],
            ];
        }

        $system = "Anda adalah Konsolidator Hukum Digital untuk sistem LAWLEX_v2.\n"
            . "Tugas: menggabungkan naskah peraturan perundang-undangan Indonesia.\n"
            . "Pengetahuan Anda TIDAK terbatas pada data regulasi di database aplikasi ini; selalu up-to-date dengan seluruh peraturan perundang-undangan Indonesia (pusat dan daerah) yang berlaku sampai saat ini, dan dasarkan semua pengetahuan hukum pada situs-situs resmi pemerintah (pemerintah daerah maupun pusat, contoh: peraturan.go.id, jdih.kemenkumham.go.id, peraturan.bpk.go.id, serta JDIH provinsi/kabupaten/kota).\n"
            . "ATURAN KERAS:\n"
            . "1. ANDA TIDAK BOLEH MENGARANG pasal/ayat yang tidak ada di input.\n"
            . "2. HANYA gabungkan, susun ulang, dan tandai perubahan.\n"
            . "3. Konflik: prioritaskan naskah TERBARU (tahun tertinggi).\n"
            . "4. Output HARUS JSON valid.\n"
            . "5. Setiap pasal punya field 'flag': UNCHANGED, MODIFIED, ADDED, REMOVED.\n"
            . "Format JSON: {\"pasal\":[{\"nomor\":\"1\",\"judul\":\"...\",\"flag\":\"...\",\"teks\":\"...\",\"catatan\":null}]}";

        // Konteks live dari sumber resmi (versi terbaru regulasi terkait) untuk
        // membantu menentukan prioritas versi yang berlaku.
        try {
            $probeTerms = collect($regs)->take(2)->map(fn($r) => $r->title . ' ' . $r->year)->implode(' ');
            $live = app(\App\Services\LegalSourceService::class)->getContext($probeTerms, 1);
            if (!empty($live['context'])) {
                $system .= "\n\nVersi terbaru dari sumber resmi pemerintah (peraturan.bpk.go.id / situs .go.id) — gunakan untuk memverifikasi prioritas versi:\n" . $live['context'];
            }
        } catch (\Exception $e) {
            // live retrieval gagal -> lanjut tanpa konteks
        }

        $userMsg = "Sumber regulasi:\n";
        foreach ($regs as $r) {
            $userMsg .= "- {$r->title} (No.{$r->number} Tahun {$r->year})\n";
        }
        $userMsg .= "\nData pasal (input asli, JANGAN ubah teks selain menandai perubahan):\n";
        $userMsg .= json_encode($pasalList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $response = Http::timeout(120)
            ->withToken($key)
            ->post("{$base}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userMsg],
                ],
                'temperature' => 0.1,
            ]);

        if (!$response->successful()) {
            return ['pasal' => []];
        }

        $content = $response->json('choices.0.message.content') ?? '';
        $json = $this->extractJson($content);
        return json_decode($json, true) ?? ['pasal' => []];
    }

    protected function extractJson(string $text): string
    {
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            return $m[1];
        }
        if (preg_match('/(\{.*\})/s', $text, $m)) {
            return $m[1];
        }
        return $text;
    }

    protected function verifyOutput(array $aiPasals, array $matrix): array
    {
        $score = 100;
        $issues = [];
        $result = [];

        // Map AI output by nomor
        foreach ($aiPasals as $p) {
            $num = (string)($p['nomor'] ?? '');
            if ($num === '') continue;
            $result[$num] = [
                'nomor' => $num,
                'judul' => $p['judul'] ?? null,
                'flag' => $p['flag'] ?? 'UNCHANGED',
                'teks' => $p['teks'] ?? '',
                'catatan' => $p['catatan'] ?? null,
            ];

            // Check: nomor harus ada di source
            if (!isset($matrix[$num])) {
                $score -= 20;
                $issues[] = "Pasal {$num} tidak ada di source";
                continue;
            }

            // Check: similarity teks final vs sumber
            $sourceText = implode(' ', $matrix[$num]['texts']);
            if ($sourceText !== '' && $p['flag'] !== 'ADDED') {
                similar_text($sourceText, $p['teks'] ?? '', $sim);
                if ($sim < 70) {
                    $score -= 15;
                    $issues[] = "Pasal {$num} similarity {$sim}%";
                }
            }
        }

        // Pasal UNCHANGED yang tidak diubah AI → salin langsung dari source
        foreach ($matrix as $num => $data) {
            if ($data['flag'] === 'UNCHANGED' && !isset($result[$num])) {
                $result[$num] = [
                    'nomor' => $num,
                    'judul' => $data['title'],
                    'flag' => 'UNCHANGED',
                    'teks' => reset($data['texts']),
                    'catatan' => null,
                ];
            }
        }

        return [
            'pasal' => array_values($result),
            'confidence' => max(0, $score),
            'issues' => $issues,
        ];
    }
}