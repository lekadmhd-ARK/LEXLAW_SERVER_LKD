<?php

namespace App\Http\Controllers;

use App\Models\Consolidation;
use Illuminate\Http\Request;

class ConsolidationController extends Controller
{
    public function index(Request $request)
    {
        $items = Consolidation::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);
        return view('consolidations.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'regulation_ids' => 'nullable|array',
            'consolidated_text' => 'nullable',
        ]);

        Consolidation::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]));

        return redirect('/consolidations')->with('success', 'Consolidation created.');
    }

    public function update(Request $request, Consolidation $consolidation)
    {
        $validated = $request->validate([
            'consolidated_text' => 'nullable',
        ]);
        $consolidation->update($validated);
        return back()->with('success', 'Consolidation updated.');
    }
}
