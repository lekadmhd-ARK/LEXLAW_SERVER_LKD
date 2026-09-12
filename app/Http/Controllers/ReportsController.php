<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTimeEntry;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user     = $request->user();
        $tenantId = $user->tenant_id;
        $isOwner  = in_array($user->role, ['owner', 'admin']);

        $workspaces = TeamWorkspace::where('tenant_id', $tenantId)->get();
        $wsIds      = $workspaces->pluck('id');

        $companyStats = [
            'total_workspaces'   => $workspaces->count(),
            'active_workspaces'  => $workspaces->where('is_active', true)->count(),
            'total_members'      => $workspaces->sum('member_count'),
            'total_documents'    => WorkspaceDocument::whereIn('workspace_id', $wsIds)->count(),
            'total_tasks'        => WorkspaceTask::whereIn('workspace_id', $wsIds)->count(),
            'completed_tasks'    => WorkspaceTask::whereIn('workspace_id', $wsIds)->where('status', 'done')->count(),
            'total_minutes'      => WorkspaceTimeEntry::whereIn('workspace_id', $wsIds)->sum('minutes'),
            'billable_minutes'   => WorkspaceTimeEntry::whereIn('workspace_id', $wsIds)->where('billable', true)->sum('minutes'),
        ];

        $companyStats['total_hours']    = round($companyStats['total_minutes'] / 60, 1);
        $companyStats['billable_hours'] = round($companyStats['billable_minutes'] / 60, 1);

        $workspaceStats = $workspaces->map(fn($ws) => (object)[
            'id'             => $ws->id,
            'name'           => $ws->name,
            'member_count'   => $ws->member_count,
            'document_count' => $ws->documents()->count(),
            'task_count'     => $ws->tasks()->count(),
            'completed_tasks'=> $ws->tasks()->where('status', 'done')->count(),
            'total_minutes'  => $ws->timeEntries()->sum('minutes'),
        ]);

        return view('reports.index', compact('companyStats', 'workspaceStats', 'isOwner'));
    }

    public function workspace(Request $request, TeamWorkspace $workspace)
    {
        $workspace->load(['members', 'documents', 'tasks', 'timeEntries']);

        $stats = [
            'total_tasks'        => $workspace->tasks->count(),
            'completed_tasks'    => $workspace->tasks->where('status', 'done')->count(),
            'total_minutes'      => $workspace->timeEntries->sum('minutes'),
            'billable_minutes'   => $workspace->timeEntries->where('billable', true)->sum('minutes'),
            'documents_count'    => $workspace->documents->count(),
        ];

        $stats['total_hours']    = round($stats['total_minutes'] / 60, 1);
        $stats['billable_hours'] = round($stats['billable_minutes'] / 60, 1);

        $memberHours = [];
        foreach ($workspace->members as $m) {
            $mins = $workspace->timeEntries->where('user_id', $m->id)->sum('minutes');
            if ($mins > 0) {
                $memberHours[$m->name] = round($mins / 60, 1);
            }
        }

        return view('reports.workspace', compact('workspace', 'stats', 'memberHours'));
    }
}
