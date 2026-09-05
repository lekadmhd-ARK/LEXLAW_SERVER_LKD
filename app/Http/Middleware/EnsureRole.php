<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in route: ->middleware('role:owner,admin') or ->middleware('role:editor')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // If no roles specified, just check authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Owner/Admin always has access if role is in list
        // Roles: owner, admin, editor, viewer
        $userRole = strtolower($user->role ?? 'viewer');

        if (in_array($userRole, array_map('strtolower', $roles))) {
            return $next($request);
        }

        // Also allow 'owner' as super role
        if ($userRole === 'owner') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                'required_roles' => $roles,
                'your_role' => $userRole
            ], 403);
        }

        return redirect('/dashboard')->with('error', 'Akses ditolak: role Anda (' . ($user->getRoleDisplay() ?? $userRole) . ') tidak memiliki izin ke halaman ini.');
    }
}
