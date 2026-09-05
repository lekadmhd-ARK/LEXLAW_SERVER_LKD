<x-layouts.base title="Billing - LEXLAW v2">
    <div class="page-head">
        <div>
            <div class="eyebrow">Subscription</div>
            <h1 class="page-title">Billing & QRIS</h1>
            <p class="page-desc">Midtrans sedang diproses. Flow sementara memakai QRIS perusahaan + upload bukti pembayaran.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert" style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert" style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">{{ $errors->first() }}</div>
    @endif

    <div class="grid-4" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
        <div class="card">
            <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">Current Plan</span>
            <h2 style="margin-top:12px;font-size:20px">{{ $company->subscription_status ?? 'trial' }}</h2>
            <p style="color:var(--text-muted);margin-top:4px">{{ $company->name ?? 'Company not set' }}</p>
            <form method="POST" action="/billing/subscribe" style="margin-top:20px">
                @csrf
                <div style="margin-bottom:12px">
                    <label class="label">Pilih Paket</label>
                    <select name="plan_id" required class="select" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        @foreach(\App\Models\Plan::all() as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} — Rp{{ number_format($plan->price_monthly) }}/bulan</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Generate QRIS Order</button>
            </form>
        </div>

        <div class="card">
            <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">Manual Payment</span>
            <h2 style="margin-top:12px;font-size:20px">QRIS Perusahaan</h2>
            <p style="color:var(--text-muted);margin-top:4px">Scan QRIS, lalu upload bukti pembayaran.</p>
            <img src="/qris/qris_ark.jpeg" alt="QRIS" style="width:100%;max-width:320px;margin:18px 0;border:1px solid var(--line);border-radius:14px;background:white">
            <form method="POST" action="/payment/upload-qris" enctype="multipart/form-data" style="margin-top:16px">
                @csrf
                <div style="margin-bottom:12px">
                    <label class="label">Bukti Transfer</label>
                    <input type="file" name="proof" accept="image/*" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                </div>
                <button class="btn btn-primary" type="submit">Upload Proof</button>
            </form>
        </div>
    </div>
</x-layouts.base>