<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrialMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Skip untuk guest, super admin, dan halaman publik
        if (!$user || (string) $user->role === '1') {
            return $next($request);
        }

        $company = $user->company;

        // Skip jika tidak ada company atau sudah berlangganan aktif
        if (!$company || $company->subscription_status === 'active') {
            return $next($request);
        }

        // Cek status trial
        if ($company->subscription_status === 'trialing') {
            if ($company->trial_ends_at && $company->trial_ends_at->isPast()) {
                // Trial sudah habis — redirect ke billing kecuali sudah di halaman billing
                if (!$request->is('billing*')) {
                    return redirect()->route('billing')
                        ->with('error', 'Masa trial Anda telah berakhir. Silakan berlangganan untuk melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
