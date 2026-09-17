<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if ((string) auth()->user()->role !== '1') {
            abort(403, 'Akses hanya untuk super admin.');
        }

        return $next($request);
    }
}
