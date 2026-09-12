<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DecisionController extends Controller
{
    private array $kategoriMap = [
        'perdata-17441'      => ['label' => 'Perdata', 'count' => 17441, 'ma_slug' => 'perdata'],
        'pidana-umum-5237'   => ['label' => 'Pidana Umum', 'count' => 5237, 'ma_slug' => 'pidana-umum'],
        'pidana-khusus-5065' => ['label' => 'Pidana Khusus', 'count' => 5065, 'ma_slug' => 'pidana-khusus'],
        'perdata-agama-1665' => ['label' => 'Perdata Agama', 'count' => 1665, 'ma_slug' => 'perdata-agama'],
        'perdata-khusus-12'  => ['label' => 'Perdata Khusus', 'count' => 12, 'ma_slug' => 'perdata-khusus'],
        'tun-1'              => ['label' => 'TUN', 'count' => 1, 'ma_slug' => 'tun'],
    ];

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
        foreach ($this->kategoriMap as $slug => $info) {
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
        if ($kat !== '' && !isset($this->kategoriMap[$kat])) {
            return response()->json(['error' => 'Klasifikasi tidak valid'], 400);
        }
        if ($thn !== '' && !preg_match('/^\d{4}$/', $thn)) {
            return response()->json(['error' => 'Tahun tidak valid'], 400);
        }

        $base = 'https://putusan3.mahkamahagung.go.id/direktori/index/pengadilan/' . $pn;
        if ($kat !== '') {
            // MA direktori pakai format kategori/{slug}-1  contoh pidana-khusus-1
            $maSlug = $this->kategoriMap[$kat]['ma_slug'] . '-1';
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
        if ($kat !== '') $info .= ' | ' . $this->kategoriMap[$kat]['label'] . ' (' . $this->kategoriMap[$kat]['count'] . ')';
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
}
