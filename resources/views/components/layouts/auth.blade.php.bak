@props(['title' => 'LEXLAW v2 - Legal SaaS'])
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root{--bg:#08090a;--panel:#0f1011;--surface:#151719;--text:#f7f8f8;--muted:#d0d6e0;--dim:#8a8f98;--line:rgba(255,255,255,.08);--line2:rgba(255,255,255,.05);--brand:#5e6ad2;--brand2:#7170ff}
        *{box-sizing:border-box} html,body{margin:0;min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;font-feature-settings:"cv01","ss03";-webkit-font-smoothing:antialiased}
        body:before{content:"";position:fixed;inset:0;background:radial-gradient(circle at 20% 0%,rgba(113,112,255,.18),transparent 40%),radial-gradient(circle at 90% 90%,rgba(16,185,129,.06),transparent 30%);pointer-events:none;z-index:-1}
        a{color:inherit;text-decoration:none} button,input,select,textarea{font:inherit}
        .auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 24px}
        .auth-card{width:100%;max-width:420px;background:rgba(15,16,17,.7);border:1px solid var(--line);border-radius:18px;padding:36px;backdrop-filter:blur(20px);box-shadow:0 20px 80px rgba(0,0,0,.5)}
        .brand-row{display:flex;align-items:center;gap:12px;margin-bottom:28px}
        .logo{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--brand),#9b8cff);display:grid;place-items:center}
        .brand-row h1{margin:0;font-size:18px;font-weight:600;letter-spacing:-.4px}
        .brand-row p{margin:0;color:var(--dim);font-size:11px;text-transform:uppercase;letter-spacing:.9px}
        .auth-card h2{margin:0 0 8px;font-size:28px;font-weight:510;letter-spacing:-.7px}
        .auth-card .sub{margin:0 0 24px;color:var(--dim);font-size:14px}
        .form{display:grid;gap:14px}
        .label{display:block;color:var(--muted);font-size:13px;font-weight:510;margin-bottom:6px}
        input[type=email],input[type=password],input[type=text]{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:8px;color:var(--text);padding:10px 12px;outline:none}
        input:focus{border-color:var(--brand2);box-shadow:0 0 0 3px rgba(113,112,255,.18)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;padding:11px 16px;font-size:14px;font-weight:510;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--muted);cursor:pointer}
        .btn-primary{background:var(--brand);border-color:var(--brand);color:white}
        .btn-primary:hover{background:var(--brand2)}
        .alert{border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.12);color:#ff9b9b;border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:14px}
        .alt-link{text-align:center;margin-top:20px;color:var(--dim);font-size:13px}
        .alt-link a{color:var(--brand2)}
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand-row">
            <div class="logo">⚖</div>
            <div><h1>LEXLAW v2</h1><p>Legal Intelligence</p></div>
        </div>
        {{ $slot }}
    </div>
</div>
@livewireScripts
</body>
</html>