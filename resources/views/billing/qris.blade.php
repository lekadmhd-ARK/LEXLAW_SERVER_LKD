<x-layouts.base>
@section("content")
    <div class="max-w-lg mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-center">Scan QRIS to Pay</h1>

        <div class="bg-white p-8 rounded-lg shadow text-center">
            <div class="mb-4">
                <div class="text-sm text-gray-500">Order ID</div>
                <div class="font-mono text-sm">{{ $orderId }}</div>
            </div>
            <div class="mb-6">
                <div class="text-sm text-gray-500">Amount</div>
                <div class="text-3xl font-bold text-indigo-600">Rp {{ number_format($amount, 0, ',', '.') }}</div>
            </div>
            <div class="mb-6">
                <div class="text-sm text-gray-500 mb-2">Plan</div>
                <div class="font-semibold">{{ $plan->name }}</div>
            </div>

            {{-- QR Code placeholder --}}
            <div class="mx-auto w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center mb-6">
                <div class="text-center text-gray-500">
                    <svg class="mx-auto w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <div class="text-xs">QR Code</div>
                    <div class="text-xs text-gray-400">(Connect Midtrans for live QR)</div>
                </div>
            </div>

            <div class="text-sm text-gray-500 mb-4">
                Open your mobile banking or e-wallet app and scan this QR code to complete payment.
            </div>

            <div class="flex justify-center gap-3 text-xs text-gray-400 mb-6">
                <span>GoPay</span><span>·</span>
                <span>OVO</span><span>·</span>
                <span>DANA</span><span>·</span>
                <span>LinkAja</span><span>·</span>
                <span>BCA/Mandiri/BNI QR</span>
            </div>

            <div class="flex justify-center gap-3">
                <a href="{{ route('billing') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">Cancel</a>
                <a href="{{ route('billing') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">I've Paid</a>
            </div>
        </div>
    </div>
</x-layouts.base>
