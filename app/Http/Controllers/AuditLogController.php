<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $items = AuditLog::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(30);
        return view('audit-logs.index', compact('items'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('audit-logs.index', ['items' => collect([$auditLog])]);
    }
}
