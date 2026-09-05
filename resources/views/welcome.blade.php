<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEXLAW v2 — Legal Intelligence & SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#08090a;--panel:#0f1011;--surface:#151719;--hover:#22242a;--text:#f7f8f8;--muted:#d0d6e0;--dim:#8a8f98;--line:rgba(255,255,255,.08);--line2:rgba(255,255,255,.05);--brand:#5e6ad2;--brand2:#7170ff;--ok:#10b981}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;font-feature-settings:"cv01","ss03";line-height:1.5;-webkit-font-smoothing:antialiased}
        body:before{content:"";position:fixed;inset:0;background:radial-gradient(circle at 50% -20%,rgba(113,112,255,.18),transparent 50%),radial-gradient(circle at 10% 80%,rgba(16,185,129,.06),transparent 40%);pointer-events:none;z-index:-1}
        a{color:inherit;text-decoration:none}
        .nav{position:sticky;top:0;height:68px;border-bottom:1px solid var(--line2);background:rgba(8,9,10,.75);backdrop-filter:blur(16px);display:flex;align-items:center;justify-content:space-between;padding:0 40px;z-index:100}
        .brand{display:flex;align-items:center;gap:12px}.logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--brand),#9b8cff);display:grid;place-items:center;font-weight:600}
        .nav-links{display:flex;gap:24px;font-size:13px;font-weight:500;color:var(--muted)}
        .nav-links a:hover{color:var(--text)}
        .nav-actions{display:flex;gap:12px;align-items:center}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:510;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--muted);cursor:pointer;transition:all .15s}
        .btn:hover{background:rgba(255,255,255,.06);color:var(--text)}
        .btn-primary{background:var(--brand);border-color:var(--brand);color:#fff}
        .btn-primary:hover{background:var(--brand2)}
        .hero{max-width:1080px;margin:80px auto 40px;padding:0 24px;text-align:center}
        .pill{display:inline-flex;align-items:center;gap:8px;padding:4px 12px;border-radius:999px;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--muted);font-size:12px;margin-bottom:24px}
        .hero h1{font-size:56px;line-height:1.05;font-weight:590;letter-spacing:-1.5px;margin:0 0 20px;background:linear-gradient(180deg,#f7f8f8 30%,#8a8f98 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero p{font-size:18px;color:var(--muted);max-width:680px;margin:0 auto 36px;line-height:1.6}
        .hero-cta{display:flex;justify-content:center;gap:16px}
        .preview{max-width:1040px;margin:40px auto 80px;padding:0 24px}
        .preview-box{border:1px solid var(--line);border-radius:16px;background:rgba(15,16,17,.8);overflow:hidden;box-shadow:0 30px 100px rgba(0,0,0,.6)}
        .preview-bar{height:40px;border-bottom:1px solid var(--line2);background:rgba(255,255,255,.02);display:flex;align-items:center;gap:8px;padding:0 16px}
        .dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}
        .features{max-width:1080px;margin:0 auto 80px;padding:0 24px}
        .section-title{text-align:center;margin-bottom:48px}
        .section-title h2{font-size:32px;font-weight:590;letter-spacing:-.8px;margin:0 0 12px}
        .section-title p{color:var(--dim);font-size:16px;margin:0}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .card{background:rgba(255,255,255,.025);border:1px solid var(--line);border-radius:14px;padding:28px}
        .card h3{margin:0 0 10px;font-size:18px;font-weight:590}
        .card p{margin:0;color:var(--dim);font-size:14px;line-height:1.6}
        .pricing{max-width:960px;margin:0 auto 100px;padding:0 24px}
        .pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .price-card{background:rgba(255,255,255,.02);border:1px solid var(--line);border-radius:16px;padding:32px;display:flex;flex-direction:column}
        .price-card.featured{background:linear-gradient(180deg,rgba(94,106,210,.12),rgba(255,255,255,.02));border-color:var(--brand)}
        .price{font-size:36px;font-weight:590;margin:16px 0 24px}
        .features-list{list-style:none;padding:0;margin:0 0 32px;flex:1}
        .features-list li{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--muted);margin-bottom:12px}
        footer{border-top:1px solid var(--line2);padding:40px 0;text-align:center;color:var(--dim);font-size:13px}
        @media(max-width:768px){.hero h1{font-size:36px}.grid-3,.pricing-grid{grid-template-columns:1fr}.nav{padding:0 20px}.nav-links{display:none}}
    </style>
</head>
<body>
    <header class="nav">
        <div class="brand">
            <div class="logo">⚖</div>
            <span style="font-weight:600;font-size:16px;letter-spacing:-.3px">LEXLAW v2</span>
        </div>
        <div class="nav-links">
            <a href="#features">Fitur</a>
            <a href="#pricing">Harga</a>
            <a href="#ai">AI Intelligence</a>
        </div>
        <div class="nav-actions">
            <a href="/login" class="btn">Login</a>
            <a href="/register" class="btn btn-primary">Mulai Sekarang</a>
        </div>
    </header>

    <section class="hero">
        <div class="pill">✦ Platform Legal Intelligence & SaaS Berbasis AI</div>
        <h1>Sistem Operasi Hukum Modern<br>untuk Perusahaan & Praktisi</h1>
        <p>Integrasi database regulasi nasional, AI Lex Q&A berstandar RAG, automated legal drafting DOCX, dan billing QRIS multi-tenant dalam satu platform presisi.</p>
        <div class="hero-cta">
            <a href="/register" class="btn btn-primary" style="padding:12px 24px;font-size:14px">Akses Dashboard →</a>
            <a href="/login" class="btn" style="padding:12px 24px;font-size:14px">Masuk Akun</a>
        </div>
    </section>

    <div class="preview">
        <div class="preview-box">
            <div class="preview-bar">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                <span style="margin-left:8px;font-size:12px;color:var(--dim);font-family:monospace">lexlaw.arktech.id/dashboard</span>
            </div>
            <div style="padding:32px;background:var(--surface);min-height:340px;display:flex;align-items:center;justify-content:center;text-align:center">
                <div>
                    <div style="font-size:42px;margin-bottom:12px">⚡</div>
                    <h3 style="margin:0 0 6px;font-size:20px">Command Center Aktif</h3>
                    <p style="color:var(--dim);font-size:14px;margin:0">Dashboard multi-tenant dengan isolasi data perusahaan yang aman.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="features" id="features">
        <div class="section-title">
            <h2>Arsitektur Legal Tech Profesional</h2>
            <p>Dirancang khusus untuk efisiensi workflow hukum tingkat lanjut.</p>
        </div>
        <div class="grid-3">
            <div class="card">
                <h3>✦ AI Lex Q&A RAG</h3>
                <p>Pencarian regulasi nasional secara FULLTEXT dipadukan dengan AI untuk jawaban akurat bersumber pasal.</p>
            </div>
            <div class="card">
                <h3>✎ Automated Drafting</h3>
                <p>Buat NDA, Perjanjian Kerjasama, MoU, dan surat formal lainnya secara instan langsung unduh format DOCX.</p>
            </div>
            <div class="card">
                <h3>✓ Validity Checker</h3>
                <p>Deteksi otomatis sitasi UU, PP, Perpres, Permen, dan Perda di dalam dokumen serta verifikasi status hukum aktif.</p>
            </div>
        </div>
    </section>

    <section class="pricing" id="pricing">
        <div class="section-title">
            <h2>Pilihan Paket Perusahaan</h2>
            <p>Fleksibel sesuai skala praktik hukum atau korporasi Anda.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>Starter</h3>
                <p class="muted" style="font-size:13px;margin:4px 0 16px">Untuk praktisi hukum independen.</p>
                <div class="price">Rp 499rb<span style="font-size:14px;color:var(--dim);font-weight:400">/bln</span></div>
                <ul class="features-list">
                    <li>✓ 50 AI Q&A per bulan</li>
                    <li>✓ Database Regulasi Nasional</li>
                    <li>✓ Dasar Legal Drafting</li>
                    <li>✓ Support Email</li>
                </ul>
                <a href="/register" class="btn" style="width:100%;justify-content:center">Pilih Starter</a>
            </div>
            <div class="price-card featured">
                <span class="badge badge-ok" style="align-self:flex-start;margin-bottom:12px">Most Popular</span>
                <h3>Professional</h3>
                <p class="muted" style="font-size:13px;margin:4px 0 16px">Untuk firma hukum & kantor konsultan.</p>
                <div class="price">Rp 1.4jt<span style="font-size:14px;color:var(--dim);font-weight:400">/bln</span></div>
                <ul class="features-list">
                    <li>✓ Unlimited AI Lex Q&A</li>
                    <li>✓ Full Validity Checker & Parser</li>
                    <li>✓ Advanced DOCX Generator</li>
                    <li>✓ Multi-Tenant Team Workspaces</li>
                    <li>✓ Prioritas Support</li>
                </ul>
                <a href="/register" class="btn btn-primary" style="width:100%;justify-content:center">Pilih Professional</a>
            </div>
            <div class="price-card">
                <h3>Enterprise</h3>
                <p class="muted" style="font-size:13px;margin:4px 0 16px">Untuk korporasi & instansi besar.</p>
                <div class="price">Custom</div>
                <ul class="features-list">
                    <li>✓ Custom AI Model Deployment</li>
                    <li>✓ Dedicated Database Tenant</li>
                    <li>✓ On-Premise / Private Cloud</li>
                    <li>✓ SLA & Dedicated Account Manager</li>
                </ul>
                <a href="/contact" class="btn" style="width:100%;justify-content:center">Hubungi Sales</a>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2026 LEXLAW v2 — Legal Intelligence SaaS. All rights reserved.</p>
    </footer>
</body>
</html>
