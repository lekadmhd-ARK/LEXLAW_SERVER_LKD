<div style="font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f6f9;padding:0;margin:0">
<div style="max-width:600px;margin:0 auto;background:#ffffff">

<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:24px 36px 0;text-align:center">
    <img src="{{ asset('images/ll_logo.png') }}" alt="LEXLAW" width="140" style="max-width:180px;height:auto;border-radius:8px">
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#5e6ad2,#7c3aed);border-radius:12px 12px 0 0">
<tr><td style="padding:32px 36px 24px">
    <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">LEXLAW</div>
    <div style="font-size:22px;font-weight:700;color:#fff;line-height:1.3">Anda Ditambahkan ke Workspace!</div>
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:28px 36px">

<p style="font-size:15px;color:#1a1a2e;margin:0 0 12px">Halo <strong>{{ $name }}</strong>,</p>
<p style="font-size:14px;color:#4a4a68;line-height:1.6;margin:0 0 20px">
    Anda ditambahkan sebagai <strong style="color:#1a1a2e">{{ $roleLabel }}</strong>
    di workspace <strong style="color:#1a1a2e">{{ $workspaceName }}</strong>
    pada perusahaan <strong style="color:#1a1a2e">{{ $companyName }}</strong> (platform LEXLAW).
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fc;border:1px solid #e2e5ec;border-radius:10px;margin-bottom:24px">
<tr><td style="padding:20px 24px">
    <div style="font-size:11px;font-weight:700;color:#5e6ad2;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;border-bottom:1px solid #e2e5ec;padding-bottom:12px">
        Cara Masuk
    </div>
    <p style="font-size:13px;color:#4a4a68;line-height:1.7;margin:0 0 8px">
        1. Email akun Anda: <strong style="color:#1a1a2e">{{ $email }}</strong><br>
        2. Klik <strong>Lupa Password</strong> di halaman login untuk membuat password Anda sendiri.<br>
        3. Masuk, lalu verifikasi email untuk mengaktifkan fitur lengkap.
    </p>
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px">
<tr><td style="padding:0 36px">
    <table cellpadding="0" cellspacing="0" style="margin:0 auto">
    <tr>
        <td style="background:linear-gradient(135deg,#5e6ad2,#7c3aed);border-radius:8px;text-align:center">
            <a href="{{ config('app.frontend_url', config('app.url')) }}/login"
               style="display:inline-block;padding:13px 32px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none">
                Buka LEXLAW →
            </a>
        </td>
    </tr>
    </table>
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0"><tr>
    <td style="padding:0 36px"><div style="border-top:1px solid #e2e5ec"></div></td>
</tr></table>

<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:20px 36px 28px;text-align:center">
    <p style="font-size:12px;color:#9ca3af;margin:0 0 8px;line-height:1.6">
        Punya pertanyaan? Hubungi <a href="mailto:support@lexlaw.arktech.id" style="color:#5e6ad2;text-decoration:none">support@lexlaw.arktech.id</a>
    </p>
    <p style="font-size:11px;color:#c0c4cc;margin:0">LEXLAW — product by https://arktech.id/ &middot; © 2026</p>
</td></tr>
</table>

</div>
</div>