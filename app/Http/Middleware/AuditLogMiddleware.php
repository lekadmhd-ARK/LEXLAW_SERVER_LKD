<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            AuditLog::create([
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'action' => strtolower($request->method()) . '_' . $request->route()->getName(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
