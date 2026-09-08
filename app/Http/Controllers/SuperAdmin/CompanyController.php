<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmationMail;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::withoutGlobalScopes()
            ->with(['plan', 'users'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        $proofs = AuditLog::withoutGlobalScopes()
            ->where('action', 'payment_proof_uploaded')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->keyBy(function ($l) {
                return $l->new_values['company_id'] ?? null;
            });

        return view('super-admin.companies', compact('companies', 'proofs'));
    }

    public function approve(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);

        Company::withoutGlobalScopes()
            ->where('id', $company->id)
            ->update([
                'subscription_status' => 'active',
                'subscribed_until' => now()->addDays(30),
            ]);
        $company->refresh();

        $owner = $company->users()->where('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->send(new PaymentConfirmationMail(
                $company,
                "LAWLEX-{$company->id}-approved",
                (int) ($company->plan?->price_monthly ?? 0),
                method: 'Transfer / QRIS',
                paidAt: now()->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
            ));
        }

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_approved',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['subscription_status' => 'active', 'subscribed_until' => $company->subscribed_until],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Company ' . $company->name . ' diaktifkan hingga ' . $company->subscribed_until->format('d M Y'));
    }

    public function reject(Request $request, $companyId)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($companyId);

        Company::withoutGlobalScopes()
            ->where('id', $company->id)
            ->update(['subscription_status' => 'rejected']);

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_rejected',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['subscription_status' => 'rejected'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Company ' . $company->name . ' ditolak');
    }
}
