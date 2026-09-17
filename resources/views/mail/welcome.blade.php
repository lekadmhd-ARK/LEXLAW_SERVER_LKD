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
    <div style="font-size:22px;font-weight:700;color:#fff;line-height:1.3">Selamat Datang, {{ $name }}!</div>
</td></tr>
</table>

<!-- Body -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:28px 36px">

<p style="font-size:15px;color:#1a1a2e;margin:0 0 12px">Halo <strong>{{ $name }}</strong>,</p>
<p style="font-size:14px;color:#4a4a68;line-height:1.6;margin:0 0 20px">
    Akun Anda <strong>berhasil dibuat</strong> dan siap digunakan.
    @if($company)
    <br><br>Organisasi: <strong style="color:#1a1a2e">{{ $company }}</strong>
    @endif
</p>

<!-- Features Card -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fc;border:1px solid #e2e5ec;border-radius:10px;margin-bottom:24px">
<tr><td style="padding:20px 24px">
    <div style="font-size:11px;font-weight:700;color:#5e6ad2;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;border-bottom:1px solid #e2e5ec;padding-bottom:12px">
        Fitur yang Bisa Anda Gunakan
    </div>
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;line-height:1.7">
        <tr><td style="color:#1a1a2e;padding:4px 0">🤖 <strong>Lex Q&amp;A RAG</strong> — tanya hukum dengan jawaban bersitasi pasal</td></tr>
        <tr><td style="color:#1a1a2e;padding:8px 0 4px">📑 <strong>Contract Reviewer</strong> — upload PDF/DOCX untuk analisis risiko</td></tr>
        <tr><td style="color:#1a1a2e;padding:8px 0 4px">✍️ <strong>Legal Drafting</strong> — draft NDA, MoU, dan perjanjian langsung jadi <code>.docx</code></td></tr>
        <tr><td style="color:#1a1a2e;padding:8px 0 4px">🗂️ <strong>Database Regulasi</strong> — akses regulasi nasional dari JDIH &amp; BPK</td></tr>
    </table>
</td></tr>
</table>

<!-- Trial Notice -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">
<tr><td style="padding:0 36px">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px">
    <tr>
        <td style="padding:14px 20px;font-size:13px;color:#92400e;font-weight:600;text-align:center;line-height:1.6">
            ⏳ <strong>Masa trial 3 hari.</strong> Kelola langganan Anda lewat menu <strong>Billing</strong> di dashboard sebelum masa trial berakhir.
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
        Terima kasih telah bergabung!<br>
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