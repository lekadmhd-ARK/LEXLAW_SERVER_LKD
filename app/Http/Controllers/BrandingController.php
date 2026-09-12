<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function edit(Request $request)
    {
        $company = $request->user()->company;
        $settings = $company->settings()->pluck('value', 'key')->toArray();
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

        if ($request->filled('name')) {
            $company->name = $request->input('name');
            $company->save();
        }

        if ($request->filled('primary_color')) {
            $this->saveSetting($company->id, 'primary_color', $request->input('primary_color'));
        }
        if ($request->filled('accent_color')) {
            $this->saveSetting($company->id, 'accent_color', $request->input('accent_color'));
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $this->saveSetting($company->id, 'logo_url', Storage::disk('public')->url($path));
        }

        return back()->with('success', 'Branding berhasil diperbarui.');
    }

    private function saveSetting(int $companyId, string $key, ?string $value): void
    {
        CompanySetting::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value],
        );
    }
}
