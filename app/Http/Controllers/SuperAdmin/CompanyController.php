<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmationMail;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

    public function destroy(Request $request, $company)
    {
        $id = is_object($company) ? $company->getKey() : $company;
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        if ($request->user()->company_id == $company->id) {
            return back()->with('error', "Tidak dapat menghapus perusahaan milik super admin sendiri ({$company->name}).");
        }

        $tenant = $company->tenant_id;
        $userIds = DB::table('users')->where('company_id', $company->id)->pluck('id');
        $userEmails = DB::table('users')->where('company_id', $company->id)->pluck('email')->filter()->values();

        // kumpulkan file dokumen beserta folder-nya sebelum row-nya hilang (best-effort)
        $documentPaths = DB::table('workspace_documents')
            ->join('team_workspaces', 'team_workspaces.id', '=', 'workspace_documents.workspace_id')
            ->where('team_workspaces.company_id', $company->id)
            ->pluck('workspace_documents.file_path');
        $workspaceFolders = DB::table('team_workspaces')
            ->where('company_id', $company->id)
            ->pluck('id');

        $snapshot = [
            'id' => $company->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'tenant_id' => $company->tenant_id,
            'status' => $company->subscription_status,
            'deleted_at' => now()->toDateTimeString(),
        ];

        DB::transaction(function () use ($company, $tenant, $userIds, $userEmails, $request, $snapshot, $documentPaths, $workspaceFolders) {
            // 1) file fisik dokumen workspace (storage/app/private/workspace-documents/*)
            foreach ($documentPaths as $path) {
                if (is_string($path) && $path !== '') {
                    Storage::disk('local')->delete($path);
                }
            }
            foreach ($workspaceFolders as $wid) {
                Storage::disk('local')->deleteDirectory('workspace-documents/' . $wid);
            }

            // 2) baris ber-tenant yang tidak ikut cascade dari company
            DB::table('consolidations')->where('tenant_id', $tenant)->delete();
            DB::table('consolidation_chunks')->where('tenant_id', $tenant)->delete();
            DB::table('regulation_passages')->where('tenant_id', $tenant)->delete();

            // 3) jejak auth & audit
            DB::table('auth_activities')->where(function ($q) use ($userIds, $userEmails) {
                $q->whereIn('user_id', $userIds)->orWhereIn('email', $userEmails);
            })->delete();
            DB::table('audit_logs')->where('tenant_id', $tenant)
                ->orWhere(function ($q) use ($company) {
                    $q->where('subject_type', 'Company')->where('subject_id', $company->id);
                })->delete();

            // 4) role/permission Spatie yang menempel di user
            DB::table('model_has_roles')->where('model_type', User::class)->whereIn('model_id', $userIds)->delete();

            // 5) company (cascade: regulations+contents, team_workspaces+members+docs/notes/tasks/time, webhooks, company_settings)
            DB::table('companies')->where('id', $company->id)->delete();

            // 6) users terakhir (cascade ai_chat_history; users.company_id nullOnDelete sudah lepas)
            DB::table('users')->whereIn('id', $userIds)->delete();

            // 7) catat audit pemusnahan (tenant null agar tak ikut terhapus)
            AuditLog::withoutGlobalScopes()->create([
                'tenant_id' => null,
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'action' => 'company_deleted',
                'subject_type' => 'Company',
                'subject_id' => $company->id,
                'old_values' => ['users_removed' => $userIds->count()],
                'new_values' => $snapshot,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with('success', "Client {$company->name} beserta " . $userIds->count() . " user dan semua datanya dihapus permanen dari database.");
    }
}
