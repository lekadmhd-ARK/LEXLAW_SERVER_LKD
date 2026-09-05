<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Regulation;
use App\Models\LegalGlossary;
use App\Models\Company;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'regulations' => Regulation::count(),
            'glossary' => LegalGlossary::count(),
            'companies' => Company::count(),
            'audit_logs' => AuditLog::count(),
        ];

        return view('dashboard', compact('user', 'stats'));
    }
}
