<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Plan;
use App\Models\AuditLog;

class BillingController extends Controller
{
    public function __invoke(Request $request)
    {
        $company = $request->user()->company;
        return view('billing.index', compact('company'));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $company = $request->user()->company;
        $plan = Plan::find($validated['plan_id']);

        if (!$plan) {
            return redirect('/billing')->withErrors('Plan not found.');
        }

        // Placeholder: generate QRIS payment
        // In production: call Midtrans API to create transaction + get QR code
        $orderId = 'LAWLEX-' . $company->id . '-' . time();
        $amount = $plan->price_monthly > 0 ? $plan->price_monthly : 10000;

        AuditLog::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'billing_subscribe',
            'subject_type' => 'Plan',
            'subject_id' => $plan->id,
            'new_values' => ['order_id' => $orderId, 'amount' => $amount, 'plan' => $plan->name],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('billing.qris', [
            'company' => $company,
            'plan' => $plan,
            'orderId' => $orderId,
            'amount' => $amount,
        ]);
    }

    public function webhook(Request $request)
    {
        // Placeholder: verify Midtrans webhook signature
        $payload = $request->all();

        if (isset($payload['order_id'])) {
            // Update company subscription status
            // In production: verify signature_header before processing
            $company = Company::where('id', $payload['company_id'] ?? 0)->first();
            if ($company) {
                $company->update([
                    'subscription_status' => 'active',
                    'subscribed_until' => now()->addDays(30),
                ]);
            }

            AuditLog::create([
                'tenant_id' => $company?->tenant_id,
                'action' => 'payment_webhook',
                'subject_type' => 'Payment',
                'new_values' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    public function success(Request $request)
    {
        return redirect('/billing')->with('success', 'Payment processed. Subscription updated.');
    }
}
