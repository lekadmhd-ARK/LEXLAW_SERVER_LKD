<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceNote;
use Illuminate\Http\Request;

class WorkspaceNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $workspace = $request->route('workspace');
            if ($workspace->tenant_id !== $request->user()->tenant_id) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function store(Request $request, TeamWorkspace $workspace)
    {
        $validated = $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);

        WorkspaceNote::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $request->user()->id,
            'title'        => $validated['title'],
            'content'      => $validated['content'],
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    public function update(Request $request, TeamWorkspace $workspace, WorkspaceNote $note)
    {
        $validated = $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);

        $note->update($validated);

        return back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy(Request $request, TeamWorkspace $workspace, WorkspaceNote $note)
    {
        $note->delete();
        return back()->with('success', 'Catatan berhasil dihapus.');
    }
}
