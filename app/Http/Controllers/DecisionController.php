<?php

namespace App\Http\Controllers;

use App\Services\PutusanDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DecisionController extends Controller
{
    public function index()
    {
        return view('decision.index');
    }

    public function getCourts()
    {
        $rows = DB::table('master_pn')
            ->select('pn_name', 'pn_url')
            ->orderBy('pn_name')
            ->get();

        $courts = [];
        foreach ($rows as $row) {
            $raw = trim($row->pn_url);
            $raw = basename($raw);
            $raw = preg_replace('/\.html$/i', '', $raw);
            $slug = strtolower(trim($raw));
            if (!str_starts_with($slug, 'pn-')) continue;
            $courts[] = [
                'slug' => $slug,
                'nama' => trim($row->pn_name),
            ];
        }
        return response()->json(['courts' => $courts]);
    }

    public function getCategories(Request $request)
    {
        $cats = [];
        foreach (PutusanDirectoryService::KATEGORI as $slug => $info) {
            $cats[] = ['slug' => $slug, 'label' => $info['label'], 'count' => $info['count']];
        }
        return response()->json(['categories' => $cats]);
    }

    public function fetchDecisions(Request $request)
    {
        $pn = strtolower(trim($request->query('pn') ?? ''));
        $kat = trim($request->query('kategori') ?? '');
        $thn = trim($request->query('tahun') ?? '');

        if (!$pn || !str_starts_with($pn, 'pn-')) {
            return response()->json(['error' => 'PN tidak valid'], 400);
        }
        $exists = DB::table('master_pn')->whereRaw("LOWER(TRIM(pn_url)) LIKE ?", ['%'.strtolower($pn).'%'])->exists();
        if (!$exists) {
            return response()->json(['error' => 'PN tidak valid'], 400);
        }
        if ($kat !== '' && !isset(PutusanDirectoryService::KATEGORI[$kat])) {
            return response()->json(['error' => 'Klasifikasi tidak valid'], 400);
        }
        if ($thn !== '' && !preg_match('/^\d{4}$/', $thn)) {
            return response()->json(['error' => 'Tahun tidak valid'], 400);
        }

        $base = 'https://putusan3.mahkamahagung.go.id/direktori/index/pengadilan/' . $pn;
        if ($kat !== '') {
            // MA direktori pakai format kategori/{slug}-1  contoh pidana-khusus-1
            $maSlug = PutusanDirectoryService::KATEGORI[$kat]['ma_slug'] . '-1';
            $base .= '/kategori/' . $maSlug;
        }
        if ($thn !== '') {
            $base .= '/tahun/' . $thn . '.html';
        } else {
            // kalau tanpa tahun tetap .html agar valid
            if (!str_ends_with($base, '.html')) $base .= '.html';
        }

        $pnRow = DB::table('master_pn')->whereRaw("LOWER(TRIM(pn_url)) LIKE ?", ['%'.strtolower($pn).'%'])->first();
        $info = 'PN: ' . trim($pnRow->pn_name);
        if ($kat !== '') $info .= ' | ' . PutusanDirectoryService::KATEGORI[$kat]['label'] . ' (' . PutusanDirectoryService::KATEGORI[$kat]['count'] . ')';
        if ($thn !== '') $info .= ' | Tahun ' . $thn;

        return response()->json([
            'success' => true,
            'directori_url' => $base,
            'filterInfo' => $info,
            'action' => 'redirect',
            'pn' => $pn,
            'kategori' => $kat,
            'tahun' => $thn,
        ]);
    }

    public function import(Request $request)
    {
        $pn = strtolower(trim($request->input('pn') ?? ''));
        $kategori = trim($request->input('kategori') ?? '');
        $tahun = trim($request->input('tahun') ?? '');
        $limit = min(60, max(5, (int) ($request->input('limit') ?? 15)));
        $withPdf = (bool) $request->input('with_pdf', false);

        if (!$pn || !preg_match('/^pn-[a-z0-9-]+$/', $pn)) {
            return response()->json(['error' => 'PN tidak valid'], 422);
        }
        if ($kategori !== '' && !isset(PutusanDirectoryService::KATEGORI[$kategori])) {
            return response()->json(['error' => 'Klasifikasi tidak valid'], 422);
        }
        if ($tahun !== '' && !preg_match('/^\d{4}$/', $tahun)) {
            return response()->json(['error' => 'Tahun tidak valid'], 422);
        }

        $service = app(PutusanDirectoryService::class);
        $summary = $service->import($pn, $kategori, $tahun, $limit, 3, 5, $withPdf);

        $message = $summary['errors'] > 0 && $summary['attempted'] === 0
            ? 'Tidak ada snapshot arsip untuk pengadilan ini (atau akses gagal).'
            : 'Seluruh data disimpan sebagai DRAFT — publish via admin sebelum tampil publik.';

        return response()->json([
            'success' => $summary['errors'] === 0 || $summary['attempted'] > 0,
            'message' => $message,
            'summary' => $summary,
        ]);
    }
}
