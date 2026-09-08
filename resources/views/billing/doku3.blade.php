<x-layouts.base title="Billing 3 - DOKU Checkout">
<div class="page-head">
  <div>
    <div class="eyebrow">Pembayaran &mdash; Testing</div>
    <h1 class="page-title">DOKU Checkout</h1>
    <p class="page-desc">Buat Payment Request ke DOKU Checkout &rarr; ambil <code>payment.url</code> &rarr; redirect ke halaman checkout DOKU (VA / QRIS / e-wallet / kartu).</p>
  </div>
  <a href="/billing" class="btn btn-secondary">&larr; Billing Lama</a>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:rgba(34,197,94,.15);color:var(--ok);border:1px solid rgba(34,197,94,.3);margin-bottom:16px">{{ session('success') }}</div>
@endif
@if($errors->any())
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,.12);color:var(--err);border:1px solid rgba(239,68,68,.3);margin-bottom:16px">
  @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif
@if(!empty($lastError))
<div style="padding:12px 16px;border-radius:8px;background:rgba(239,68,68,.12);color:var(--err);border:1px solid rgba(239,68,68,.3);margin-bottom:16px">
  <b>DOKU Error:</b> {{ $lastError }}
</div>
@endif

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:20px;max-width:1100px;margin:0 auto">
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <div style="font-size:14px;font-weight:700;margin-bottom:6px">1. Masukkan Nominal &amp; Mulai Checkout</div>
    <p style="font-size:12px;color:var(--muted);margin-bottom:16px">Request dikirim ke DOKU <b>{{ $sandbox ? 'SANDBOX' : 'PRODUCTION' }}</b>. Jika sukses, Anda akan diarahkan ke halaman checkout DOKU.</p>

    <form method="POST" action="{{ route('billing3.checkout') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
      @csrf
      <div style="flex:1;min-width:180px">
        <label class="label">Nominal (Rp)</label>
        <input type="number" name="nominal" min="1000" step="1000" required placeholder="contoh: 50000" value="{{ old('nominal') }}">
      </div>
      <button type="submit" class="btn btn-primary" style="height:38px;white-space:nowrap">Bayar via DOKU</button>
    </form>

    <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
      @foreach([10000,25000,50000,100000] as $n)
        <form method="POST" action="{{ route('billing3.checkout') }}">@csrf<input type="hidden" name="nominal" value="{{ $n }}"><button class="btn btn-secondary" style="padding:6px 12px;font-size:12px">Rp {{ number_format($n,0,',','.') }}</button></form>
      @endforeach
    </div>
  </div>

  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <div style="font-size:13px;font-weight:700;margin-bottom:10px">Konfigurasi</div>
    <table style="width:100%;font-size:12px;border-collapse:collapse">
      <tr>
        <td style="padding:6px 4px;color:var(--muted)">Environment</td>
        <td style="padding:6px 4px;text-align:right">{{ $sandbox ? 'SANDBOX' : 'PRODUCTION' }}</td>
      </tr>
      <tr>
        <td style="padding:6px 4px;color:var(--muted)">Client ID</td>
        <td style="padding:6px 4px;text-align:right;font-family:monospace;font-size:11px;word-break:break-all">{{ $clientId }}</td>
      </tr>
      <tr>
        <td style="padding:6px 4px;color:var(--muted)">Endpoint</td>
        <td style="padding:6px 4px;text-align:right;font-family:monospace;font-size:11px">{{ $sandbox ? 'api-sandbox.doku.com' : 'api.doku.com' }}</td>
      </tr>
    </table>

    <div style="margin-top:14px;background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:12px">
      <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px">Catatan Integrasi</div>
      <ul style="font-size:11px;color:var(--muted);line-height:1.6;margin-left:16px">
        <li>Signature di-generate di backend (HMACSHA256 dari Client-Id, Request-Id, Request-Timestamp, Request-Target, Digest).</li>
        <li>Response sukses mengandung <code>response.payment.url</code> &mdash; di-redirect langsung ke halaman DOKU.</li>
        <li>Notifikasi DOKU akan meng-<code>POST</code> ke <code>/webhook/doku</code> &mdash; signature diverifikasi lalu di-log.</li>
        <li>Pengujian pembayaran bisa dilakukan via <b>simulasi sandbox</b> di dashboard DOKU (tanpa uang asli).</li>
      </ul>
    </div>
  </div>
</div>
</x-layouts.base>