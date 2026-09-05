<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use Illuminate\Http\Request;

class TeamWorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $items = TeamWorkspace::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);
        return view('team-workspaces.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        TeamWorkspace::create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]));

        return back()->with('success', 'Workspace created.');
    }
}
