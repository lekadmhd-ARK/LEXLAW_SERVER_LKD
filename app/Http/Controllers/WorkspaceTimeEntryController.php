<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeEntry;
use Illuminate\Http\Request;

class WorkspaceTimeEntryController extends Controller
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
            'description' => 'nullable|max:500',
            'task_id'    => 'nullable|exists:workspace_tasks,id',
            'hours'      => 'required|integer|min:0|max:23',
            'minutes'    => 'required|integer|min:0|max:59',
            'billable'   => 'sometimes|boolean',
            'entry_date' => 'required|date',
        ]);

        $totalMinutes = ($validated['hours'] * 60) + $validated['minutes'];

        if ($totalMinutes === 0) {
            return back()->with('error', 'Durasi waktu tidak boleh 0.');
        }

        WorkspaceTimeEntry::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $request->user()->id,
            'task_id'      => $validated['task_id'] ?? null,
            'description'  => $validated['description'] ?? null,
            'minutes'      => $totalMinutes,
            'billable'     => $request->boolean('billable', true),
            'entry_date'   => $validated['entry_date'],
        ]);

        return back()->with('success', 'Waktu berhasil dicatat.');
    }

    public function destroy(Request $request, TeamWorkspace $workspace, WorkspaceTimeEntry $entry)
    {
        $entry->delete();
        return back()->with('success', 'Entry waktu berhasil dihapus.');
    }
}
