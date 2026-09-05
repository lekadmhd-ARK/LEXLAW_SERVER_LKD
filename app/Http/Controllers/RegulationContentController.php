<?php

namespace App\Http\Controllers;

use App\Models\RegulationContent;
use Illuminate\Http\Request;

class RegulationContentController extends Controller
{
    public function index(Request $request)
    {
        $items = RegulationContent::latest()->paginate(20);
        return view('regulations.index', ['regulations' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'regulation_id' => 'required|exists:regulations,id',
            'article_number' => 'nullable|max:50',
            'article_title' => 'nullable|max:255',
            'content' => 'nullable',
        ]);

        RegulationContent::create($validated);
        return back()->with('success', 'Content added.');
    }

    public function update(Request $request, RegulationContent $regulationContent)
    {
        $validated = $request->validate([
            'content' => 'nullable',
        ]);
        $regulationContent->update($validated);
        return back()->with('success', 'Content updated.');
    }
}
