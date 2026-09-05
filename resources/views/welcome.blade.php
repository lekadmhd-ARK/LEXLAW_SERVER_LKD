<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEXLAW v2 — Legal Intelligence & SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#08090a;--panel:#0f1011;--surface:#151719;--card:#111214;--text:#f7f8f8;--muted:#a1a8b5;--dim:#6b7280;--line:rgba(255,255,255,.07);--line2:rgba(255,255,255,.04);--brand:#5e6ad2;--brand2:#7c5cff;--ok:#10b981;--warn:#f59e0b}
        *{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        html,body{min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;line-height:1.6;-webkit-font-smoothing:antialiased}
        body:before{content:"";position:fixed;inset:0;background:radial-gradient(ellipse 900px 500px at 50% -10%,rgba(94,106,210,.18),transparent 60%),radial-gradient(ellipse 700px 400px at 85% 70%,rgba(16,185,129,.07),transparent 50%),radial-gradient(ellipse 600px 400px at 10% 85%,rgba(124,92,255,.08),transparent 50%);pointer-events:none;z-index:-1}
        a{color:inherit;text-decoration:none}
        /* NAV */
        .nav{position:sticky;top:0;height:64px;border-bottom:1px solid var(--line2);background:rgba(8,9,10,.8);backdrop-filter:blur(20px) saturate(1.2);display:flex;align-items:center;justify-content:space-between;padding:0 32px;z-index:100}
        .brand{display:flex;align-items:center;gap:10px}
        .logo{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:grid;place-items:center;font-weight:700;font-size:15px;color:#fff;box-shadow:0 4px 16px rgba(94,106,210,.35)}
        .brand b{font-weight:700;font-size:15px;letter-spacing:-.4px}
        .brand span{font-weight:300;font-size:13px;color:var(--muted);letter-spacing:.3px}
        .nav-links{display:flex;gap:22px;font-size:13px;font-weight:500;color:var(--muted)}
        .nav-links a{transition:color .15s}
        .nav-links a:hover{color:var(--text)}
        .nav-actions{display:flex;gap:10px;align-items:center}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:550;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--text);cursor:pointer;transition:all .18s;white-space:nowrap}
        .btn:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.12);transform:translateY(-1px)}
        .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));border-color:transparent;color:#fff;box-shadow:0 4px 16px rgba(94,106,210,.3)}
        .btn-primary:hover{background:linear-gradient(135deg,#6b78e0,#8b6cff);box-shadow:0 6px 20px rgba(94,106,210,.4)}
        .hamburger{display:none;width:36px;height:36px;border-radius:8px;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--text);font-size:18px;cursor:pointer;place-items:center}
        .mobile-menu{display:none;position:fixed;inset:64px 0 0 0;background:rgba(8,9,10,.97);backdrop-filter:blur(20px);z-index:99;padding:24px;flex-direction:column;gap:16px}
        .mobile-menu.open{display:flex}
        .mobile-menu a{font-size:15px;font-weight:500;color:var(--muted);padding:12px 0;border-bottom:1px solid var(--line2)}
        .mobile-menu a:hover{color:var(--text)}
        /* HERO */
        .hero{max-width:1100px;margin:0 auto;padding:72px 24px 0;text-align:center}
        .pill{display:inline-flex;align-items:center;gap:8px;padding:5px 14px;border-radius:999px;border:1px solid rgba(94,106,210,.25);background:rgba(94,106,210,.08);color:var(--muted);font-size:11px;font-weight:500;letter-spacing:.4px;margin-bottom:20px}
        .pill b{width:6px;height:6px;border-radius:50%;background:var(--ok);box-shadow:0 0 8px var(--ok);display:inline-block;animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
        .hero h1{font-size:clamp(32px,5.5vw,54px);line-height:1.06;font-weight:700;letter-spacing:-1.8px;margin:0 0 16px}
        .hero h1 span{background:linear-gradient(135deg,#f7f8f8 40%,#8a8f98 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero h1 em{font-style:normal;background:linear-gradient(135deg,var(--brand),var(--brand2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero p{font-size:clamp(15px,2vw,18px);color:var(--muted);max-width:680px;margin:0 auto 28px;line-height:1.65}
        .hero-cta{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-bottom:40px}
        .hero-cta .btn{padding:12px 22px;font-size:14px;border-radius:12px}
        .hero-stats{display:flex;flex-wrap:wrap;justify-content:center;gap:28px;margin-top:8px;padding:20px 0;border-top:1px solid var(--line2);border-bottom:1px solid var(--line2)}
        .stat{text-align:center}
        .stat strong{font-size:22px;font-weight:700;letter-spacing:-.5px;display:block}
        .stat span{font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:.6px}
        /* PREVIEW */
        .preview{max-width:1040px;margin:36px auto 0;padding:0 24px}
        .preview-box{border:1px solid var(--line);border-radius:18px;background:rgba(15,16,17,.85);overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.03) inset}
        .preview-bar{height:42px;border-bottom:1px solid var(--line2);background:rgba(255,255,255,.02);display:flex;align-items:center;gap:8px;padding:0 16px}
        .dot{width:10px;height:10px;border-radius:50%}
        .dot:nth-child(1){background:#ff5f57}.dot:nth-child(2){background:#ffbd2e}.dot:nth-child(3){background:#28c840}
        .preview-body{padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .preview-card{background:rgba(255,255,255,.03);border:1px solid var(--line2);border-radius:12px;padding:16px}
        .preview-card .icon{width:32px;height:32px;border-radius:8px;display:grid;place-items:center;font-size:14px;margin-bottom:10px}
        .preview-card h4{font-size:13px;font-weight:600;margin-bottom:4px}
        .preview-card p{font-size:12px;color:var(--dim);line-height:1.5}
        /* SECTIONS */
        .section{max-width:1100px;margin:0 auto;padding:64px 24px}
        .section-head{text-align:center;margin-bottom:36px}
        .section-head .eyebrow{font-size:11px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--brand);margin-bottom:8px}
        .section-head h2{font-size:clamp(24px,3.5vw,34px);font-weight:700;letter-spacing:-.8px;margin:0 0 10px}
        .section-head p{color:var(--dim);font-size:14px;max-width:560px;margin:0 auto}
        /* AI GRID */
        .ai-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
        .ai-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:24px;position:relative;overflow:hidden;transition:all .22s}
        .ai-card:before{content:"";position:absolute;inset:0;background:radial-gradient(400px 200px at 50% 0%,rgba(94,106,210,.08),transparent 60%);opacity:0;transition:opacity .22s;pointer-events:none}
        .ai-card:hover{transform:translateY(-4px);border-color:rgba(94,106,210,.25);box-shadow:0 16px 40px rgba(0,0,0,.3),0 0 0 1px rgba(94,106,210,.1) inset}
        .ai-card:hover:before{opacity:1}
        .ai-card .ai-icon{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;font-size:18px;margin-bottom:14px;border:1px solid var(--line)}
        .ai-card h3{font-size:14px;font-weight:650;margin-bottom:6px}
        .ai-card p{font-size:13px;color:var(--dim);line-height:1.6;margin-bottom:12px}
        .ai-card .ai-meta{display:flex;gap:8px;flex-wrap:wrap}
        .ai-tag{font-size:10px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;padding:3px 8px;border-radius:999px;border:1px solid var(--line);color:var(--muted);background:rgba(255,255,255,.02)}
        .ai-tag.ok{color:var(--ok);border-color:rgba(16,185,129,.2);background:rgba(16,185,129,.07)}
        /* PRICING */
        .pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start}
        .price-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:28px;display:flex;flex-direction:column;position:relative;transition:all .22s}
        .price-card:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.25)}
        .price-card.featured{border-color:var(--brand);background:linear-gradient(180deg,rgba(94,106,210,.12),rgba(17,18,20,1));box-shadow:0 20px 50px rgba(94,106,210,.18);transform:scale(1.02)}
        .price-card.featured:hover{transform:scale(1.03) translateY(-2px)}
        .badge-pop{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;font-size:10px;font-weight:700;padding:4px 12px;border-radius:999px;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap;box-shadow:0 4px 12px rgba(94,106,210,.4)}
        .price-card h3{font-size:16px;font-weight:700;margin-bottom:2px}
        .price-card .plan-desc{font-size:12px;color:var(--dim);margin-bottom:14px;min-height:18px}
        .price{font-size:32px;font-weight:700;letter-spacing:-1px;margin-bottom:2px}
        .price small{font-size:13px;font-weight:400;color:var(--dim)}
        .price-sub{font-size:11px;color:var(--dim);margin-bottom:18px}
        .features-list{list-style:none;padding:0;margin:0 0 20px;flex:1}
        .features-list li{display:flex;gap:9px;font-size:12.5px;color:var(--muted);margin-bottom:9px;line-height:1.4}
        .features-list li i{color:var(--ok);font-style:normal;font-weight:700;flex-shrink:0}
        .features-list li.muted{color:var(--dim)}
        .features-list li.muted i{color:var(--dim)}
        /* FAQ */
        .faq-wrap{max-width:760px;margin:0 auto}
        .faq-item{border:1px solid var(--line);border-radius:12px;margin-bottom:10px;overflow:hidden;background:var(--card);transition:border-color .15s}
        .faq-item:hover{border-color:rgba(94,106,210,.2)}
        .faq-item.open{border-color:rgba(94,106,210,.3)}
        .faq-q{width:100%;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 18px;background:none;border:none;color:var(--text);font-size:13.5px;font-weight:550;font-family:inherit;cursor:pointer;text-align:left}
        .faq-q span:last-child{font-size:11px;color:var(--dim);transition:transform .25s;flex-shrink:0;width:20px;height:20px;border-radius:50%;border:1px solid var(--line);display:grid;place-items:center}
        .faq-item.open .faq-q span:last-child{transform:rotate(180deg);background:var(--brand);color:#fff;border-color:var(--brand)}
        .faq-a{max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s ease;padding:0 18px;color:var(--muted);font-size:13px;line-height:1.7}
        .faq-item.open .faq-a{max-height:300px;padding:0 18px 16px;border-top:1px solid var(--line2);margin-top:0;padding-top:12px}
        /* LEGAL */
        .legal-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .legal-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:26px}
        .legal-card h3{font-size:14px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:8px}
        .legal-card ul{list-style:none;padding:0;margin:0}
        .legal-card li{font-size:12.5px;color:var(--muted);line-height:1.7;padding:7px 0;border-bottom:1px solid var(--line2);display:flex;gap:8px}
        .legal-card li:last-child{border:none}
        .legal-card li b{color:var(--text);flex-shrink:0}
        /* CONTACT */
        .contact-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:20px;align-items:stretch}
        .contact-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:26px}
        .contact-card h3{font-size:15px;font-weight:700;margin-bottom:6px}
        .contact-card p{font-size:13px;color:var(--dim);margin-bottom:16px}
        .contact-row{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--line);border-radius:10px;margin-bottom:10px;background:rgba(255,255,255,.02)}
        .contact-row .ci{width:36px;height:36px;border-radius:8px;background:rgba(94,106,210,.12);border:1px solid rgba(94,106,210,.2);display:grid;place-items:center;font-size:16px;flex-shrink:0}
        .contact-row strong{font-size:13px;display:block}
        .contact-row span{font-size:12px;color:var(--dim)}
        .wa-btn{display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;padding:12px 20px;border-radius:10px;font-weight:600;font-size:13px;transition:all .18s}
        .wa-btn:hover{background:#128C7E;transform:translateY(-1px);box-shadow:0 6px 16px rgba(37,211,102,.3)}
        /* MODAL POPUP */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(10px);z-index:200;align-items:center;justify-content:center;padding:20px}
        .modal-overlay.active{display:flex}
        .modal-box{width:100%;max-width:720px;max-height:85vh;background:#0f1012;border:1px solid var(--line);border-radius:18px;display:flex;flex-direction:column;box-shadow:0 30px 90px rgba(0,0,0,.7);overflow:hidden}
        .modal-head{padding:18px 24px;border-bottom:1px solid var(--line2);display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.02)}
        .modal-head h3{font-size:16px;font-weight:700;margin:0}
        .modal-close{width:30px;height:30px;border-radius:8px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--text);font-size:16px;cursor:pointer;display:grid;place-items:center}
        .modal-close:hover{background:rgba(255,255,255,.1)}
        .modal-body{padding:24px;overflow-y:auto;font-size:13.5px;color:var(--muted);line-height:1.75}
        .modal-body h4{color:var(--text);font-size:14px;margin:18px 0 8px}
        .modal-body h4:first-child{margin-top:0}
        .modal-body p{margin-bottom:12px}
        .modal-body ul{padding-left:18px;margin-bottom:12px}
        .modal-body li{margin-bottom:6px}
        /* FOOTER */
        footer{border-top:1px solid var(--line2);padding:28px 24px;text-align:center;color:var(--dim);font-size:12px}
        footer button.link-btn{background:none;border:none;color:var(--muted);text-decoration:underline;text-underline-offset:3px;font:inherit;cursor:pointer;padding:0 4px}
        footer button.link-btn:hover{color:var(--text)}
        /* REVEAL */
        .reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s ease}
        .reveal.in{opacity:1;transform:none}
        /* RESPONSIVE */
        @media(max-width:1024px){
            .ai-grid{grid-template-columns:repeat(2,1fr)}
            .pricing-grid{grid-template-columns:1fr;max-width:440px;margin:0 auto}
            .price-card.featured{transform:none}
            .price-card.featured:hover{transform:translateY(-3px)}
            .legal-grid{grid-template-columns:1fr}
            .contact-grid{grid-template-columns:1fr}
            .preview-body{grid-template-columns:1fr}
        }
        @media(max-width:768px){
            .nav{padding:0 16px;height:60px}
            .nav-links,.nav-actions .btn:not(.btn-primary){display:none}
            .hamburger{display:grid}
            .hero{padding:40px 16px 0}
            .section{padding:48px 16px}
            .ai-grid{grid-template-columns:1fr}
            .hero-stats{gap:16px}
        }
        @media(max-width:480px){
            .hero h1{letter-spacing:-1px}
            .preview{padding:0 16px}
        }
    </style>
</head>
<body>
    <header class="nav">
        <a href="/" class="brand">
            <div class="logo">⚖</div>
            <div><b>LEXLAW v2</b> <span>Legal Intelligence</span></div>
        </a>
        <nav class="nav-links">
            <a href="#ai-features">Fitur AI</a>
            <a href="#pricing">Harga</a>
            <a href="#faq">FAQ</a>
            <a href="#legal">Legal</a>
            <a href="#contact">Kontak</a>
        </nav>
        <div class="nav-actions">
            <a href="/login" class="btn">Masuk</a>
            <a href="/register" class="btn btn-primary">Mulai Gratis →</a>
            <button class="hamburger" id="hamburger" aria-label="Menu">☰</button>
        </div>
    </header>
    <div class="mobile-menu" id="mobileMenu">
        <a href="#ai-features" onclick="closeMenu()">✦ Fitur AI</a>
        <a href="#pricing" onclick="closeMenu()">💰 Harga</a>
        <a href="#faq" onclick="closeMenu()">❓ FAQ</a>
        <a href="#legal" onclick="closeMenu()">📜 Syarat & Refund</a>
        <a href="#contact" onclick="closeMenu()">📍 Kontak</a>
        <div style="display:flex;gap:10px;margin-top:8px">
            <a href="/login" class="btn" style="flex:1;justify-content:center">Masuk</a>
            <a href="/register" class="btn btn-primary" style="flex:1;justify-content:center">Daftar</a>
        </div>
    </div>

    <section class="hero">
        <div class="pill"><b></b> Platform Legal Intelligence & SaaS Berbasis AI — Live</div>
        <h1><span>Sistem Operasi Hukum</span><br><em>Modern untuk Praktisi</em></h1>
        <p>Database regulasi nasional + 4 AI Tools terintegrasi — Lex Q&A, Draft DOCX, Validity Checker & Contract Reviewer — dalam satu dashboard multi-tenant yang aman.</p>
        <div class="hero-cta">
            <a href="/register" class="btn btn-primary" style="padding:13px 26px;font-size:14px">Coba Gratis Sekarang →</a>
            <a href="#ai-features" class="btn" style="padding:13px 22px;font-size:14px">Lihat Fitur AI ↓</a>
        </div>
        <div class="hero-stats">
            <div class="stat"><strong>500+</strong><span>Regulasi Terindeks</span></div>
            <div class="stat"><strong>4</strong><span>AI Tools Aktif</span></div>
            <div class="stat"><strong>~15 detik</strong><span>Rata-rata Analisis</span></div>
            <div class="stat"><strong>7 Hari</strong><span>Garansi Refund</span></div>
        </div>
    </section>

    <div class="preview reveal">
        <div class="preview-box">
            <div class="preview-bar">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                <span style="margin-left:8px;font-size:11px;color:var(--dim);font-family:JetBrains Mono">lexlaw.arktech.id/dashboard — Command Center</span>
                <span style="margin-left:auto;font-size:10px;color:var(--ok);font-weight:600;letter-spacing:.4px">● LIVE</span>
            </div>
            <div class="preview-body">
                <div class="preview-card">
                    <div class="icon" style="background:rgba(94,106,210,.12);border:1px solid rgba(94,106,210,.2)">✦</div>
                    <h4>Lex Q&A — Tanya Hukum</h4>
                    <p>"Apakah UU Cipta Kerja masih berlaku setelah Putusan MK?" — Jawaban akurat dengan sitasi pasal & sumber regulasi.</p>
                </div>
                <div class="preview-card">
                    <div class="icon" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2)">✎</div>
                    <h4>Draft DOCX Instan</h4>
                    <p>Generate NDA, Perjanjian Kerjasama, MoU langsung jadi file Word siap tanda tangan — 10 detik jadi.</p>
                </div>
                <div class="preview-card">
                    <div class="icon" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2)">✓</div>
                    <h4>Validity Checker</h4>
                    <p>Paste dokumen → deteksi otomatis UU/PP/Perpres/Permen/Perda + cek status masih berlaku atau sudah dicabut.</p>
                </div>
                <div class="preview-card">
                    <div class="icon" style="background:rgba(124,92,255,.1);border:1px solid rgba(124,92,255,.2)">📄</div>
                    <h4>Contract Reviewer</h4>
                    <p>Upload PDF/DOCX kontrak → AI bedah risiko, klausul janggal & saran perbaikan. Download laporan PDF/Word.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="section reveal" id="ai-features">
        <div class="section-head">
            <div class="eyebrow">✦ AI POWERED</div>
            <h2>Semua Tools Hukum dalam Satu Tempat</h2>
            <p>Klik kartu untuk langsung coba — semua terhubung ke database regulasi nasional yang sama.</p>
        </div>
        <div class="ai-grid">
            <a href="/ai/lex-qna" class="ai-card">
                <div class="ai-icon" style="background:rgba(94,106,210,.1);border-color:rgba(94,106,210,.2)">✦</div>
                <h3>Lex Q&A (RAG)</h3>
                <p>Tanya hukum natural language. AI jawab dengan sitasi pasal akurat + link regulasi sumber. Anti-halusinasi.</p>
                <div class="ai-meta"><span class="ai-tag ok">RAG</span><span class="ai-tag">Fulltext Search</span></div>
            </a>
            <a href="/ai/draft" class="ai-card">
                <div class="ai-icon" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.2)">✎</div>
                <h3>Draft DOCX Generator</h3>
                <p>Buat dokumen hukum formal: NDA, MoU, Perjanjian, Surat Kuasa — langsung unduh .docx siap pakai.</p>
                <div class="ai-meta"><span class="ai-tag ok">DOCX</span><span class="ai-tag">3 Varian Style</span></div>
            </a>
            <a href="/ai/validity" class="ai-card">
                <div class="ai-icon" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.2)">✓</div>
                <h3>Validity Checker</h3>
                <p>Tempel teks dokumen → AI ekstrak semua sitasi regulasi & verifikasi apakah masih berlaku.</p>
                <div class="ai-meta"><span class="ai-tag">UU</span><span class="ai-tag">PP</span><span class="ai-tag">Perpres</span><span class="ai-tag">Perda</span></div>
            </a>
            <a href="/ai/contract-review" class="ai-card">
                <div class="ai-icon" style="background:rgba(124,92,255,.1);border-color:rgba(124,92,255,.2)">📄</div>
                <h3>Contract Reviewer</h3>
                <p>Upload kontrak PDF/DOCX atau paste teks → AI analisis risiko, klausul berat sebelah & rekomendasi.</p>
                <div class="ai-meta"><span class="ai-tag ok">PDF/Word Export</span><span class="ai-tag">UID Tracking</span></div>
            </a>
            <a href="/regulations" class="ai-card">
                <div class="ai-icon" style="background:rgba(6,182,212,.1);border-color:rgba(6,182,212,.2)">📚</div>
                <h3>Database Regulasi</h3>
                <p>Akses 500+ regulasi nasional terindeks: UU, PP, Perpres, Permen, Perda — search kilat + auto-fetch.</p>
                <div class="ai-meta"><span class="ai-tag ok">Public Domain</span><span class="ai-tag">JDIH/BPK</span></div>
            </a>
            <a href="/legal-glossary" class="ai-card">
                <div class="ai-icon" style="background:rgba(236,72,153,.1);border-color:rgba(236,72,153,.2)">📖</div>
                <h3>Glosarium Hukum</h3>
                <p>Kamus istilah hukum Indonesia lengkap — definisi presisi untuk mahasiswa, praktisi & korporasi.</p>
                <div class="ai-meta"><span class="ai-tag">Kamus</span><span class="ai-tag">Referensi</span></div>
            </a>
        </div>
    </section>

    <section class="section reveal" id="pricing">
        <div class="section-head">
            <div class="eyebrow">💰 HARGA TRANSPARAN</div>
            <h2>Pilih Paket Sesuai Skala Anda</h2>
            <p>Semua paket akses regulasi unlimited. Beda di kuota AI per bulan — upgrade kapan saja, refund 7 hari.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>Basic</h3>
                <div class="plan-desc">Praktisi independen & freelancer</div>
                <div class="price">Rp 100<span style="font-size:15px;color:var(--dim)">rb</span> <small>/bulan</small></div>
                <div class="price-sub">atau Rp 1jt /tahun</div>
                <ul class="features-list">
                    <li><i>✓</i> 1 user · Regulasi unlimited</li>
                    <li><i>✓</i> Lex Q&A — 30× /bulan</li>
                    <li><i>✓</i> Draft DOCX — 10× /bulan</li>
                    <li><i>✓</i> Contract Review — 10× /bulan</li>
                    <li><i>✓</i> Validity — 30× /bulan</li>
                    <li><i>✓</i> Glosarium & Search</li>
                    <li class="muted"><i>—</i> Tanpa Workspaces</li>
                </ul>
                <a href="/register" class="btn" style="width:100%">Pilih Basic</a>
            </div>
            <div class="price-card featured">
                <div class="badge-pop">★ Most Popular</div>
                <h3>Professional</h3>
                <div class="plan-desc">Firma hukum & konsultan UMKM</div>
                <div class="price">Rp 599<span style="font-size:15px;color:var(--dim)">rb</span> <small>/bulan</small></div>
                <div class="price-sub">atau Rp 5,99jt /tahun</div>
                <ul class="features-list">
                    <li><i>✓</i> 10 users · Regulasi unlimited</li>
                    <li><i>✓</i> Lex Q&A — <b>Unlimited</b></li>
                    <li><i>✓</i> Draft DOCX — 50× /bulan</li>
                    <li><i>✓</i> Contract Review — 50× /bulan</li>
                    <li><i>✓</i> Validity — 100× /bulan</li>
                    <li><i>✓</i> Semua fitur Basic + prioritas</li>
                </ul>
                <a href="/register" class="btn btn-primary" style="width:100%">Pilih Professional</a>
            </div>
            <div class="price-card">
                <h3>Enterprise</h3>
                <div class="plan-desc">Korporasi & instansi besar</div>
                <div class="price">Rp 999<span style="font-size:15px;color:var(--dim)">rb</span> <small>/bulan</small></div>
                <div class="price-sub">atau Rp 9,99jt /tahun · Custom SLA</div>
                <ul class="features-list">
                    <li><i>✓</i> 50 users · Regulasi unlimited</li>
                    <li><i>✓</i> Lex Q&A — <b>Unlimited</b></li>
                    <li><i>✓</i> Draft — <b>Unlimited</b></li>
                    <li><i>✓</i> Contract Review — <b>Unlimited</b></li>
                    <li><i>✓</i> Validity — <b>Unlimited</b></li>
                    <li><i>✓</i> Workspaces + Audit Log + Priority</li>
                </ul>
                <a href="https://wa.me/6281297414115" target="_blank" class="btn" style="width:100%">Hubungi Sales →</a>
            </div>
        </div>
        <p style="text-align:center;margin-top:18px;font-size:12px;color:var(--dim)">Semua harga sudah termasuk akses database regulasi nasional public domain. <a href="#legal" style="text-decoration:underline">Lihat syarat & refund</a></p>
    </section>

    <section class="section reveal" id="faq">
        <div class="section-head">
            <div class="eyebrow">❓ FAQ</div>
            <h2>Pertanyaan yang Sering Ditanyakan</h2>
        </div>
        <div class="faq-wrap">
            <div class="faq-item">
                <button class="faq-q"><span>🔍 Apa itu LEXLAW dan siapa yang cocok pakai?</span><span>▾</span></button>
                <div class="faq-a">LEXLAW adalah SaaS Legal Intelligence untuk praktisi hukum, firma, korporasi & mahasiswa. Cocok untuk yang butuh riset regulasi cepat, draft dokumen instan, dan analisis kontrak tanpa hire banyak staf legal.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q"><span>🛡️ Apakah data kontrak saya aman & tidak disimpan?</span><span>▾</span></button>
                <div class="faq-a">Ya. File kontrak hanya diproses sekali saat analisis lalu dihapus. Tidak disimpan di server. Semua lalu lintas terenkripsi. Lihat Kebijakan Privasi & Disclaimer kami.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q"><span>⏱️ Berapa lama analisis & format file apa didukung?</span><span>▾</span></button>
                <div class="faq-a">Lex Q&A ~5–10 detik, Draft DOCX ~15–30 detik, Contract Review ~20–40 detik. Contract Reviewer dukung PDF, DOCX, TXT (maks 10MB). Hasil bisa diunduh PDF & Word.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q"><span>📄 Apakah AI bisa menggantikan pengacara?</span><span>▾</span></button>
                <div class="faq-a">Tidak. LEXLAW adalah alat bantu referensi & produktivitas — bukan nasihat hukum profesional. Selalu verifikasi hasil AI dengan advokat berlisensi sebelum eksekusi.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q"><span>💳 Bagaimana pembayaran & refund?</span><span>▾</span></button>
                <div class="faq-a">Bayar via QRIS (Midtrans). Garansi refund 100% dalam 7 hari kalender sejak transaksi pertama. Klaim via email support — dana kembali 3–14 hari kerja.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q"><span>🔄 Bisakah upgrade/downgrade paket kapan saja?</span><span>▾</span></button>
                <div class="faq-a">Bisa. Upgrade langsung aktif & kuota menyesuaikan. Downgrade berlaku periode berikutnya. Kuota bulanan reset otomatis tiap tanggal perpanjangan.</div>
            </div>
        </div>
    </section>

    <section class="section reveal" id="legal">
        <div class="section-head">
            <div class="eyebrow">⚖️ LEGALITAS</div>
            <h2>Aman Secara Hukum</h2>
            <p>Transparan dari awal — klik tombol di bawah untuk baca dokumen lengkap via popup tanpa perlu login.</p>
        </div>
        <div class="legal-grid">
            <div class="legal-card">
                <h3>📜 Syarat & Ketentuan</h3>
                <ul>
                    <li><b>1.</b> Dengan mendaftar Anda menyetujui S&K, Disclaimer & Kebijakan Privasi LEXLAW.</li>
                    <li><b>2.</b> Layanan: pencarian regulasi, analisis kontrak, drafting & validity — sesuai paket.</li>
                    <li><b>3.</b> Anda bertanggung jawab atas keamanan akun & dilarang pakai layanan untuk aktivitas ilegal.</li>
                    <li><b>4.</b> Output AI bersifat informatif, bukan legal opinion — wajib verifikasi mandiri.</li>
                </ul>
                <button onclick="openModal('terms')" class="btn" style="width:100%;margin-top:14px;justify-content:center">Baca S&K (Popup) →</button>
            </div>
            <div class="legal-card">
                <h3>💰 Kebijakan Refund</h3>
                <ul>
                    <li><b>7 Hari</b> — Garansi uang kembali 100% sejak transaksi pertama, tanpa ribet.</li>
                    <li><b>Klaim</b> — Email ke support@lexlaw.arktech.id + bukti bayar + alasan.</li>
                    <li><b>Pengecualian</b> — Lewat 7 hari, renewal, atau pelanggaran S&K tidak dapat refund.</li>
                    <li><b>Proses</b> — Dana kembali 3–14 hari kerja via metode bayar asal / transfer bank.</li>
                </ul>
                <button onclick="openModal('refund')" class="btn" style="width:100%;margin-top:14px;justify-content:center">Baca Refund Policy (Popup) →</button>
            </div>
        </div>
        <div class="legal-card" style="margin-top:16px;text-align:center">
            <h3 style="justify-content:center">🛡️ Disclaimer & Penafian Hukum</h3>
            <p style="font-size:12.5px;color:var(--muted);line-height:1.7">Data regulasi bersumber dari dokumen resmi pemerintah Indonesia (public domain) — informasi bersifat referensi dan tidak menggantikan dokumen resmi / lembaran negara. LEXLAW tidak menciptakan hubungan advokat-klien. Penggunaan output AI sepenuhnya tanggung jawab pengguna.</p>
            <button onclick="openModal('disclaimer')" class="btn" style="margin-top:12px">Baca Disclaimer (Popup) →</button>
        </div>
    </section>

    <section class="section reveal" id="contact">
        <div class="section-head">
            <div class="eyebrow">📍 HUBUNGI KAMI</div>
            <h2>Butuh Bantuan? Langsung Chat</h2>
        </div>
        <div class="contact-grid">
            <div class="contact-card">
                <h3>Tangerang, Indonesia</h3>
                <p>Respon cepat via WhatsApp — konsultasi paket, demo & support teknis.</p>
                <div class="contact-row">
                    <div class="ci">💬</div>
                    <div><strong>WhatsApp</strong><span>0812-9741-4115 — klik untuk chat langsung</span></div>
                </div>
                <div class="contact-row">
                    <div class="ci">📍</div>
                    <div><strong>Alamat</strong><span>Tangerang, Indonesia</span></div>
                </div>
                <div class="contact-row">
                    <div class="ci">✉️</div>
                    <div><strong>Email</strong><span>support@lexlaw.arktech.id</span></div>
                </div>
                <a href="https://wa.me/6281297414115" target="_blank" rel="noopener" class="wa-btn" style="margin-top:12px;width:100%;justify-content:center">💬 Chat via WhatsApp</a>
            </div>
            <div class="contact-card" style="display:flex;flex-direction:column;justify-content:center;text-align:center;background:linear-gradient(135deg,rgba(94,106,210,.08),rgba(124,92,255,.06));border-color:rgba(94,106,210,.15)">
                <div style="font-size:36px;margin-bottom:10px">⚖️</div>
                <h3>LEXLAW v2</h3>
                <p style="margin-bottom:14px">Legal Intelligence & SaaS Platform<br>AI-powered hukum Indonesia</p>
                <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                    <a href="/register" class="btn btn-primary">Daftar Gratis</a>
                    <a href="https://wa.me/6281297414115" target="_blank" class="btn">Tanya via WA</a>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL OVERLAYS (NO LOGIN REQUIRED) -->
    <div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeModal()">
        <div class="modal-box">
            <div class="modal-head">
                <h3 id="modalTitle">Dokumen Legal</h3>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content injected by JS -->
            </div>
        </div>
    </div>

    <footer>
        <div style="max-width:1100px;margin:0 auto">
            <div>© 2026 LEXLAW v2 — Legal Intelligence & SaaS Platform. All rights reserved.</div>
            <div style="margin-top:6px">Data regulasi public domain · 
                <button class="link-btn" onclick="openModal('disclaimer')">Disclaimer</button> · 
                <button class="link-btn" onclick="openModal('terms')">Syarat & Ketentuan</button> · 
                <button class="link-btn" onclick="openModal('refund')">Refund Policy</button> · 
                <a href="https://wa.me/6281297414115" target="_blank">Kontak WA</a>
            </div>
        </div>
    </footer>

    <script>
    var MODALS = {
        disclaimer: {
            title: '🛡️ Disclaimer & Penafian Hukum',
            html: `<h4>PENTING — Baca Penafian Hukum ini dengan saksama sebelum menggunakan platform LexLaw.</h4>
            <p>Selamat datang di <strong>LexLaw</strong> ("Platform"). Dengan mengakses atau menggunakan fitur AI dan layanan di platform ini, Anda dianggap telah membaca, memahami, dan menyetujui seluruh batasan serta pelepasan tanggung jawab di bawah ini.</p>
            <h4>1. Sifat Layanan Bersifat Informatif, Bukan Nasihat Hukum Formal</h4>
            <p>Seluruh output, draf dokumen, analisis, dan jawaban dari AI LEXLAW murni berfungsi sebagai <strong>alat bantu referensi, edukasi, dan penunjang produktivitas</strong>. Output AI <strong>tidak menggantikan pendapat hukum profesional</strong> (legal opinion) dari advokat atau konsultan hukum yang berlisensi.</p>
            <h4>2. Tidak Ada Hubungan Hukum Klien-Advokat</h4>
            <p>Penggunaan fitur AI di LEXLAW <strong>tidak menciptakan hubungan hukum formal</strong> antara advokat dan klien.</p>
            <h4>3. Batasan Akurasi & Tanggung Jawab Verifikasi Pengguna</h4>
            <p>Sistem AI dapat mengalami keterbatasan teknis, kekeliruan konteks, atau ketidaksesuaian dengan pembaruan regulasi terbaru. Pengguna <strong>wajib melakukan pemeriksaan mandiri</strong> (independent legal review) atau meminta validasi dari tenaga hukum profesional sebelum mengesahkan dokumen hukum apa pun.</p>
            <h4>4. Pelepasan Tanggung Jawab Hukum</h4>
            <p>Pihak manajemen LEXLAW <strong>tidak bertanggung jawab</strong> atas segala kerugian materiil/immateriil, sengketa, kerugian bisnis, atau konsekuensi hukum apa pun yang timbul akibat penggunaan atau ketergantungan penuh pada hasil keluaran AI.</p>
            <h4>5. Data Regulasi & Sumber Resmi</h4>
            <p>Data regulasi bersumber dari dokumen resmi pemerintah Indonesia (public domain - UU No. 28 Tahun 2014 tentang Hak Cipta Pasal 43 huruf a). Informasi ini bersifat referensi dan tidak menggantikan lembaran negara resmi.</p>`
        },
        terms: {
            title: '📜 Syarat & Ketentuan Layanan',
            html: `<h4>PENTING — Baca Syarat & Ketentuan ini sebelum menggunakan platform LexLaw.</h4>
            <p>Dokumen ini mengatur hubungan antara Anda ("Pengguna") dan LexLaw ("Platform") terkait penggunaan layanan, fitur, dan konten yang disediakan.</p>
            <h4>1. Penerimaan Syarat & Ketentuan</h4>
            <p>Dengan mengakses, mendaftar, atau menggunakan layanan LexLaw dalam bentuk apa pun, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh Syarat & Ketentuan ini serta Kebijakan Privasi dan Disclaimer yang terkait.</p>
            <h4>2. Jangkauan Layanan</h4>
            <p>LexLaw menyediakan platform berbasis web yang mengintegrasikan kecerdasan buatan (AI) untuk: pencarian & analisis peraturan perundang-undangan Indonesia, analisis kontrak bisnis (Contract Reviewer), pembuatan draft dokumen hukum, dan validasi prinsip hukum & perundang-undangan.</p>
            <h4>3. Kewajiban Pengguna</h4>
            <p>Anda bertanggung jawab sepenuhnya atas keamanan akun Anda. Anda dilarang menggunakan Layanan untuk melakukan aktivitas ilegal, termasuk tetapi tidak terbatas pada: pencucian uang, penipuan, pelanggaran hak cipta, serta pengaksesan sistem tanpa izin.</p>
            <h4>4. Keamanan Akun & Kerahasiaan Data</h4>
            <p>Anda bertanggung jawab untuk menjaga kerahasiaan kredensial masuk Anda. Dokumen kontrak yang diunggah untuk dianalisis diproses secara temporary dan tidak disimpan secara permanen di server kami.</p>`
        },
        refund: {
            title: '💰 Kebijakan Pengembalian Dana (Refund Policy)',
            html: `<h4>PENTING — Kebijakan Pengembalian Dana Layanan LexLaw.</h4>
            <p>Di <strong>LexLaw</strong>, kami berkomitmen untuk memberikan layanan Legal Intelligence berbasis AI terbaik bagi instansi, perusahaan, dan profesional hukum di Indonesia.</p>
            <h4>1. Jaminan Kepuasan 7 Hari (7-Day Money-Back Guarantee)</h4>
            <p>Setiap pelanggan baru paket berlangganan berhak atas jaminan pengembalian dana penuh (100% refund) dalam kurun waktu <strong>7 (tujuh) hari kalender</strong> sejak tanggal transaksi pertama.</p>
            <h4>2. Ketentuan Pengajuan Refund</h4>
            <p>Pengajuan harus dikirimkan melalui email resmi ke <strong>support@lexlaw.arktech.id</strong> atau via WhatsApp ke <strong>0812-9741-4115</strong> dengan melampirkan bukti pembayaran dan alasan pembatalan.</p>
            <h4>3. Pengecualian (Non-Refundable)</h4>
            <ul>
                <li>Permintaan refund yang diajukan setelah melewati batas waktu 7 hari sejak transaksi pertama tidak dapat diproses.</li>
                <li>Langganan yang diperpanjang secara otomatis (renewal) tidak termasuk dalam garansi 7 hari, kecuali terdapat kendala sistem yang sah dari pihak kami.</li>
                <li>Akun yang terbukti melanggar Syarat & Ketentuan Layanan tidak berhak atas pengembalian dana.</li>
            </ul>
            <h4>4. Waktu Proses Pengembalian</h4>
            <p>Dana yang disetujui untuk di-refund akan dikembalikan melalui metode pembayaran asal atau transfer bank dalam waktu <strong>3 sampai 14 hari kerja</strong>.</p>`
        }
    };

    function openModal(type) {
        var data = MODALS[type];
        if(!data) return;
        document.getElementById('modalTitle').innerHTML = data.title;
        document.getElementById('modalBody').innerHTML = data.html;
        document.getElementById('modalOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') closeModal();
    });

    (function(){
        var btn=document.getElementById('hamburger'),menu=document.getElementById('mobileMenu');
        btn.addEventListener('click',function(){menu.classList.toggle('open');btn.textContent=menu.classList.contains('open')?'✕':'☰'});
        window.closeMenu=function(){menu.classList.remove('open');btn.textContent='☰'};
        document.querySelectorAll('.faq-item .faq-q').forEach(function(q){
            q.addEventListener('click',function(){
                var item=this.parentElement,wasOpen=item.classList.contains('open');
                document.querySelectorAll('.faq-item').forEach(function(i){i.classList.remove('open')});
                if(!wasOpen) item.classList.add('open');
            });
        });
        var obs=new IntersectionObserver(function(entries){
            entries.forEach(function(e){if(e.isIntersecting) e.target.classList.add('in')});
        },{threshold:.12});
        document.querySelectorAll('.reveal').forEach(function(el){obs.observe(el)});
        var first=document.querySelector('.faq-item'); if(first) first.classList.add('open');
    })();
    </script>
</body>
</html>