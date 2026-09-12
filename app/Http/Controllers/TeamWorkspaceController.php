<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TeamWorkspaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->route('workspace')) {
                $ws = $request->route('workspace');
                if ($ws->tenant_id !== $request->user()->tenant_id) {
                    abort(403);
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $items = TeamWorkspace::with('creator')
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('team-workspaces.index', compact('items'));
    }

    public function create(Request $request)
    {
        $types = TeamWorkspace::TYPES;
        return view('team-workspaces.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|max:255',
            'type'        => 'nullable|in:' . implode(',', array_keys(TeamWorkspace::TYPES)),
            'description' => 'nullable|max:1000',
        ]);

        $workspace = TeamWorkspace::create(array_merge($validated, [
            'tenant_id'  => $request->user()->tenant_id,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'is_active'  => true,
        ]));

        $workspace->members()->attach($request->user()->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        AuditService::log('workspace.created', $workspace, [], $workspace->toArray());

        return redirect()->route('team-workspaces.show', $workspace)
            ->with('success', "Workspace '{$workspace->name}' berhasil dibuat.");
    }

    public function show(Request $request, TeamWorkspace $teamWorkspace)
    {
        $tab = $request->query('tab', 'members');
        $types = TeamWorkspace::TYPES;
        $roles = TeamWorkspace::MEMBER_ROLES;

        $teamWorkspace->load(['members', 'creator', 'documents', 'notes', 'tasks.assignee', 'timeEntries.task']);

        AuditService::log('workspace.viewed', $teamWorkspace, null, [
            'tab' => $tab,
            'member_count' => $teamWorkspace->members->count(),
        ]);

        return view('team-workspaces.show', [
            'w'           => $teamWorkspace,
            'tab'         => $tab,
            'types'       => $types,
            'roles'       => $roles,
            'documents'   => $teamWorkspace->documents,
            'notes'       => $teamWorkspace->notes,
            'tasks'       => $teamWorkspace->tasks,
            'timeEntries' => $teamWorkspace->timeEntries,
            'members'     => $teamWorkspace->members,
        ]);
    }

    public function edit(Request $request, TeamWorkspace $teamWorkspace)
    {
        $types   = TeamWorkspace::TYPES;
        $members = $teamWorkspace->members;
        $w       = $teamWorkspace;
        $old     = $w->getOriginal();

        return view('team-workspaces.edit', compact('w', 'types', 'members'));
    }

    public function update(Request $request, TeamWorkspace $teamWorkspace)
    {
        $old = $teamWorkspace->getOriginal();

        $validated = $request->validate([
            'name'        => 'required|max:255',
            'type'        => 'nullable|in:' . implode(',', array_keys(TeamWorkspace::TYPES)),
            'description' => 'nullable|max:1000',
            'is_active'   => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $teamWorkspace->update($validated);

        AuditService::log('workspace.updated', $teamWorkspace, array_only($old, ['name', 'type', 'description', 'is_active']), $validated);

        return redirect()->route('team-workspaces.show', $teamWorkspace)
            ->with('success', "Workspace '{$teamWorkspace->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, TeamWorkspace $teamWorkspace)
    {
        $name = $teamWorkspace->name;
        AuditService::log('workspace.deleted', $teamWorkspace, $teamWorkspace->toArray());

        $teamWorkspace->delete();

        return redirect()->route('team-workspaces.index')
            ->with('success', "Workspace '{$name}' berhasil dihapus.");
    }
}
