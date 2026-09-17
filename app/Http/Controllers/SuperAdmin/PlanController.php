<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('companies')->orderByDesc('is_active')->orderBy('price_monthly')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function editAllPrices()
    {
        $canEditPrice = auth()->user()->role == 1;
        return view('super-admin.edit-plan', compact('canEditPrice'));
    }

    public function updateAllPrices(Request $request)
    {
        if (auth()->user()->role != 1) abort(403);

        $validated = $request->validate([
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
        ]);

        Plan::query()->update([
            'price_monthly' => $validated['monthly_price'],
            'price_yearly' => $validated['yearly_price'],
        ]);

        return back()->with('success', 'Harga langganan berhasil diperbarui.');
    }

    public function create()
    {
        return view('super-admin.plans.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:plans,slug',
            'price_monthly'   => 'required|numeric|min:0',
            'price_yearly'    => 'required|numeric|min:0',
            'max_users'       => 'required|integer|min:1',
            'max_regulations' => 'required|integer|min:1',
            'max_ai_queries'  => 'required|integer|min:0',
            'ai_enabled'      => 'boolean',
            'is_active'       => 'boolean',
            'features'        => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['ai_enabled'] = $request->boolean('ai_enabled');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['features'] = array_values(array_filter(array_map('trim', explode("\n", $validated['features'] ?? ''))));

        Plan::create($validated);
        return redirect()->route('super-admin.plans')->with('success', "Paket \"{$validated['name']}\" berhasil dibuat.");
    }

    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        return view('super-admin.plans.form', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:plans,slug,' . $id,
            'price_monthly'   => 'required|numeric|min:0',
            'price_yearly'    => 'required|numeric|min:0',
            'max_users'       => 'required|integer|min:1',
            'max_regulations' => 'required|integer|min:1',
            'max_ai_queries'  => 'required|integer|min:0',
            'ai_enabled'      => 'boolean',
            'is_active'       => 'boolean',
            'features'        => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['ai_enabled'] = $request->boolean('ai_enabled');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['features'] = array_values(array_filter(array_map('trim', explode("\n", $validated['features'] ?? ''))));

        $plan->update($validated);
        return redirect()->route('super-admin.plans')->with('success', "Paket \"{$plan->name}\" berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        if ($plan->companies()->count() > 0) {
            return back()->with('error', "Paket \"{$plan->name}\" tidak bisa dihapus karena masih digunakan {$plan->companies()->count()} perusahaan.");
        }
        $name = $plan->name;
        $plan->delete();
        return back()->with('success', "Paket \"{$name}\" berhasil dihapus.");
    }
}
