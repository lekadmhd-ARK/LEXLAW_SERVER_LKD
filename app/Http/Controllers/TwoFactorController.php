<?php

namespace App\Http\Controllers;

use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    public function form(Request $request)
    {
        if (!$request->user()->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        return view('two-factor.form');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $key = '2fa:' . $user->id;
        $stored = cache()->get($key);

        if (!$stored || $stored['expires_at'] < now()->getTimestamp() || !Hash::check($request->code, $stored['code'])) {
            return back()->withErrors(['code' => 'Kode salah atau sudah kedaluwarsa.']);
        }

        cache()->forget($key);
        $request->session()->put('2fa_passed', true);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard')->with('status', 'Verifikasi dua langkah berhasil.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        cache()->put(
            '2fa:' . $user->id,
            ['code' => Hash::make($code), 'expires_at' => now()->addMinutes(10)->getTimestamp()],
            now()->addMinutes(10)
        );

        try {
            $user->notify(new TwoFactorCodeNotification($code));
        } catch (\Throwable $e) {
            Log::warning('2FA resend failed for user: ' . $user->email . ' — ' . $e->getMessage());
        }

        return back()->with('status', 'Kode verifikasi baru telah dikirim ke email Anda.');
    }
}