<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\User;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
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
            'email' => 'required|email|exists:users,email',
            'role'  => 'required|in:' . implode(',', array_keys(TeamWorkspace::MEMBER_ROLES)),
        ]);

        $user = User::where('email', $validated['email'])->where('tenant_id', $request->user()->tenant_id)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan di tenant Anda.');
        }

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User sudah menjadi anggota workspace ini.');
        }

        $workspace->members()->attach($user->id, [
            'role'      => $validated['role'],
            'joined_at' => now(),
        ]);

        return back()->with('success', "{$user->name} berhasil ditambahkan sebagai " . TeamWorkspace::MEMBER_ROLES[$validated['role']] . ".");
    }

    public function update(Request $request, TeamWorkspace $workspace, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', array_keys(TeamWorkspace::MEMBER_ROLES)),
        ]);

        if (!$workspace->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User bukan anggota workspace ini.');
        }

        $workspace->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', "Role {$user->name} diperbarui menjadi " . TeamWorkspace::MEMBER_ROLES[$validated['role']] . ".");
    }

    public function destroy(Request $request, TeamWorkspace $workspace, User $user)
    {
        $currentUser = $request->user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus diri sendiri dari workspace.');
        }

        $pivot = $workspace->members()->where('user_id', $user->id)->first()?->pivot;
        if ($pivot && $pivot->role === 'owner') {
            return back()->with('error', 'Tidak dapat menghapus owner workspace.');
        }

        $workspace->members()->detach($user->id);

        return back()->with('success', "{$user->name} berhasil dihapus dari workspace.");
    }
}
