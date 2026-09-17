<div style="font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f6f9;padding:0;margin:0">
<div style="max-width:600px;margin:0 auto;background:#ffffff">

<!-- Logo -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:24px 36px 0;text-align:center">
    <img src="{{ asset('images/ll_logo.png') }}" alt="LEXLAW" width="140" style="max-width:180px;height:auto;border-radius:8px">
</td></tr>
</table>

<!-- Header -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#5e6ad2,#7c3aed);border-radius:12px 12px 0 0">
<tr><td style="padding:32px 36px 24px">
    <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">LEXLAW</div>
    <div style="font-size:22px;font-weight:700;color:#fff;line-height:1.3">Pembayaran Berhasil</div>
</td></tr>
</table>

<!-- Body -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:28px 36px">

<p style="font-size:15px;color:#1a1a2e;margin:0 0 20px">Halo <strong>{{ $company }}</strong>,</p>
<p style="font-size:14px;color:#4a4a68;line-height:1.6;margin:0 0 24px">Terima kasih! Pembayaran Anda telah berhasil diterima. Langganan LEXLAW Anda kini aktif dan siap digunakan.</p>

<!-- Invoice Card -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fc;border:1px solid #e2e5ec;border-radius:10px;margin-bottom:24px">
<tr><td style="padding:20px 24px">

    <div style="font-size:11px;font-weight:700;color:#5e6ad2;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;border-bottom:1px solid #e2e5ec;padding-bottom:12px">
        Detail Invoice
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;line-height:1.8">
    <tr>
        <td style="color:#787c85;padding:4px 0;width:40%">No. Invoice</td>
        <td style="color:#1a1a2e;font-weight:600;padding:4px 0">{{ $invoice }}</td>
    </tr>
    <tr>
        <td style="color:#787c85;padding:4px 0">Perusahaan</td>
        <td style="color:#1a1a2e;font-weight:600;padding:4px 0">{{ $company }}</td>
    </tr>
    <tr>
        <td style="color:#787c85;padding:4px 0">Paket</td>
        <td style="color:#1a1a2e;font-weight:600;padding:4px 0">{{ $plan }}</td>
    </tr>
    <tr>
        <td style="color:#787c85;padding:4px 0">Nominal Dibayar</td>
        <td style="color:#1a1a2e;font-weight:700;font-size:15px;padding:4px 0">Rp {{ $amount }}</td>
    </tr>
    <tr>
        <td style="color:#787c85;padding:4px 0">Metode Pembayaran</td>
        <td style="color:#1a1a2e;font-weight:600;padding:4px 0">{{ $method }}</td>
    </tr>
    <tr>
        <td style="color:#787c85;padding:4px 0">Waktu Pembayaran</td>
        <td style="color:#1a1a2e;font-weight:600;padding:4px 0">{{ $paidAt }}</td>
    </tr>
    @if($subscribedUntil)
    <tr>
        <td style="color:#787c85;padding:4px 0">Berlaku Hingga</td>
        <td style="color:#22c55e;font-weight:700;padding:4px 0">{{ $subscribedUntil }}</td>
    </tr>
    @endif
    </table>

</td></tr>
</table>

<!-- Success Badge -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
<tr><td style="padding:0 36px">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px">
    <tr>
        <td style="padding:14px 20px;font-size:13px;color:#065f46;font-weight:600;text-align:center">
            ✅ Status Langganan: <span style="color:#059669;font-weight:700">ACTIVE</span>
        </td>
    </tr>
    </table>
</td></tr>
</table>

<!-- CTA -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px">
<tr><td style="padding:0 36px">
    <table cellpadding="0" cellspacing="0" style="margin:0 auto">
    <tr>
        <td style="background:linear-gradient(135deg,#5e6ad2,#7c3aed);border-radius:8px;text-align:center">
            <a href="{{ config('app.frontend_url', config('app.url')) }}/dashboard"
               style="display:inline-block;padding:13px 32px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none">
                Buka Dashboard →
            </a>
        </td>
    </tr>
    </table>
</td></tr>
</table>

<!-- Divider -->
<table width="100%" cellpadding="0" cellspacing="0"><tr>
    <td style="padding:0 36px"><div style="border-top:1px solid #e2e5ec"></div></td>
</tr></table>

<!-- Footer -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:20px 36px 28px;text-align:center">
    <p style="font-size:12px;color:#9ca3af;margin:0 0 8px;line-height:1.6">
        Ada pertanyaan terkait pembayaran?<br>
        Hubungi <a href="mailto:support@lexlaw.arktech.id" style="color:#5e6ad2;text-decoration:none">support@lexlaw.arktech.id</a>
        atau <a href="https://wa.me/6281297414115" style="color:#5e6ad2;text-decoration:none">Chat via WhatsApp</a>
    </p>
    <p style="font-size:11px;color:#c0c4cc;margin:0">
        LEXLAW — product by https://arktech.id/
    </p>
    <p style="font-size:11px;color:#c0c4cc;margin:0">
        © 2026 LEXLAW — by ARKTech · Layanan hukum digital terpadu
    </p>
</td></tr>
</table>

</div>
</div>
