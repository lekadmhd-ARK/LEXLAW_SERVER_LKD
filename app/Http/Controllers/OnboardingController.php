<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function dismiss(Request $request)
    {
        $user = $request->user();
        $company = $user->company;

        if ($company) {
            $settings = $company->settings ?? [];
            $settings['onboarding_dismissed_at'] = now()->toDateTimeString();
            $company->update(['settings' => $settings]);
        }

        return back()->with('status', 'Onboarding disembunyikan.');
    }
}