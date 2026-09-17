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

        if (!$user) {
            return redirect('/login');
        }

        // Super admin tidak pernah dibatasi kuota
        if ((string) $user->role === '1') {
            return $next($request);
        }

        $company = $user->company;
        if (!$company) {
            return redirect('/dashboard')->withErrors('Akun tidak memiliki perusahaan.');
        }

        // Masa trial aktif (3 hari pertama) = akses penuh tanpa perlu plan/admin approve
        if ($company->subscription_status === 'trialing'
            && ($company->trial_ends_at === null || !$company->trial_ends_at->isPast())) {
            $request->merge(['_trial_active' => true, '_plan_limit' => -1, '_plan_used' => 0]);
            return $next($request);
        }

        $plan = $company->plan;
        if (!$plan || !$plan->is_active) {
            return redirect('/billing')->withErrors('Paket tidak aktif. Silakan pilih paket langganan.');
        }

        $this->resetQuotaIfNeeded($company);

        [$limit, $used] = $this->resolveLimitAndUsage($plan, $company, $tool);

        // Enterprise / unlimited
        if ($limit === -1 || $limit >= 999999) {
            return $next($request);
        }

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

    protected function resolveLimitAndUsage($plan, $company, string $tool): array
    {
        if (in_array($tool, ['qna', 'draft', 'contract_review', 'validity'], true)) {
            $budget = (int) $plan->max_ai_queries;
            if ($budget <= 0) {
                $budget = (int) ($plan->{'limit_' . $tool} ?? 0);
            }

            return [$budget, (int) ($company->{'quota_' . $tool} ?? 0)];
        }

        if ($tool === 'regulations') {
            return [(int) ($plan->max_regulations ?? 0), $company->regulations()->count()];
        }

        if ($tool === 'users') {
            return [(int) ($plan->max_users ?? 0), $company->users()->count()];
        }

        return [(int) ($plan->{'limit_' . $tool} ?? 0), (int) ($company->{'quota_' . $tool} ?? 0)];
    }

    protected function resetQuotaIfNeeded($company): void
    {
        if ($company->quota_reset_at && $company->quota_reset_at->isPast()) {
            $company->update([
                'quota_qna' => 0,
                'quota_draft' => 0,
                'quota_contract_review' => 0,
                'quota_validity' => 0,
                'quota_reset_at' => now()->addMonth(),
            ]);
        }

        if (!$company->quota_reset_at) {
            $company->update(['quota_reset_at' => now()->addMonth()]);
        }
    }

    public static function incrementQuota($company, string $tool): void
    {
        $field = 'quota_' . $tool;

        if ($company && in_array($field, ['quota_qna', 'quota_draft', 'quota_contract_review', 'quota_validity'], true)) {
            $company->increment($field);
        }
    }
}