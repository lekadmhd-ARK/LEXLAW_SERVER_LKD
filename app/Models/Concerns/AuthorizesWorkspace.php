<?php

namespace App\Models\Concerns;

use App\Models\TeamWorkspace;
use Illuminate\Http\Request;

trait AuthorizesWorkspace
{
    protected function authorizeWorkspace(Request $request, TeamWorkspace $workspace): void
    {
        if ($workspace->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini.');
        }
    }
}
