<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::all();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function edit()
    {
        $canEditPrice = auth()->user()->role == 1;
        return view('super-admin.edit-plan', compact('canEditPrice'));
    }

    public function update(Request $request)
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
}
