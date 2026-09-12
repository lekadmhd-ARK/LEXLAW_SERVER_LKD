<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $logins = AuditLog::where('user_id', $user->id)
            ->where(fn($q) => $q->where('action', 'login.success')->orWhere('action', 'login.failed'))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $sessions = collect();
        try {
            $sessionRows = DB::table('sessions')->where('user_id', $user->id)->get();
            $sessions = $sessionRows->map(function ($s) {
                return (object)[
                    'id'          => $s->id,
                    'ip_address'  => $s->ip_address,
                    'user_agent'  => $s->user_agent,
                    'last_active' => date('d M Y H:i', $s->last_activity),
                    'device'      => $this->parseUserAgent($s->user_agent),
                ];
            });
        } catch (\Throwable $e) {
            $sessions = collect();
        }

        return view('security.index', compact('logins', 'sessions'));
    }

    public function revokeSession(Request $request, string $sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->where('user_id', $request->user()->id)->delete();
        return back()->with('success', 'Session berhasil dicabut.');
    }

    public function revokeAllOtherSessions(Request $request)
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        return back()->with('success', 'Semua session lain berhasil dicabut.');
    }

    private function parseUserAgent(string $ua): string
    {
        $browser = 'Unknown';
        if (str_contains($ua, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($ua, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
        elseif (str_contains($ua, 'Edge')) $browser = 'Edge';

        $os = 'Unknown';
        if (str_contains($ua, 'Windows')) $os = 'Windows';
        elseif (str_contains($ua, 'Mac OS')) $os = 'macOS';
        elseif (str_contains($ua, 'Linux')) $os = 'Linux';
        elseif (str_contains($ua, 'Android')) $os = 'Android';
        elseif (str_contains($ua, 'iPhone')) $os = 'iOS';

        return "{$browser} di {$os}";
    }
}
