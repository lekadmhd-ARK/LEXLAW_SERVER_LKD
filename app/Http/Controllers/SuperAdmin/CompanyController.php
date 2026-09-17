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
            ->keyBy(fn ($l) => $l->new_values['company_id'] ?? null);

        return view('super-admin.companies', compact('companies', 'proofs'));
    }

    public function approve(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);

        Company::withoutGlobalScopes()
            ->where('id', $company->id)
            ->update([
                'subscription_status' => 'active',
                'subscribed_until'    => now()->addDays(30),
            ]);
        $company->refresh();

        $owner = $company->users()->where('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->send(new PaymentConfirmationMail(
                $company,
                "LAWLEX-{$company->id}-approved",
                (int) ($company->plan?->price_monthly ?? 0),
                method:          'Transfer / QRIS',
                paidAt:          now()->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
                subscribedUntil: $company->subscribed_until?->setTimezone('Asia/Jakarta')->format('d M Y'),
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

        return back()->with('success', "Company {$company->name} diaktifkan hingga {$company->subscribed_until->format('d M Y')}");
    }

    public function reject(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);

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

        return back()->with('success', "Company {$company->name} ditolak");
    }

    public function suspend(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);
        $old = $company->subscription_status;

        Company::withoutGlobalScopes()->where('id', $company->id)->update(['subscription_status' => 'suspended']);
        $company->refresh();

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_suspended',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['previous_status' => $old, 'subscription_status' => 'suspended'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Company {$company->name} ditangguhkan (suspended)");
    }

    public function activate(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);
        $old = $company->subscription_status;

        Company::withoutGlobalScopes()->where('id', $company->id)->update([
            'subscription_status' => 'active',
            'subscribed_until' => $company->subscribed_until ?? now()->addDays(30),
        ]);
        $company->refresh();

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_activated',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['previous_status' => $old, 'subscription_status' => 'active'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Company {$company->name} diaktifkan kembali");
    }

    public function deactivate(Request $request, $company)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($company);
        $old = $company->subscription_status;

        Company::withoutGlobalScopes()->where('id', $company->id)->update([
            'subscription_status' => 'inactive',
            'subscribed_until' => null,
            'trial_ends_at' => null,
        ]);
        $company->refresh();

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_deactivated',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['previous_status' => $old, 'subscription_status' => 'inactive'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Company {$company->name} dinonaktifkan");
    }

    public function updateStatus(Request $request, $company)
    {
        $request->validate([
            'subscription_status' => 'required|in:trialing,active,suspended,inactive,rejected',
        ]);

        $company = Company::withoutGlobalScopes()->findOrFail($company);
        $old = $company->subscription_status;
        $new = $request->subscription_status;

        Company::withoutGlobalScopes()->where('id', $company->id)->update(['subscription_status' => $new]);
        $company->refresh();

        AuditLog::withoutGlobalScopes()->create([
            'tenant_id' => $company->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'company_status_changed',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => ['previous_status' => $old, 'subscription_status' => $new],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Status {$company->name} diubah dari \"{$old}\" ke \"{$new}\"");
    }
}
