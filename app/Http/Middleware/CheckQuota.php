<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckQuota
{
    public function handle(Request $request, Closure $next, string $tool)
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        $company = $user->company;
        if (!$company) return redirect('/dashboard')->withErrors('Akun tidak memiliki perusahaan.');

        $plan = $company->plan;
        if (!$plan || !$plan->is_active) {
            return redirect('/billing')->withErrors('Paket tidak aktif. Silakan pilih paket langganan.');
        }

        // Reset quota bulanan jika sudah lewat 1 bulan
        if ($company->quota_reset_at && $company->quota_reset_at->lt(now())) {
            $company->update([
                'quota_qna' => 0,
                'quota_draft' => 0,
                'quota_contract_review' => 0,
                'quota_validity' => 0,
                'quota_reset_at' => now()->addMonth(),
            ]);
        }

        // Inisialisasi reset_at jika null
        if (!$company->quota_reset_at) {
            $company->update(['quota_reset_at' => now()->addMonth()]);
        }

        // Cek limit per tool
        $limitField = 'limit_' . $tool;
        $quotaField = 'quota_' . $tool;
        $limit = $plan->$limitField ?? 0;
        $used = $company->$quotaField ?? 0;

        // Enterprise: unlimited (limit >= 999999)
        if ($limit >= 999999 || $limit == -1) {
            return $next($request);
        }

        // Basic/Professional: cek quota
        if ($limit > 0 && $used >= $limit) {
            $toolName = ucfirst(str_replace('_', ' ', $tool));
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => "Kuota {$toolName} sudah habis bulan ini. Upgrade ke paket yang lebih tinggi untuk melanjutkan.",
                    'quota_exceeded' => true,
                    'limit' => $limit,
                    'used' => $used,
                ], 429);
            }
            return redirect('/billing')->withErrors("Kuota {$toolName} sudah habis. Upgrade paket untuk melanjutkan.");
        }

        $request->merge(['_plan_limit' => $limit, '_plan_used' => $used]);
        return $next($request);
    }

    public static function incrementQuota($company, string $tool): void
    {
        $field = 'quota_' . $tool;
        if ($company && in_array($field, ['quota_qna','quota_draft','quota_contract_review','quota_validity'])) {
            $company->increment($field);
        }
    }
}
