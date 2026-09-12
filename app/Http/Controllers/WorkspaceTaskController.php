<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceTask;
use Illuminate\Http\Request;

class WorkspaceTaskController extends Controller
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
            'title'        => 'required|max:255',
            'description'  => 'nullable',
            'assigned_to'  => 'nullable|exists:users,id',
            'priority'     => 'nullable|in:low,normal,high,urgent',
            'due_date'     => 'nullable|date',
        ]);

        WorkspaceTask::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $request->user()->id,
            'assigned_to'  => $validated['assigned_to'] ?? null,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'priority'     => $validated['priority'] ?? 'normal',
            'due_date'     => $validated['due_date'] ?? null,
            'status'       => 'todo',
        ]);

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, TeamWorkspace $workspace, WorkspaceTask $task)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|max:255',
            'description'  => 'sometimes|nullable',
            'assigned_to'  => 'sometimes|nullable|exists:users,id',
            'status'       => 'sometimes|in:todo,in_progress,done,cancelled',
            'priority'     => 'sometimes|in:low,normal,high,urgent',
            'due_date'     => 'sometimes|nullable|date',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'done' && !$task->completed_at) {
            $validated['completed_at'] = now();
        } elseif (isset($validated['status']) && $validated['status'] !== 'done') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, TeamWorkspace $workspace, WorkspaceTask $task)
    {
        $task->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function toggleStatus(Request $request, TeamWorkspace $workspace, WorkspaceTask $task)
    {
        $task->status = $task->status === 'done' ? 'todo' : 'done';
        $task->completed_at = $task->status === 'done' ? now() : null;
        $task->save();

        return back()->with('success', 'Status tugas diperbarui.');
    }
}
