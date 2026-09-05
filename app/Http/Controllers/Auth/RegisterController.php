<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed'],
            'company_name' => ['required', 'max:255'],
        ]);

        $tenantId = Str::uuid()->toString();
        $company = Company::create([
            'tenant_id' => $tenantId,
            'name' => $validated['company_name'],
            'slug' => Str::slug($validated['company_name']),
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'role' => 'owner',
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }
}
