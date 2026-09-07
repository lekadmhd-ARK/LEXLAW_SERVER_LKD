<?php

namespace App\Http\Controllers;

use App\Models\Putusan;
use Illuminate\Http\Request;

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
}