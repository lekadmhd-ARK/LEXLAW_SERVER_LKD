<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\Plan;
use App\Models\AuditLog;

class BillingController extends Controller
{
    public function __invoke(Request $request)
    {
        $company = $request->user()->company;

        if (! $company) {
            return redirect('/dashboard')->with('error', 'Akun Anda belum terhubung ke perusahaan. Silakan hubungi administrator.');
        }

        return view('billing.index', compact('company'));
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $company = $request->user()->company;

        if (! $company) {
            return redirect('/billing')->withErrors('Akun Anda belum terhubung ke perusahaan.');
        }

        $plan = Plan::find($validated['plan_id']);

        if (!$plan) {
            return redirect('/billing')->withErrors('Plan not found.');
        }

        $amount = $plan->price_monthly > 0 ? $plan->price_monthly : 10000;
        $orderId = 'LAWLEX-' . $company->id . '-' . time();

        $qrisImage = $this->qrisImageForAmount($amount);

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
            'qrisImage' => $qrisImage,
        ]);
    }

    public function uploadProof(Request $request)
    {
        $validated = $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $company = $request->user()->company;

        if (! $company) {
            return redirect('/billing')->withErrors('Akun Anda belum terhubung ke perusahaan.');
        }

        $path = $request->file('proof')->store('proofs', 'public');

        AuditLog::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'action' => 'payment_proof_uploaded',
            'subject_type' => 'Company',
            'subject_id' => $company->id,
            'new_values' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'proof_path' => $path,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/billing')->with('success', 'Bukti pembayaran terkirim. Menunggu approval admin.');
    }

    public function success(Request $request)
    {
        return redirect('/billing')->with('success', 'Payment processed. Subscription updated.');
    }

    private function qrisImageForAmount($amount)
    {
        $amount = (int) $amount;
        $available = [999000, 599000, 99000];

        // 1) Eksak jika ada
        if (in_array($amount, $available) && file_exists(public_path("paket_qris/qris_{$amount}.jpeg"))) {
            return "/paket_qris/qris_{$amount}.jpeg";
        }

        // 2) Fallback: nominal tersedia terdekat (>= amount), biar QR tidak over-bayar
        $best = null;
        foreach ($available as $a) {
            if (file_exists(public_path("paket_qris/qris_{$a}.jpeg")) && $a >= $amount && ($best === null || $a < $best)) {
                $best = $a;
            }
        }
        if ($best !== null) {
            return "/paket_qris/qris_{$best}.jpeg";
        }

        // 3) Fallback terakhir: file static lama
        return '/qris/qris_ark.jpeg';
    }
}
