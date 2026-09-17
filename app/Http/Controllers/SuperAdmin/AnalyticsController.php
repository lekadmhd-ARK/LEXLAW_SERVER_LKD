<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $base = DB::table('companies');

        $groups = DB::table('companies')
            ->selectRaw("subscription_status, COUNT(*) as total")
            ->groupBy('subscription_status')
            ->get()
            ->pluck('total', 'subscription_status')
            ->map(fn ($v) => (int) $v);

        $totalCompanies = (int) $base->count();
        $active = (int) ($groups['active'] ?? 0);
        $trialing = (int) ($groups['trialing'] ?? 0);
        $suspended = (int) ($groups['suspended'] ?? 0);
        $inactive = (int) ($groups['inactive'] ?? 0);
        $rejected = (int) ($groups['rejected'] ?? 0);
        $totalUsers = (int) DB::table('users')->count();

        $mrr = (float) DB::table('companies')
            ->join('plans', 'plans.id', '=', 'companies.plan_id')
            ->whereIn('companies.subscription_status', ['active', 'trialing'])
            ->where('plans.is_active', true)
            ->sum('plans.price_monthly');

        // Distribusi user per paket (perusahaan berstatus aktif/trialing)
        $planDist = DB::table('companies')
            ->join('plans', 'plans.id', '=', 'companies.plan_id')
            ->whereIn('companies.subscription_status', ['active', 'trialing'])
            ->selectRaw('plans.name as plan, COUNT(*) as companies, SUM(plans.price_monthly) as mrr')
            ->groupBy('plans.name')
            ->orderByDesc('mrr')
            ->get();

        // Signup per pekan (8 pekan terakhir)
        $signups = DB::table('companies')
            ->selectRaw("to_char(date_trunc('week', created_at), 'YYYY-MM-DD') AS week, COUNT(*) AS total")
            ->where('created_at', '>=', now()->subWeeks(8))
            ->groupBy(DB::raw("date_trunc('week', created_at)"))
            ->orderBy(DB::raw("date_trunc('week', created_at)"))
            ->get();

        $conversionRate = ($trialing + $active) > 0
            ? round(($active / ($trialing + $active)) * 100, 1)
            : 0;

        return view('super-admin.analytics', compact(
            'totalCompanies',
            'active',
            'trialing',
            'suspended',
            'inactive',
            'rejected',
            'totalUsers',
            'mrr',
            'planDist',
            'signups',
            'conversionRate'
        ));
    }
}