<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guest & super admin bebas
        if (!$user || (string) $user->role === '1') {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return $next($request);
        }

        if (in_array($company->subscription_status, ['suspended', 'inactive', 'rejected'], true)) {
            // Halaman yang tetap boleh diakses saat status non-aktif
            $path = '/' . ltrim($request->path(), '/');
            $allowedPrefixes = [
                '/billing', '/support', '/email/', '/2fa', '/onboarding',
                '/password-change', '/security', '/logout', '/status',
            ];
            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return $next($request);
                }
            }

            $message = match ($company->subscription_status) {
                'suspended' => 'Akun perusahaan sedang di-suspend. Hubungi support untuk mengaktifkan kembali.',
                'inactive'  => 'Akun perusahaan sedang nonaktif. Silakan aktifkan langganan untuk melanjutkan.',
                default     => 'Permohonan perusahaan Anda ditolak. Hubungi support jika ini salah.',
            };

            return redirect('/billing')->with('error', $message);
        }

        return $next($request);
    }
}