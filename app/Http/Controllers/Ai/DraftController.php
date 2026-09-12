<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\LegalSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class DraftController extends Controller
{
    public function form()
    {
        return view('ai.draft');
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'instructions' => 'required|string',
            'effective_date' => 'nullable|date',
            'variant_count' => 'nullable|integer|in:1,3',
        ]);

        $documentType = $validated['document_type'];
        $instructions = $validated['instructions'];
        $effectiveDate = $validated['effective_date'] ?? null;
        $variantCount = (int) ($validated['variant_count'] ?? 1);

        // SATU-SATUNYA SUMBER: Live retrieval dari sumber resmi pemerintah
        $context = $this->fetchRegulationContext($instructions);

        $label = $this->getDocumentLabel($documentType);
        $date = $effectiveDate ?: 'sesuai kesepakatan';

        // Gaya varian yang tersedia
        $styles = [
            ['title' => 'Formal', 'desc' => 'formal-konservatif standar notaril'],
            ['title' => 'Modern', 'desc' => 'modern-praktis dengan bahasa lebih ringkas namun tetap lengkap'],
            ['title' => 'Komprehensif', 'desc' => 'sangat detail dan mendalam, semua pasal dielaborasi maksimal'],
        ];

        if ($variantCount > 1) {
            $drafts = [];
            foreach ($styles as $i => $style) {
                if ($i >= $variantCount) break;
                $drafts[] = $this->generateDraft($label, $date, $instructions, $context, $style);
            }
        } else {
            $drafts = [$this->generateDraft($label, $date, $instructions, $context, $styles[0])];
        }

        return view('ai.draft', [
            'drafts' => $drafts,
            'document_type' => $documentType,
            'instructions' => $instructions,
        ]);
    }

    protected function generateDraft($label, $date, $instructions, $context, $style)
    {
        $base = "Anda adalah Senior Legal Drafter Indonesia yang sangat berpengalaman (Corporate Lawyer / Notaris). Buatlah draf dokumen hukum yang profesional, rinci, dan sesuai HUKUM POSITIF INDONESIA yang berlaku saat ini.

SUMBER INFORMASI:
- Referensi hukum di bawah ini diambil LANGSUNG dari situs resmi pemerintah Indonesia (peraturan.bpk.go.id, jdih.kemenkumham.go.id, dan situs .go.id lainnya)

ATURAN PENTING:
- Draf harus merujuk pada peraturan yang BERLAKU saat ini
- Jika di sumber resmi tertulis status 'Dicabut' atau 'Tidak Berlaku', JANGAN gunakan peraturan tersebut
- Jika ada beberapa versi UU, gunakan versi TERBARU
- Selalu cantumkan URL sumber resmi sebagai referensi
- Jika ragu, sarankan verifikasi ke peraturan.bpk.go.id atau jdih.kemenkumham.go.id

KAPABILITAS LIVE RETRIEVAL:
- Anda MENGUBAHKAN diri menjadi agen yang melakukan pencarian real-time ke situs resmi pemerintah (.go.id, BPK, JDIH) untuk memverifikasi regulasi terbaru.

";

        $gaya = "GAYA PENULISAN: " . $style['desc'] . ".\n";

        if ($style['title'] === 'Modern') {
            $gaya .= "- Gunakan kalimat aktif yang ringkas namun tetap presisi dan mengikat secara hukum.\n"
                . "- Pecah pasal-pasal kompleks menjadi ayat (1), (2), (3) dengan huruf a, b, c.\n"
                . "- Sertakan klausul praktis: definisi operasional, durasi, mekanisme pelaksanaan, jaminan, dan penyelesaian sengketa.\n";
        } elseif ($style['title'] === 'Komprehensif') {
            $gaya .= "- Uraikan SETIAP pasal dengan ayat-ayat terperinci dan huruf a, b, c, dst.\n"
                . "- Elaborasi maksimal: definisi istilah, hak & kewajiban para pihak, kewajiban pembayaran, jangka waktu, wanprestasi & sanksi (denda, ganti rugi), force majeure, kerahasiaan, perlindungan data pribadi (UU PDP), penyelesaian sengketa (musyawarah, mediasi, arbitrase BANI, pengadilan negeri), hukum yang berlaku, perubahan & pengalihan, dan ketentuan penutup.\n"
                . "- Cantumkan tabel/nomor pasal yang jelas. Dokumen harus panjang, lengkap, dan menyeluruh.\n";
        } else {
            $gaya .= "- Gaya formal-konservatif standar notaril, struktur klasik, bahasa baku yang tegas.\n";
        }

        $struktur = "STRUKTUR WAJIB:\n"
            . "1. JUDUL (rata tengah)\n"
            . "2. KOMPARISI: identitas lengkap para pihak (nama, NIK/NIB, jabatan, alamat, kedudukan hukum)\n"
            . "3. PREMIS/RECITALS: latar belakang dan maksud para pihak\n"
            . "4. DEFINISI: istilah kunci dalam kontrak\n"
            . "5. PASAL-PASAL: substansi pokok (objek, hak & kewajiban, jangka waktu, pembayaran/biaya, wanprestasi, force majeure, kerahasiaan, penyelesaian sengketa, hukum yang berlaku)\n"
            . "6. PENUTUP: kata penutup dan blok tanda tangan\n\n";

        $html = "FORMAT HTML:\n"
            . "- Judul: <h3 align=\"center\">JUDUL</h3>\n"
            . "- Pasal: gunakan <strong>Pasal 1</strong> diikuti isi, atau <ol><li> bila perlu\n"
            . "- Tanda tangan: dua blok sejajar kiri-kanan, masing-masing dengan ruang \"Materai Rp 10.000\" dan nama jelas + jabatan\n"
            . "- JANGAN gunakan Markdown (** **). Gunakan <strong> untuk penebalan.\n\n";

        $systemPrompt = $base . $gaya . "\n" . $struktur . $html
            . ($context ? "REFERENSI HUKUM DARI SUMBER RESMI PEMERINTAH:\n" . $context . "\n" : "Tidak berhasil mengambil referensi dari sumber resmi. Buat draf berdasarkan pengetahuan hukum positif Indonesia yang berlaku.\n")
            . "JENIS DOKUMEN: " . $label . "\n"
            . "TANGGAL EFEKTIF: " . $date . "\n"
            . "INSTRUKSI / KASUS: " . $instructions;

        try {
            $response = Http::timeout(180)->withToken(env('AI_API_KEY'))->post(
                config('services.ai.base_url', 'http://127.0.0.1:20128/v1') . '/chat/completions',
                [
                    'model' => config('services.ai.model', 'ARK'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Buat draf lengkap " . $label . " (gaya: " . $style['title'] . ")."],
                    ],
                    'temperature' => $style['title'] === 'Formal' ? 0.2 : 0.5,
                    'stream' => false,
                    'max_tokens' => $style['title'] === 'Komprehensif' ? 6000 : 4500,
                ]
            );
            $raw = $response->json('choices.0.message.content') ?? '';
            if (trim($raw) === '') {
                return 'Gagal generate: AI tidak mengembalikan konten.';
            }
            return $this->hardCleanMarkdown($raw);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * SATU-SATUNYA SUMBER: Live retrieval dari sumber resmi pemerintah
     */
    protected function fetchRegulationContext($instructions)
    {
        $context = "";
        try {
            $live = app(LegalSourceService::class)->getContext($instructions, 5);
            if (!empty($live['context'])) {
                $context = $live['context'];
            }
        } catch (\Exception $e) {
            // live retrieval gagal
        }
        return $context;
    }

    protected function getDocumentLabel($type)
    {
        $labels = [
            'nda' => 'PERJANJIAN KERAHASIAAN (NON-DISCLOSURE AGREEMENT)',
            'kerjasama' => 'PERJANJIAN KERJASAMA (JOINT OPERATION / PARTNERSHIP)',
            'jual_beli' => 'PERJANJIAN JUAL BELI BERSYARAT',
            'sewa_menyewa' => 'PERJANJIAN SEWA MENYEWA',
            'ppjb' => 'PERJANJIAN PENGIKATAN JUAL BELI (PPJB)',
            'kontrak_kerja' => 'PERJANJIAN KERJA / KONTRAK KERJA',
            'pemborongan' => 'PERJANJIAN PEMBORONGAN / JASA',
            'kredit' => 'PERJANJIAN KREDIT / PINJAM-MEMINJAM',
            'waralaba' => 'PERJANJIAN WARALABA (FRANCHISE)',
            'distribusi' => 'PERJANJIAN DISTRIBUSI / KEAGENAN',
            'lisensi' => 'PERJANJIAN LISENSI',
            'mou' => 'NOTA KESEPAHAMAN (MoU)',
            'akta_pendirian' => 'AKTA PENDIRIAN PERSEROAN TERBATAS',
            'akta_perubahan' => 'AKTA PERUBAHAN PERSEROAN TERBATAS',
            'sk_direksi' => 'SURAT KEPUTUSAN DIREKSI / KOMISARIS',
            'berita_acara_rups' => 'BERITA ACARA RUPS',
            'surat_kuasa' => 'SURAT KUASA',
            'surat_keputusan' => 'SURAT KEPUTUSAN (SK)',
            'surat_edaran' => 'SURAT EDARAN',
            'pkwt' => 'PERJANJIAN KERJA WAKTU TERTENTU (PKWT)',
            'surat_peringatan' => 'SURAT PERINGATAN (SP)',
            'pengangkatan' => 'SURAT PENGANGKATAN KARYAWAN',
            'phk' => 'SURAT PEMUTUSAN HUBUNGAN KERJA (PHK)',
            'pkb' => 'PERJANJIAN KERJA BERSAMA (PKB)',
            'mutasi' => 'SURAT MUTASI / ROTASI',
            'pengunduran_diri' => 'SURAT PENGUNDURAN DIRI',
            'permohonan' => 'SURAT PERMOHONAN',
            'pemberitahuan' => 'SURAT PEMBERITAHUAN',
            'undangan' => 'SURAT UNDANGAN',
            'keterangan' => 'SURAT KETERANGAN',
            'rekomendasi' => 'SURAT REKOMENDASI',
            'pernyataan' => 'SURAT PERNYATAAN',
            'surat_tugas' => 'SURAT TUGAS',
            'wasiat' => 'SURAT WASIAT (TESTAMEN)',
            'pra_nikah' => 'PERJANJIAN PRA-NIKAH',
            'hibah' => 'PERJANJIAN HIBAH',
            'kuasa_jual' => 'SURAT KUASA JUAL',
            'pengakuan_utang' => 'SURAT PENGAKUAN UTANG',
            'kuasa_khusus' => 'SURAT KUASA KHUSUS',
            'gugatan' => 'SURAT GUGATAN',
            'somasi' => 'SOMASI / SURAT TEGURAN',
            'pledoi' => 'SURAT PEMBELAAN (PLEDOI)',
        ];
        return $labels[$type] ?? strtoupper(str_replace('_', ' ', $type));
    }

    protected function hardCleanMarkdown($text)
    {
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/^#+\s+(.+)$/m', '<h3 align="center">$1</h3>', $text);
        $text = trim($text);
        $text = preg_replace('/\n\s*\n/', "</p><p>", $text);
        $text = "<p>" . $text . "</p>";
        return $text;
    }

    public function download(Request $request)
    {
        if (!$request->has('content')) abort(404);
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $cleanText = strip_tags($request->input('content'));
        $section->addText($cleanText);
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $tempPath = sys_get_temp_dir() . '/lawlex_' . uniqid() . '.docx';
        $objWriter->save($tempPath);
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
