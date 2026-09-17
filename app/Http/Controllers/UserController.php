<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $items = User::where('tenant_id', $request->user()->tenant_id)->paginate(20);
        return view('users.index', compact('items'));
    }

    public function show(Request $request, User $user)
    {
        abort_unless($user->tenant_id === $request->user()->tenant_id, 403);
        return view('users.index', ['items' => collect([$user])]);
    }
}
