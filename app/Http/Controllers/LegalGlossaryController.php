<?php

namespace App\Http\Controllers;

use App\Models\LegalGlossary;
use Illuminate\Http\Request;

class LegalGlossaryController extends Controller
{
    public function index(Request $request)
    {
        $items = LegalGlossary::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);
        return view('legal-glossary.index', compact('items'));
    }

    public function create()
    {
        return view('legal-glossary.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => 'required|max:255',
            'definition' => 'required',
            'category' => 'nullable|max:100',
        ]);

        LegalGlossary::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
        ]));

        return redirect('/legal-glossary')->with('success', 'Term added.');
    }

    public function update(Request $request, LegalGlossary $legalGlossary)
    {
        $validated = $request->validate([
            'definition' => 'required',
        ]);
        $legalGlossary->update($validated);
        return back()->with('success', 'Term updated.');
    }

    public function destroy(LegalGlossary $legalGlossary)
    {
        $legalGlossary->delete();
        return back()->with('success', 'Term deleted.');
    }
}
