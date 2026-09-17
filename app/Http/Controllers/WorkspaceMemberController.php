<?php

namespace App\Http\Controllers;

use App\Mail\TeamInviteMail;
use App\Models\TeamWorkspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WorkspaceMemberController extends Controller
{

    public function store(Request $request, TeamWorkspace $workspace)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => 'required|in:' . implode(',', array_keys(TeamWorkspace::MEMBER_ROLES)),
        ]);

        $email  = mb_strtolower(trim($validated['email']));
        $role   = $validated['role'];

        // Cari ke seluruh sistem (tanpa distorsi scope tenant) supaya kita bisa
        // membedakan "email milik perusahaan lain" vs "belum punya akun sama sekali".
        $user = User::where('email', $email)->first();

        if ($user && $user->tenant_id !== $request->user()->tenant_id) {
            return back()->with('error', 'Email tersebut sudah terdaftar dan merupakan bagian dari perusahaan lain. Anggota harus bergabung ke perusahaan Anda.');
        }

        if (!$user) {
            if ($role === 'owner') {
                $role = 'admin';
            }

            $user = User::create([
                'name'       => ucfirst(substr(explode('@', $email)[0], 0, 40)),
                'email'      => $email,
                'password'   => bcrypt(Str::random(40)),
                'tenant_id'  => $request->user()->tenant_id,
                'company_id' => $request->user()->company_id,
                'role'       => $role,
            ]);

            try {
                Mail::to($user->email)->send(new TeamInviteMail(
                    $user,
                    $workspace,
                    TeamWorkspace::MEMBER_ROLES[$role],
                    $request->user()->company?->name ?? 'Perusahaan Anda',
                ));
            } catch (\Throwable $e) {
                Log::warning('Invite email failed for user: ' . $user->email . ' — ' . $e->getMessage());
            }
        }

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User sudah menjadi anggota workspace ini.');
        }

        $workspace->members()->attach($user->id, [
            'role'      => $role,
            'joined_at' => now(),
        ]);

        return back()->with('success', "{$user->name} berhasil ditambahkan sebagai " . TeamWorkspace::MEMBER_ROLES[$role] . ".");
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
