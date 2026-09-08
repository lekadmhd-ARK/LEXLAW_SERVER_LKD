<?php

namespace App\Http\Controllers;

use App\Models\Putusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PutusanController extends Controller
{
    public function index(Request $request)
    {
        $query = Putusan::published()->latest('tanggal_putusan');

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        if ($request->filled('jenis_pengadilan')) {
            $query->byJenis($request->jenis_pengadilan);
        }

        if ($request->filled('golongan_perkara')) {
            $query->byGolongan($request->golongan_perkara);
        }

        if ($request->filled('tingkat_pengadilan')) {
            $query->where('tingkat_pengadilan', $request->tingkat_pengadilan);
        }

        if ($request->filled('status_putusan')) {
            $query->where('status_putusan', $request->status_putusan);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_putusan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_putusan', '<=', $request->tanggal_sampai);
        }

        $putusans = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Putusan::published()->count(),
            'ma' => Putusan::published()->where('jenis_pengadilan', 'MA')->count(),
            'pt' => Putusan::published()->where('jenis_pengadilan', 'PT')->count(),
            'pn' => Putusan::published()->where('jenis_pengadilan', 'PN')->count(),
            'ptun' => Putusan::published()->where('jenis_pengadilan', 'PTUN')->count(),
        ];

        return view('putusans.index', compact('putusans', 'stats'));
    }

    public function show(Putusan $putusan)
    {
        $related = Putusan::published()
            ->where('id', '!=', $putusan->id)
            ->where(function ($q) use ($putusan) {
                $q->where('golongan_perkara', $putusan->golongan_perkara)
                  ->orWhere('jenis_pengadilan', $putusan->jenis_pengadilan);
            })
            ->latest('tanggal_putusan')
            ->limit(5)
            ->get();

        return view('putusans.show', compact('putusan', 'related'));
    }

    public function analyze(Putusan $putusan)
    {
        $apiKey = config('services.ai.api_key', config('services.ai.key'));
        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.model', 'ARK');

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'AI gateway belum dikonfigurasi.',
            ], 500);
        }

        $isi = mb_substr(trim($putusan->isi_putusan ?? ''), 0, 30000);
        $ringkasan = mb_substr(trim($putusan->ringkasan_putusan ?? ''), 0, 3000);
        $pasal = $putusan->pasal_dikutip ?? [];
        $pasalTxt = is_array($pasal) ? implode("\n- ", $pasal) : (string) $pasal;

        $userPrompt = "Nomor Putusan: {$putusan->nomor_putusan}
Pengadilan: {$putusan->nama_pengadilan} ({$putusan->jenis_pengadilan})
Golongan Perkara: {$putusan->golongan_perkara}
Tingkat: {$putusan->tingkat_pengadilan}
Tanggal Putusan: " . ($putusan->tanggal_putusan?->format('d M Y') ?? '-') . "
Status: {$putusan->status_putusan}

Para Pihak:
" . ($putusan->para_pihak ?? '-') . "

Ringkasan Putusan (headnote):
$ringkasan

Pasal yang dikutip:
" . ($pasalTxt ?: '-') . "

Isi Putusan Lengkap:
$isi";

        $systemPrompt = "Anda adalah analis hukum Indonesia senior spesialis yurisprudensi dan putusan pengadilan. Pengetahuan Anda TIDAK terbatas pada data di database aplikasi ini; selalu dasarkan pada seluruh putusan, peraturan perundang-undangan Indonesia (pusat dan daerah) yang terbaru, dan situs resmi pemerintah (.go.id, mahkamahagung.go.id, peraturan.bpk.go.id, jdih.kemenkumham.go.id).

Analisis putusan berikut secara profesional dan hasilkan dalam bahasa Indonesia dengan format Markdown, berisi bagian-bagian:

## Ringkasan Eksekutif
(Jelaskan singkat: apa isu hukum, siapa pihak berperkara, dan apa amar/putusan pengadilan dalam 3-5 kalimat)

## Prinsip Hukum (Ratione Decidendi)
(Pertimbangan hukum utama yang menjadi dasar majelis menjatuhkan putusan: pasal-pasal yang diterapkan, penafsiran hukum kunci, dan alasan majelis)

## Kaidah Hukum / Yurisprudensi
(Jelaskan kaidah hukum yang dapat ditarik dari putusan ini — prinsip yang bisa dijadikan rujukan untuk kasus serupa)

## Analisis Perspektif Para Pihak
(Ringkas posisi dan argumentasi penggugat/pemohon vs tergugat/termohon, serta siapa yang menang dan implikasinya)

## Relevansi & Implikasi
(Siapa yang sebaiknya peduli dengan putusan ini: praktisi hukum, compliance, bisnis, masyarakat; dan dampaknya terhadap praktik hukum)

## Pasal Kunci
(Daftar pasal undang-undang/regulasi yang menjadi dasar putusan, lengkap dengan keterangan singkat relevansinya)

Gunakan bahasa Indonesia yang profesional dan lugas. Jika isi putusan tidak tersedia/terlalu ringkas, tetap berikan analisis berdasarkan ringkasan dan data meta yang ada, dan nyatakan keterbatasan datanya. Jangan mengarang fakta yang tidak ada di dalam putusan.";

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                    'stream' => false,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI gateway error (' . $response->status() . '). Silakan coba lagi.',
                ], 500);
            }

            $answer = $response->json('choices.0.message.content');
            if (empty(trim((string) $answer))) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak menghasilkan respons. Silakan coba lagi.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'answer' => $answer,
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