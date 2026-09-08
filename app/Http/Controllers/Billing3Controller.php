<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmationMail;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Billing3Controller extends Controller
{
    public function __invoke(Request $request)
    {
        $company = $request->user()->company ?? null;

        return view('billing.doku3', [
            'company' => $company,
            'sandbox' => (bool) config('services.doku.sandbox'),
            'clientId' => config('services.doku.client_id'),
            'lastError' => null,
        ]);
    }

    public function createCheckout(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|integer|min:1000',
        ]);

        $company = $request->user()->company ?? null;
        $amount = (int) $validated['nominal'];
        $invoice = 'LAWLEX-' . ($company->id ?? 'TEST') . '-' . time();

        $body = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $invoice,
            ],
            'payment' => [
                'payment_due_date' => 60,
            ],
        ];

        $target = '/checkout/v1/payment';
        $endpoint = rtrim((string) config('services.doku.base_url'), '/') . $target;

        try {
            $response = Http::withHeaders($this->buildHeaders($body, $target))
                ->post($endpoint, $body);

            $json = $response->json() ?? [];

            if ($response->successful() && isset($json['response']['payment']['url'])) {
                return redirect()->away($json['response']['payment']['url']);
            }

            $error = is_array($json['message'] ?? null)
                ? implode('; ', $json['message'])
                : ($json['message'] ?? $response->body());

            return $this->backToForm($error, $company);
        } catch (\Throwable $e) {
            Log::error('DOKU checkout error', ['error' => $e->getMessage()]);

            return $this->backToForm($e->getMessage(), $company);
        }
    }

    public function handleNotification(Request $request)
    {
        $secretKey = config('services.doku.secret_key');
        $rawBody = $request->getContent();

        $clientId = (string) $request->header('Client-Id', '');
        $requestId = (string) $request->header('Request-Id', '');
        $timestamp = (string) $request->header('Request-Timestamp', '');
        $signature = (string) $request->header('Signature', '');

        $digest = base64_encode(hash('sha256', $rawBody, true));
        $rawComponent = "Client-Id:{$clientId}\nRequest-Id:{$requestId}\nRequest-Timestamp:{$timestamp}\nRequest-Target:/webhook/doku\nDigest:{$digest}";
        $expected = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $rawComponent, $secretKey, true));

        $valid = hash_equals($signature, $expected);

        Log::info('DOKU notification', [
            'valid' => $valid,
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        if (!$valid) {
            return response()->json(['status' => 'invalid signature'], 200);
        }

        $json = $request->json();
        $status = strtoupper((string) ($json->get('transaction.status') ?? $json->get('status') ?? ''));
        $invoice = $json->get('order.invoice_number') ?? $json->get('invoice_number');
        $amount = $json->get('order.amount') ?? $json->get('amount') ?? 0;

        if ($status === 'SUCCESS') {
            $company = $this->findCompanyByInvoice($invoice);
            if ($company) {
                $company->update([
                    'subscription_status' => 'active',
                    'subscribed_until' => now()->addDays(30),
                ]);

                $owner = $company->users()->where('role', 'owner')->first();
                if ($owner) {
                    Mail::to($owner->email)->send(new PaymentConfirmationMail(
                        $company,
                        (string) $invoice,
                        (int) $amount,
                        method: 'DOKU Checkout',
                        paidAt: now()->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
                    ));
                }
            }
        }

        Log::info('DOKU notification validated', [
            'invoice' => $invoice,
            'status' => $status,
            'amount' => $amount,
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function findCompanyByInvoice(?string $invoice): ?Company
    {
        if (!$invoice) {
            return null;
        }

        // Pola: LAWLEX-{company_id}-{timestamp}
        $parts = explode('-', $invoice);
        $companyId = $parts[1] ?? null;

        return $companyId ? Company::find($companyId) : null;
    }

    private function backToForm(?string $error, $company)
    {
        return view('billing.doku3', [
            'company' => $company,
            'sandbox' => (bool) config('services.doku.sandbox'),
            'clientId' => config('services.doku.client_id'),
            'lastError' => $error,
        ]);
    }

    private function buildHeaders(array $body, string $target): array
    {
        $clientId = (string) config('services.doku.client_id');
        $secretKey = (string) config('services.doku.secret_key');
        $requestId = (string) \Illuminate\Support\Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(hash('sha256', json_encode($body), true));

        $rawComponent = "Client-Id:{$clientId}\nRequest-Id:{$requestId}\nRequest-Timestamp:{$timestamp}\nRequest-Target:{$target}\nDigest:{$digest}";
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $rawComponent, $secretKey, true));

        return [
            'Client-Id' => $clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}