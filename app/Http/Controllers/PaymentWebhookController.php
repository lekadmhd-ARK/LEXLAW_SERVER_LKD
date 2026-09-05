<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\AuditLog;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature', '');

        // TODO: verify Midtrans signature with config('midtrans.server_key')
        // For now, process if order_id present

        if (isset($payload['order_id']) && isset($payload['transaction_status'])) {
            $status = $payload['transaction_status'];

            // Map Midtrans status to subscription status
            $subscriptionStatus = match($status) {
                'capture', 'settlement' => 'active',
                'pending' => 'pending',
                'expire', 'cancel', 'deny' => 'canceled',
                default => 'pending',
            };

            // Find company by order pattern: LAWLEX-{company_id}-{timestamp}
            $parts = explode('-', $payload['order_id'] ?? '');
            $companyId = $parts[1] ?? null;

            if ($companyId) {
                $company = Company::find($companyId);
                if ($company && in_array($subscriptionStatus, ['active', 'canceled'])) {
                    $company->update([
                        'subscription_status' => $subscriptionStatus,
                        'subscribed_until' => $subscriptionStatus === 'active' ? now()->addDays(30) : null,
                    ]);
                }
            }

            AuditLog::create([
                'tenant_id' => $company?->tenant_id,
                'action' => 'payment_webhook',
                'subject_type' => 'Payment',
                'subject_id' => null,
                'old_values' => ['previous_status' => $company?->subscription_status],
                'new_values' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored'], 200);
    }
}
