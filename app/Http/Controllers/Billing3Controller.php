<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $amount = $json->get('order.amount') ?? $json->get('amount');

        // TODO: map DOKU notification to company subscription when SUCCESS.
        // Log keeps audit trail for now during billing3 sandbox testing.

        Log::info('DOKU notification validated', [
            'invoice' => $invoice,
            'status' => $status,
            'amount' => $amount,
        ]);

        return response()->json(['status' => 'ok']);
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