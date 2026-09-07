<x-layouts.base title="Billing 2 - QRIS Dinamis">
<div class="page-head">
  <div>
    <div class="eyebrow">Pembayaran &mdash; Testing</div>
    <h1 class="page-title">QRIS Dinamis</h1>
    <p class="page-desc">Generate QRIS dengan nominal (static &rarr; dinamis: Tag PIM 01 11&rarr;12 + Tag 54 + CRC16).</p>
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

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:20px;max-width:1100px;margin:0 auto">
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <div style="font-size:14px;font-weight:700;margin-bottom:6px">1. Masukkan Nominal</div>
    <p style="font-size:12px;color:var(--muted);margin-bottom:16px">QR statis akan disisipi Tag 54 (nominal) dan CRC dihitung ulang. QR dinamis tetap ke merchant yang sama, tapi nominal terkunci.</p>

    <form method="POST" action="{{ route('billing2.make-dynamic') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
      @csrf
      <div style="flex:1;min-width:180px">
        <label class="label">Nominal (Rp)</label>
        <input type="number" name="nominal" min="1000" step="1000" required placeholder="contoh: 50000" value="{{ old('nominal', $amount ?? '') }}">
      </div>
      <button type="submit" class="btn btn-primary" style="height:38px;white-space:nowrap">Generate QR Dinamis</button>
    </form>

    <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
      @foreach([10000,25000,50000,100000] as $n)
        <form method="POST" action="{{ route('billing2.make-dynamic') }}">@csrf<input type="hidden" name="nominal" value="{{ $n }}"><button class="btn btn-secondary" style="padding:6px 12px;font-size:12px">Rp {{ number_format($n,0,',','.') }}</button></form>
      @endforeach
    </div>

    @if(!empty($payloadDynamic))
    <div style="margin-top:18px;padding:14px;background:var(--bg);border:1px solid var(--line);border-radius:10px">
      <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">Payload Dinamis (siap jadi QR)</div>
      <div style="font-family:monospace;font-size:11px;word-break:break-all;line-height:1.5;color:var(--text)">{{ $payloadDynamic }}</div>
      <div style="margin-top:8px;display:flex;gap:8px">
        <button class="btn btn-secondary" style="font-size:12px" onclick="navigator.clipboard.writeText('{{ $payloadDynamic }}');this.textContent='Tersalin!'">Copy Payload</button>
        @if(!empty($orderId))<span style="font-size:11px;color:var(--muted);align-self:center">Order: <b style="color:var(--text)">{{ $orderId }}</b></span>@endif
      </div>
      <div style="margin-top:10px;font-size:11px;color:var(--muted)">Tag 54 = nominal &bull; Tag 01 PIM 11&rarr;12 &bull; CRC16-CCITT dihitung untuk <code>...6304</code> + 4 hex.</div>
    </div>
    @endif
  </div>

  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:22px;text-align:center">
    <div style="font-size:13px;font-weight:700;margin-bottom:10px">QR Code</div>
    @if(!empty($payloadDynamic))
      <div id="qrcode" style="display:flex;justify-content:center;padding:12px;background:#fff;border-radius:12px;border:1px solid var(--line);min-height:260px;align-items:center"></div>
      <div id="qr-amount" style="margin-top:12px;font-size:22px;font-weight:800;color:var(--accent)">Rp {{ number_format($amount,0,',','.') }}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:4px">Scan pakai DANA / GoPay / ShopeePay / MBanking &mdash; nominal otomatis terisi.</div>
      <div style="margin-top:12px;display:flex;gap:8px;justify-content:center">
        <button class="btn btn-secondary" style="font-size:12px" onclick="downloadQR()">Download PNG</button>
      </div>
    @else
      <div style="padding:40px 16px;border:2px dashed var(--line);border-radius:12px;color:var(--muted);font-size:13px">
        Belum ada QR.<br>Masukkan nominal &amp; Generate.
      </div>
      <div style="margin-top:14px;text-align:left;background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:12px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px">Catatan Integrasi</div>
        <ul style="font-size:11px;color:var(--muted);line-height:1.6;margin-left:16px">
          <li>QR dinamis bernominal <b>tidak otomatis</b> verifikasi pembayaran tanpa webhook/VA.</li>
          <li>Untuk auto-verify butuh Midtrans / iPaymu / provider QRIS dinamis (API).</li>
          <li>Halaman ini pakai payload statis demo &mdash; ganti <code>$payloadStaticShort</code> di <code>Billing2Controller</code> dengan string hasil decode <code>/qris/qris_ark.jpeg</code> untuk merchant asli.</li>
        </ul>
      </div>
    @endif
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
@if(!empty($payloadDynamic))
(function(){
  var payload = @json($payloadDynamic);
  var el = document.getElementById('qrcode');
  if(el && payload){
    new QRCode(el, { text: payload, width: 240, height: 240, correctLevel: QRCode.CorrectLevel.M });
  }
})();
function downloadQR(){
  var c = document.querySelector('#qrcode canvas');
  if(!c){ alert('QR belum siap'); return; }
  var a = document.createElement('a'); a.href = c.toDataURL('image/png'); a.download = 'qris-{{ $amount ?? "dinamis" }}.png'; a.click();
}
@endif
</script>
@endpush
</x-layouts.base>
