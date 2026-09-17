<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthActivity;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            AuthActivity::record('login', $user, ip: $request->ip(), userAgent: $request->userAgent());

            if ($user->two_factor_enabled) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                cache()->put(
                    '2fa:' . $user->id,
                    ['code' => Hash::make($code), 'expires_at' => now()->addMinutes(10)],
                    now()->addMinutes(10)
                );

                try {
                    $user->notify(new TwoFactorCodeNotification($code));
                } catch (\Throwable $e) {
                    // email gagal — kode tetap tersimpan, user bisa minta kirim ulang
                }

                return redirect()->route('two-factor.form');
            }

            return redirect()->intended('/dashboard');
        }

        AuthActivity::record('login_failed', email: $credentials['email'], ip: $request->ip(), userAgent: $request->userAgent());

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }
}