<?php

namespace App\Http\Controllers;

use App\Models\RegulationContent;
use App\Models\Regulation;
use Illuminate\Http\Request;

class RegulationContentController extends Controller
{
    public function index(Request $request)
    {
        $items = RegulationContent::latest()->paginate(20);

        $stats = [
            'total'   => Regulation::count(),
            'uu'      => Regulation::where('hierarchy_level', '1')->count(),
            'pp'      => Regulation::where('hierarchy_level', '2')->count(),
            'perpres' => Regulation::where('hierarchy_level', '3')->count(),
            'active'  => Regulation::where('is_active', true)->count(),
        ];

        return view('regulations.index', [
            'regulations'     => $items,
            'stats'           => $stats,
            'liveSuggestions' => collect(),
        ]);
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
