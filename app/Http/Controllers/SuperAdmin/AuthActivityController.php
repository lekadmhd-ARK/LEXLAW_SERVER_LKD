<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuthActivity;
use Illuminate\Http\Request;

class AuthActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = AuthActivity::with('user')->latest('id');

        if ($request->filled('event') && in_array($request->input('event'), ['register', 'login', 'login_failed', 'logout'], true)) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('ip_address', 'like', '%' . $request->input('q') . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->input('q') . '%'));
            });
        }

        $activities = $query->paginate(40)->withQueryString();

        return view('super-admin.auth-activities', compact('activities'));
    }
}