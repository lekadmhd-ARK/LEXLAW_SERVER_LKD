<?php

namespace App\Http\Controllers;

use App\Models\LegalGlossary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegalGlossaryController extends Controller
{
    /**
     * Scope query agar mencakup data tenant user + data bersama (shared).
     */
    protected function scopeFor(Request $request)
    {
        $tenantId = data_get($request->user(), 'tenant_id');
        return LegalGlossary::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId ?: 'shared')
              ->orWhere('tenant_id', 'shared');
        });
    }

    public function index(Request $request)
    {
        $query = $this->scopeFor($request);

        // Filter kategori
        if ($cat = $request->get('category')) {
            $query->where('kategori_hukum', $cat);
        }

        // Filter huruf awal (indeks A-Z)
        if ($letter = $request->get('letter')) {
            $query->where('term', 'ILIKE', $letter . '%');
        }

        // Search: ILIKE (fuzzy) pada term/singkatan/definisi
        if ($q = trim($request->get('q', ''))) {
            $q = addslashes($q);
            $query->where(function ($w) use ($q) {
                $w->where('term', 'ILIKE', "%{$q}%")
                  ->orWhere('singkatan', 'ILIKE', "%{$q}%")
                  ->orWhere('definisi_singkat', 'ILIKE', "%{$q}%")
                  ->orWhere('penjelasan_lengkap', 'ILIKE', "%{$q}%");
            });
        }

        $items = $query->orderBy('term')->paginate(24)->withQueryString();

        // Daftar kategori unik untuk filter
        $categories = $this->scopeFor($request)
            ->whereNotNull('kategori_hukum')
            ->where('kategori_hukum', '<>', '')
            ->distinct()
            ->pluck('kategori_hukum')
            ->sort()
            ->values();

        $total = $this->scopeFor($request)->count();

        return view('legal-glossary.index', compact('items', 'categories', 'total'));
    }

    public function show(Request $request, LegalGlossary $legalGlossary)
    {
        $this->authorizeAccess($legalGlossary);

        // Istilah terkait (kategori sama, selain dirinya)
        $related = $this->scopeFor($request)
            ->where('id', '<>', $legalGlossary->id)
            ->when($legalGlossary->kategori_hukum, function ($q) use ($legalGlossary) {
                $q->where('kategori_hukum', $legalGlossary->kategori_hukum);
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('legal-glossary.show', compact('legalGlossary', 'related'));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $items = $this->scopeFor($request)
            ->where(function ($w) use ($q) {
                $w->where('term', 'ILIKE', "%{$q}%")
                  ->orWhere('singkatan', 'ILIKE', "%{$q}%");
            })
            ->orderByRaw("CASE WHEN lower(term) = lower(?) THEN 0 WHEN lower(term) ILIKE ? THEN 1 ELSE 2 END", [$q, "{$q}%"])
            ->limit(8)
            ->get(['id', 'term', 'singkatan', 'kategori_hukum', 'definisi_singkat']);

        return response()->json([
            'results' => $items->map(fn ($i) => [
                'id' => $i->id,
                'term' => $i->term,
                'singkatan' => $i->singkatan,
                'kategori' => $i->kategori_hukum,
                'definisi' => $i->definisi_singkat,
            ]),
        ]);
    }

    public function create()
    {
        return view('legal-glossary.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => 'required|max:255',
            'singkatan' => 'nullable|max:100',
            'kategori_hukum' => 'nullable|max:50',
            'definisi_singkat' => 'nullable',
            'penjelasan_lengkap' => 'required',
            'contoh_implementasi' => 'nullable',
        ]);

        LegalGlossary::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
        ]));

        return redirect('/legal-glossary')->with('success', 'Istilah ditambahkan.');
    }

    public function edit(Request $request, LegalGlossary $legalGlossary)
    {
        $this->authorizeEdit($legalGlossary);
        return view('legal-glossary.edit', compact('legalGlossary'));
    }

    public function update(Request $request, LegalGlossary $legalGlossary)
    {
        $this->authorizeEdit($legalGlossary);

        $validated = $request->validate([
            'term' => 'required|max:255',
            'singkatan' => 'nullable|max:100',
            'kategori_hukum' => 'nullable|max:50',
            'definisi_singkat' => 'nullable',
            'penjelasan_lengkap' => 'required',
            'contoh_implementasi' => 'nullable',
        ]);

        $legalGlossary->update($validated);
        return redirect('/legal-glossary')->with('success', 'Istilah diperbarui.');
    }

    public function destroy(Request $request, LegalGlossary $legalGlossary)
    {
        $this->authorizeEdit($legalGlossary);
        $legalGlossary->delete();
        return back()->with('success', 'Istilah dihapus.');
    }

    /**
     * Akses baca: milik user ATAU data bersama (shared).
     */
    protected function authorizeAccess(LegalGlossary $glossary)
    {
        $tenantId = auth()->user()->tenant_id;
        if ($glossary->tenant_id !== $tenantId && $glossary->tenant_id !== 'shared') {
            abort(403);
        }
    }

    /**
     * Akses tulis: hanya milik user sendiri, atau shared hanya untuk super admin.
     */
    protected function authorizeEdit(LegalGlossary $glossary)
    {
        $tenantId = auth()->user()->tenant_id;
        if ($glossary->tenant_id === 'shared') {
            // hanya super admin yang boleh ubah/hapus data bersama
            if (!auth()->user()->is_super_admin && auth()->user()->email !== 'admin@lexlaw.id') {
                abort(403);
            }
            return;
        }
        if ($glossary->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
