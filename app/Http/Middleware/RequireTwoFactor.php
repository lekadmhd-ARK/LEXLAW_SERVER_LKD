<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && !$request->session()->get('2fa_passed', false)) {
            if (!$request->routeIs('two-factor.*') && !$request->routeIs('logout')) {
                return redirect()->route('two-factor.form');
            }
        }

        return $next($request);
    }
}