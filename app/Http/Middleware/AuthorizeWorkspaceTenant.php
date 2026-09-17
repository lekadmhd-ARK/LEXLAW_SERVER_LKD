<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeWorkspaceTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');
        $user = $request->user();

        abort_unless($workspace && $user && $workspace->tenant_id === $user->tenant_id, 403);

        return $next($request);
    }
}