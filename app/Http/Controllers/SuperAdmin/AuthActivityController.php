<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuthActivity;
use Illuminate\Http\Request;

class AuthActivityController extends Controller
{
    protected const SORTABLE = [
        'time' => 'created_at',
        'user' => 'email',
        'event' => 'event',
        'ip' => 'ip_address',
        'fingerprint' => 'device_fingerprint',
        'location' => 'geo_country',
        'mac' => 'mac_address',
        'agent' => 'user_agent',
    ];

    public function index(Request $request)
    {
        $query = AuthActivity::with('user');

        if ($request->filled('event') && in_array($request->input('event'), ['register', 'login', 'login_failed', 'logout'], true)) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('ip_address', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('mac_address', 'like', '%' . $request->input('q') . '%')
                    ->orWhere('device_fingerprint', 'like', '%' . $request->input('q') . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->input('q') . '%'));
            });
        }

        $currentSort = array_key_exists($request->input('sort'), self::SORTABLE) ? $request->input('sort') : 'time';
        $currentDir = strtolower((string) $request->input('dir')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy(self::SORTABLE[$currentSort], $currentDir);

        $activities = $query->paginate(25)->withQueryString();

        return view('super-admin.auth-activities', compact('activities', 'currentSort', 'currentDir'));
    }
}