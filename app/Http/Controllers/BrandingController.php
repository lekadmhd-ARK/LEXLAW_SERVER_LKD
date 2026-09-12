<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function edit(Request $request)
    {
        $company  = $request->user()->company;
        $settings = $company->settings ?? [];

        return view('settings.branding', compact('company', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'           => 'nullable|max:255',
            'primary_color'  => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color'   => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo'           => 'nullable|image|max:5120',
        ]);

        $company = $request->user()->company;
        $user    = $request->user();

        if (!in_array($user->role, ['owner', 'admin'])) {
            abort(403);
        }

        $settings = $company->settings ?? [];

        if ($request->filled('name')) {
            $company->name = $request->input('name');
            $company->save();
        }

        if ($request->filled('primary_color')) {
            $settings['primary_color'] = $request->input('primary_color');
        }
        if ($request->filled('accent_color')) {
            $settings['accent_color'] = $request->input('accent_color');
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $settings['logo_url'] = Storage::disk('public')->url($path);
        }

        $company->settings = $settings;
        $company->save();

        return back()->with('success', 'Branding berhasil diperbarui.');
    }
}
