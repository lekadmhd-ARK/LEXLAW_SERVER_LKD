<?php

namespace App\Policies;

use App\Models\TeamWorkspace;
use App\Models\User;
use App\Models\WorkspaceDocument;
use App\Models\WorkspaceNote;
use App\Models\WorkspaceTask;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TeamWorkspace $workspace): bool
    {
        return $workspace->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin']);
    }

    public function delete(User $user, TeamWorkspace $workspace): bool
    {
        return $this->getUserRole($user, $workspace) === 'owner';
    }

    // Members
    public function manageMembers(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin']);
    }

    public function removeMember(User $user, TeamWorkspace $workspace, User $member): bool
    {
        if ($user->id === $member->id) return false;
        $memberRole = $workspace->members()->where('user_id', $member->id)->first()?->pivot->role;
        if ($memberRole === 'owner') return false;
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin']);
    }

    // Documents
    public function uploadDocument(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    public function deleteDocument(User $user, TeamWorkspace $workspace, WorkspaceDocument $document): bool
    {
        $role = $this->getUserRole($user, $workspace);
        if (in_array($role, ['owner', 'admin'])) return true;
        return $document->user_id === $user->id && $role === 'member';
    }

    public function downloadDocument(User $user, TeamWorkspace $workspace): bool
    {
        return $this->view($user, $workspace);
    }

    // Notes
    public function createNote(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    public function editNote(User $user, TeamWorkspace $workspace, WorkspaceNote $note): bool
    {
        $role = $this->getUserRole($user, $workspace);
        if (in_array($role, ['owner', 'admin'])) return true;
        return $note->user_id === $user->id && $role === 'member';
    }

    // Tasks
    public function createTask(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    public function editTask(User $user, TeamWorkspace $workspace, WorkspaceTask $task): bool
    {
        $role = $this->getUserRole($user, $workspace);
        if (in_array($role, ['owner', 'admin'])) return true;
        return $task->user_id === $user->id && $role === 'member';
    }

    // Time
    public function logTime(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    public function viewAllTime(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin']);
    }

    // Audit
    public function viewAudit(User $user, TeamWorkspace $workspace): bool
    {
        $role = $this->getUserRole($user, $workspace);
        return in_array($role, ['owner', 'admin']);
    }

    private function getUserRole(User $user, TeamWorkspace $workspace): ?string
    {
        return $workspace->members()->where('user_id', $user->id)->value('team_workspace_members.role');
    }
}
