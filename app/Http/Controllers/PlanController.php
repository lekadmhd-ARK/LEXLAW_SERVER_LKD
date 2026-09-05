<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super-admin')->only(['edit', 'update']);
    }

    public function index()
    {
        return view('super-admin.plans');
    }

    public function edit()
    {
        $user = auth()->user();
        $hasSuperRole = $user->hasRole('super-admin');

        return view('super-admin.edit-plan', [
            'canEditPrice' => $hasSuperRole,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        return back()->with('success', 'Harga langganan berhasil diperbarui');
    }
}