@props(['title' => 'LEXLAW v2 - Legal SaaS'])
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root{--bg:#08090a;--panel:#0f1011;--surface:#151719;--card:#111214;--text:#f7f8f8;--muted:#a1a8b5;--dim:#6b7280;--line:rgba(255,255,255,.07);--line2:rgba(255,255,255,.04);--brand:#5e6ad2;--brand2:#7c5cff;--ok:#10b981}
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;-webkit-font-smoothing:antialiased}
        a{color:inherit;text-decoration:none}
        button,input{font:inherit}
        .auth-shell{min-height:100vh;display:grid;grid-template-columns:1fr 1.15fr}
        /* LEFT */
        .auth-left{position:relative;background:radial-gradient(900px 600px at 20% 10%,rgba(94,106,210,.22),transparent 60%),radial-gradient(700px 500px at 80% 90%,rgba(16,185,129,.09),transparent 50%),linear-gradient(180deg,#0c0d10,#08090a);border-right:1px solid var(--line2);padding:40px 36px;display:flex;flex-direction:column;overflow:hidden}
        .auth-left:before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 0%,rgba(255,255,255,.03),transparent 40%);pointer-events:none}
        .brand{display:flex;align-items:center;gap:10px;position:relative}
        .logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:grid;place-items:center;font-weight:700;font-size:15px;color:#fff;box-shadow:0 4px 16px rgba(94,106,210,.35)}
        .brand b{font-size:15px;font-weight:700;letter-spacing:-.3px}
        .brand span{font-size:12px;color:var(--muted);letter-spacing:.3px}
        .hero{margin-top:auto;position:relative}
        .hero h1{font-size:32px;font-weight:700;letter-spacing:-1.2px;line-height:1.1;margin-bottom:12px}
        .hero h1 em{font-style:normal;background:linear-gradient(135deg,var(--brand),var(--brand2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero p{font-size:13.5px;color:var(--muted);line-height:1.65;margin-bottom:22px;max-width:380px}
        .feat-list{list-style:none;display:grid;gap:10px}
        .feat{display:flex;gap:10px;align-items:flex-start;background:rgba(255,255,255,.03);border:1px solid var(--line2);border-radius:12px;padding:12px}
        .feat .ic{width:30px;height:30px;border-radius:8px;display:grid;place-items:center;font-size:14px;flex-shrink:0;border:1px solid var(--line)}
        .feat strong{font-size:12.5px;display:block}
        .feat span{font-size:12px;color:var(--dim)}
        .left-foot{margin-top:20px;font-size:11px;color:var(--dim);position:relative}
        /* RIGHT */
        .auth-right{display:flex;align-items:center;justify-content:center;padding:32px 24px;background:var(--bg)}
        .auth-card{width:100%;max-width:420px}
        .card-head{margin-bottom:22px}
        .card-head h2{font-size:24px;font-weight:700;letter-spacing:-.6px;margin-bottom:6px}
        .card-head p{font-size:13px;color:var(--muted)}
        .form{display:grid;gap:14px}
        .field{display:grid;gap:6px}
        .field label{font-size:12px;font-weight:600;color:var(--muted);letter-spacing:.2px}
        .input-wrap{position:relative}
        .input-wrap .ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--dim);pointer-events:none}
        input[type=email],input[type=password],input[type=text]{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:10px;color:var(--text);padding:11px 12px 11px 34px;outline:none;font-size:13.5px;transition:border-color .15s,box-shadow .15s}
        input:focus{border-color:rgba(94,106,210,.5);box-shadow:0 0 0 3px rgba(94,106,210,.15);background:rgba(255,255,255,.06)}
        input::placeholder{color:var(--dim)}
        .row-between{display:flex;align-items:center;justify-content:space-between;font-size:12.5px}
        .check{display:flex;align-items:center;gap:7px;color:var(--muted);cursor:pointer}
        .check input{accent-color:var(--brand)}
        .link{color:var(--brand2);font-weight:500}
        .link:hover{color:var(--brand)}
        .btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border-radius:10px;font-size:13.5px;font-weight:600;border:1px solid transparent;cursor:pointer;transition:all .18s}
        .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 6px 20px rgba(94,106,210,.3)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(94,106,210,.38)}
        .btn-primary:active{transform:none}
        .alert{border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#ff9b9b;border-radius:10px;padding:10px 12px;font-size:12.5px}
        .alt{margin-top:18px;text-align:center;font-size:13px;color:var(--dim)}
        .alt a{color:var(--brand2);font-weight:600}
        .alt a:hover{color:var(--brand)}
        .divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:var(--dim);font-size:11px;letter-spacing:.4px;text-transform:uppercase}
        .divider:before,.divider:after{content:"";flex:1;height:1px;background:var(--line2)}
        @media(max-width:900px){
            .auth-shell{grid-template-columns:1fr}
            .auth-left{display:none}
            .auth-right{padding:24px 16px}
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-left">
        <a href="/" class="brand">
            <div class="logo">⚖</div>
            <div><b>LEXLAW v2</b><br><span>Legal Intelligence</span></div>
        </a>
        <div class="hero">
            <h1>Kerja hukum<br><em>10× lebih cepat</em></h1>
            <p>4 AI Tools + database regulasi nasional dalam satu dashboard. Draft, cek validitas, dan bedah kontrak dalam detik.</p>
            <ul class="feat-list">
                <li class="feat"><div class="ic" style="background:rgba(94,106,210,.12)">✦</div><div><strong>Lex Q&A RAG — anti halusinasi</strong><span>Jawaban dengan sitasi pasal & sumber regulasi</span></div></li>
                <li class="feat"><div class="ic" style="background:rgba(16,185,129,.1)">✎</div><div><strong>Draft DOCX instan</strong><span>NDA, MoU, Perjanjian — langsung jadi .docx</span></div></li>
                <li class="feat"><div class="ic" style="background:rgba(124,92,255,.1)">📄</div><div><strong>Contract Reviewer</strong><span>Upload PDF/DOCX → laporan risiko + PDF/Word</span></div></li>
            </ul>
        </div>
        <div class="left-foot">© 2026 LEXLAW v2 · Data regulasi public domain · <a href="/" style="text-decoration:underline">Kembali ke landing</a></div>
    </div>
    <div class="auth-right">
        <div class="auth-card">
            {{ $slot }}
        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
