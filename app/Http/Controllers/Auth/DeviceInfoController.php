<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthActivity;
use Illuminate\Http\Request;

/**
 * Client (browser) melaporkan IP lokal hasil WebRTC (best-effort).
 * MAC hanya terisi bila server berada di LAN yang sama (via ARP lookup).
 * Dari internet publik, MAC tidak tersedia — kolom tetap diisi null.
 */
class DeviceInfoController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        $localIps = collect($request->input('local_ips', []))
            ->filter(fn ($ip) => is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP))
            ->unique()
            ->values()
            ->all();

        $activity = AuthActivity::where('user_id', $user->id)
            ->whereIn('event', ['login', 'register', 'login_failed'])
            ->latest('id')
            ->first();

        if (!$activity) {
            return response()->json(['ok' => true, 'matched' => false]);
        }

        $privateIp = null;
        foreach ($localIps as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                $privateIp = $ip;
                break;
            }
        }

        $mac = null;
        if (config('app.env') === 'production' || $privateIp) {
            $mac = $this->resolveMacViaArp($privateIp);
        }

        $activity->update([
            'local_ip' => $privateIp ?? ($localIps[0] ?? null),
            'mac_address' => $activity->mac_address ?? $mac,
        ]);

        return response()->json(['ok' => true, 'matched' => true]);
    }

    protected function resolveMacViaArp(?string $ip): ?string
    {
        if (!$ip || !function_exists('exec') || !is_callable('exec')) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
            return null; // hanya IP privat (LAN) yang layak di-ARP
        }

        try {
            exec('arp -n ' . escapeshellarg($ip) . ' 2>/dev/null', $out, $code);
            if ($code !== 0) {
                return null;
            }
            $line = implode("\n", $out);
            if (preg_match('/\s(at|hetzner)\s+([0-9a-fA-F:]{17})/', $line, $m)) {
                return strtoupper($m[2]);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}