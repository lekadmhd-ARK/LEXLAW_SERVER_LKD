<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Regulation;
use App\Models\LegalGlossary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Stat riil dari DB
        $totalRegs = Regulation::where('tenant_id', $tenantId)->count();
        $uuCount = Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '1')->count();
        $ppCount = Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '2')->count();
        $perpresCount = Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '3')->count();
        $permenCount = Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '4')->count();
        $perdaCount = Regulation::where('tenant_id', $tenantId)->where('hierarchy_level', '5')->count();

        // Count glossary
        $glossaryCount = class_exists('\App\Models\LegalGlossary') 
            ? LegalGlossary::where('tenant_id', $tenantId)->count() 
            : 0;

        // 5 Regulasi Terbaru
        $latestRegs = Regulation::where('tenant_id', $tenantId)
            ->latest()
            ->take(5)
            ->get();

        // Cek status AI gateway (9Router) — Active / Busy / Offline
        $aiStatus = 'offline';
        $aiStatusColor = 'danger';
        try {
            $aiUrl = env('AI_BASE_URL', 'http://127.0.0.1:20128/v1');
            $aiKey = env('AI_API_KEY', '');
            $t0 = microtime(true);
            $resp = Http::timeout(3)->withHeaders([
                'Authorization' => 'Bearer ' . $aiKey,
                'Content-Type' => 'application/json',
            ])->post(rtrim($aiUrl, '/') . '/chat/completions', [
                'model' => env('AI_MODEL', 'gemini-3.5-flash'),
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1,
            ]);
            $latency = round((microtime(true) - $t0) * 1000);
            if ($resp->successful()) {
                $aiStatus = 'active';
                $aiStatusColor = 'success';
            } elseif ($resp->status() === 429 || $resp->status() === 503) {
                $aiStatus = 'busy';
                $aiStatusColor = 'warning';
            }
        } catch (\Exception $e) {
            $aiStatus = 'offline';
        }

        return view('dashboard', compact(
            'user',
            'totalRegs',
            'uuCount',
            'ppCount',
            'perpresCount',
            'permenCount',
            'perdaCount',
            'glossaryCount',
            'latestRegs',
            'aiStatus',
            'aiStatusColor'
        ));
    }
}