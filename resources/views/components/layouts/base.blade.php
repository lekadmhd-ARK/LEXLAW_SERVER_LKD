@props(['title' => 'LEXLAW v2'])
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - LEXLAW v2</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function(){ var t = localStorage.getItem('lawlex-theme'); if(t) document.documentElement.setAttribute('data-theme', t); })();
    </script>
    <style>
        /* ========== THEME ========== */
        :root,[data-theme="dark"]{--bg:#0a0c10;--bg2:#11141a;--text:#e4e7eb;--muted:#787c85;--accent:#5e6ad2;--accent-rgb:94,106,210;--accent-bg:rgba(94,106,210,.15);--line:#303444;--ok:#22c55e;--err:#ef4444;--warn:#f59e0b;--sidebar-w:264px;--sidebar-w-mini:76px;--radius:12px}
        [data-theme="light"]{--bg:#fff;--bg2:#f5f6f8;--text:#111827;--muted:#6b7280;--accent:#5e6ad2;--accent-rgb:94,106,210;--accent-bg:rgba(94,106,210,.12);--line:#e5e7eb;--ok:#16a34a;--err:#dc2626;--warn:#d97706}

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{font-family:'Inter',system-ui,-apple-system,sans-serif}
        body{background:var(--bg);color:var(--text);min-height:100vh;transition:background .2s,color .2s}
        a{color:inherit;text-decoration:none}

        /* ========== LAYOUT ========== */
        .layout{display:flex;min-height:100vh}
        .sidebar{width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--bg2);border-right:1px solid var(--line);display:flex;flex-direction:column;height:100vh;position:fixed;top:0;left:0;z-index:100;transition:width .25s ease,min-width .25s ease,transform .25s ease}
        .main{flex:1;margin-left:var(--sidebar-w);padding:24px 32px;transition:margin-left .25s ease;min-height:100vh;max-width:100%}

        /* ========== SIDEBAR HEADER ========== */
        .sb-header{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--line);height:58px;gap:8px}
        .sb-logo{font-size:17px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;letter-spacing:-.2px}
        .sb-logo .grad{background:linear-gradient(135deg,var(--accent),#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .sb-toggle{width:30px;height:30px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .15s}
        .sb-toggle:hover{background:var(--accent);border-color:var(--accent);color:#fff}

        /* ========== NAV ========== */
        .sb-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:12px 10px;scrollbar-width:thin;scrollbar-color:var(--line) transparent}
        .sb-nav::-webkit-scrollbar{width:4px}
        .sb-nav::-webkit-scrollbar-thumb{background:var(--line);border-radius:4px}
        .sb-nav a{position:relative;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--muted);font-size:13px;font-weight:500;transition:all .15s;white-space:nowrap;background:transparent;border:1px solid transparent}
        .sb-nav a:hover{background:var(--accent-bg);color:var(--text);border-color:rgba(94,106,210,.3)}
        .sb-nav a.active{background:linear-gradient(135deg,var(--accent),#6d5ae6);color:#fff;box-shadow:0 4px 14px rgba(94,106,210,.35)}
        .sb-nav .ic{width:22px;text-align:center;font-size:15px;flex-shrink:0;display:inline-block}
        .sb-nav .txt{overflow:hidden;transition:opacity .2s;flex:1}
        .sb-divider{height:1px;background:var(--line);margin:10px 12px}

        /* ========== TOOLTIP ========== */
        .sb-nav a[data-tip]::after{
            content:attr(data-tip);
            position:absolute;left:calc(100% + 10px);top:50%;transform:translateY(-50%) translateX(-4px);
            background:#1a1d24;color:#fff;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:500;
            white-space:nowrap;opacity:0;visibility:hidden;pointer-events:none;
            box-shadow:0 6px 20px rgba(0,0,0,.45);border:1px solid rgba(94,106,210,.35);
            transition:opacity .15s,transform .15s,visibility .15s;z-index:200}
        .sb-nav a[data-tip]::before{
            content:'';position:absolute;left:calc(100% + 4px);top:50%;transform:translateY(-50%);
            border:5px solid transparent;border-right-color:#1a1d24;opacity:0;visibility:hidden;transition:opacity .15s,visibility .15s;z-index:200}
        /* tooltip hanya tampil saat collapsed (icon-only) */
        .sidebar.collapsed .sb-nav a[data-tip]:hover::after,
        .sidebar.collapsed .sb-nav a[data-tip]:hover::before{opacity:1;visibility:visible}
        .sidebar.collapsed .sb-nav a[data-tip]:hover::after{transform:translateY(-50%) translateX(0)}

        /* ========== SIDEBAR FOOTER ========== */
        .sb-footer{padding:12px 10px;border-top:1px solid var(--line)}
        .sb-footer .theme-btn{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--muted);font-size:13px;cursor:pointer;border:none;background:transparent;width:100%;transition:all .15s}
        .sb-footer .theme-btn:hover{background:var(--accent-bg);color:var(--text)}
        .sb-footer .copyright{padding:12px 12px 2px;font-size:10px;color:var(--muted);text-align:center;transition:opacity .2s}

        /* ========== COLLAPSED STATE ========== */
        .sidebar.collapsed{width:var(--sidebar-w-mini);min-width:var(--sidebar-w-mini)}
        .sidebar.collapsed~.main{margin-left:var(--sidebar-w-mini)}
        .sidebar.collapsed .sb-nav{padding:12px 8px}
        .sidebar.collapsed .sb-nav a{justify-content:center;padding:11px 0}
        .sidebar.collapsed .sb-nav .txt{opacity:0;width:0}
        .sidebar.collapsed .sb-footer .theme-btn span:last-child{opacity:0;width:0}
        .sidebar.collapsed .sb-footer .copyright{display:none}
        .sidebar.collapsed .sb-footer .theme-btn{justify-content:center}
        .sidebar.collapsed .sb-logo{display:none}
        .sidebar.collapsed .sb-header{justify-content:center;padding:16px 0}

        /* ========== CONTENT ========== */
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .page-title{font-size:24px;font-weight:700;letter-spacing:-.3px}
        .page-desc{color:var(--muted);font-size:13px;margin-top:4px}
        .eyebrow{font-size:11px;font-weight:700;color:var(--accent);margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px}
        .card{background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:20px;transition:border-color .15s,transform .15s}
        .card:hover{border-color:rgba(94,106,210,.35)}
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .stat{border-left:3px solid var(--accent)}
        .stat .num{font-size:28px;font-weight:700;color:var(--accent)}
        .stat .label{font-size:12px;font-weight:600;color:var(--text);margin-top:4px}
        .stat .hint{font-size:11px;color:var(--muted);margin-top:2px}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:all .15s}
        .btn-primary{background:var(--accent);color:#fff}
        .btn-primary:hover{opacity:.9;box-shadow:0 4px 12px rgba(94,106,210,.35)}
        .btn-secondary{background:transparent;color:var(--text);border:1px solid var(--line)}
        .btn-secondary:hover{border-color:var(--accent);color:var(--accent)}
        .label{display:block;font-size:13px;font-weight:500;color:var(--text);margin-bottom:6px}
        input,textarea,select{background:var(--bg2);color:var(--text);border:1px solid var(--line);border-radius:8px;padding:8px 12px;font-size:13px;transition:border-color .15s;width:100%}
        input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-bg)}
        .table{width:100%;border-collapse:collapse}
        .table th{border-bottom:1px solid var(--line);padding:10px 12px;font-size:11px;font-weight:600;color:var(--muted);text-align:left;text-transform:uppercase;letter-spacing:.3px}
        .table td{padding:10px 12px;border-bottom:1px solid var(--line);font-size:13px}

        /* ========== MOBILE ========== */
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:90;opacity:0;transition:opacity .25s}
        .sidebar-overlay.active{display:block;opacity:1}
        .mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:54px;background:var(--bg2);border-bottom:1px solid var(--line);z-index:80;padding:0 16px;align-items:center;gap:12px}
        .mobile-hamburger{width:36px;height:36px;border-radius:10px;border:1px solid var(--line);background:var(--bg);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;transition:all .15s}
        .mobile-hamburger:hover{background:var(--accent-bg)}
        .mobile-logo{font-size:16px;font-weight:700;color:var(--text)}

        @media(max-width:1024px){.grid-4{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){
            .sidebar{transform:translateX(-100%);width:264px;min-width:264px;box-shadow:4px 0 30px rgba(0,0,0,.3)}
            .sidebar.mobile-open{transform:translateX(0)}
            .sidebar.collapsed{width:264px;min-width:264px}
            .sidebar.collapsed .sb-nav a{justify-content:flex-start;padding:10px 12px}
            .sidebar.collapsed .sb-nav .txt{opacity:1;width:auto}
            .sidebar.collapsed .sb-footer .theme-btn span:last-child{opacity:1;width:auto}
            .sidebar.collapsed .sb-footer .copyright{display:block}
            .sidebar.collapsed .sb-footer .theme-btn{justify-content:flex-start}
            .sidebar.collapsed .sb-logo{display:block}
            .sidebar.collapsed .sb-header{justify-content:space-between;padding:16px 18px}
            .sidebar.collapsed .sb-nav a[data-tip]::after,.sidebar.collapsed .sb-nav a[data-tip]::before{display:none}
            .main{margin-left:0;padding:66px 16px 20px;max-width:100%}
            .mobile-header{display:flex}
            .grid-4{grid-template-columns:1fr}
        }
        @media(max-width:480px){
            .main{padding:60px 12px 16px}
            .card{padding:16px}
            .page-title{font-size:18px}
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="mobile-header">
            <button class="mobile-hamburger" id="mobileHamburger">☰</button>
            <span class="mobile-logo">⚖️ LEXLAW v2</span>
        </div>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="sidebar" id="sidebar">
            <div class="sb-header">
                <span class="sb-logo"><span class="grad">⚖️ LEXLAW</span> v2</span>
                <button class="sb-toggle" id="sbToggle" title="Sembunyikan menu" aria-label="Toggle sidebar">→</button>
            </div>
            <nav class="sb-nav">
                <a href="/dashboard" data-tip="Dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><span class="ic">📊</span><span class="txt">Dashboard</span></a>
                <a href="/regulations" data-tip="Regulations" class="{{ request()->is('regulations*') ? 'active' : '' }}"><span class="ic">⚖️</span><span class="txt">Regulations</span></a>
                <a href="/legal-glossary" data-tip="Glossary" class="{{ request()->is('legal-glossary*') ? 'active' : '' }}"><span class="ic">◇</span><span class="txt">Glossary</span></a>
                <a href="/consolidations" data-tip="Consolidations" class="{{ request()->is('consolidations*') ? 'active' : '' }}"><span class="ic">◎</span><span class="txt">Consolidations</span></a>
                <a href="/team-workspaces" data-tip="Workspaces" class="{{ request()->is('team-workspaces*') ? 'active' : '' }}"><span class="ic">▣</span><span class="txt">Workspaces</span></a>
                <div class="sb-divider"></div>
                <a href="/ai/lex-qna" data-tip="Lex Q&A" class="{{ request()->is('ai/lex-qna*') ? 'active' : '' }}"><span class="ic">✦</span><span class="txt">Lex Q&A</span></a>
                <a href="/ai/draft" data-tip="Draft DOCX" class="{{ request()->is('ai/draft*') ? 'active' : '' }}"><span class="ic">✎</span><span class="txt">Draft DOCX</span></a>
                <a href="/ai/contract-review" data-tip="Contract Reviewer" class="{{ request()->is('ai/contract-review*') ? 'active' : '' }}"><span class="ic">📄</span><span class="txt">Contract Reviewer</span></a>
                <a href="/ai/validity" data-tip="Validity Checker" class="{{ request()->is('ai/validity*') ? 'active' : '' }}"><span class="ic">✓</span><span class="txt">Validity Checker</span></a>
                <div class="sb-divider"></div>
                <a href="/billing" data-tip="Billing" class="{{ request()->is('billing*') ? 'active' : '' }}"><span class="ic">◆</span><span class="txt">Billing</span></a>
                <a href="/password-change" data-tip="Password" class="{{ request()->is('password-change') ? 'active' : '' }}"><span class="ic">🔐</span><span class="txt">Password</span></a>
                <div class="sb-divider"></div>
                <a href="/disclaimer" data-tip="Disclaimer" class="{{ request()->is('disclaimer') ? 'active' : '' }}"><span class="ic">🛡️</span><span class="txt">Disclaimer</span></a>
                <a href="/terms-of-service" data-tip="Syarat & Ketentuan" class="{{ request()->is('terms-of-service') ? 'active' : '' }}"><span class="ic">📜</span><span class="txt">Syarat & Ketentuan</span></a>
                <div class="sb-divider"></div>
                <a href="/logout" data-tip="Logout" style="color:var(--err)"><span class="ic">↳</span><span class="txt">Logout</span></a>
            </nav>
            <div class="sb-footer">
                <button class="theme-btn" id="themeToggle">
                    <span class="ic" id="themeIcon">☀️</span>
                    <span id="themeLabel">Mode Light</span>
                </button>
                <div class="copyright">© 2026 LEXLAW v2</div>
            </div>
        </div>

        <main class="main">
            {{ $slot }}
            @yield('content')
        </main>

        {{-- ===== SaaS FOOTER ===== --}}
        <footer class="saas-footer">
            <div class="saas-footer-inner">
                <div class="saas-footer-grid">
                    {{-- Brand --}}
                    <div class="saas-footer-brand">
                        <div class="saas-footer-logo">⚖️ LEXLAW v2</div>
                        <p class="saas-footer-tagline">Legal Intelligence & SaaS Platform — AI-powered hukum Indonesia.</p>
                    </div>
                    {{-- FAQ --}}
                    <div class="saas-footer-col">
                        <h4 class="saas-footer-title">❓ FAQ</h4>
                        <div class="saas-faq-item">
                            <button class="saas-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.classList.toggle('open')">
                                <span>Apa itu LEXLAW?</span><span class="saas-faq-arrow">▾</span>
                            </button>
                            <div class="saas-faq-a"><p>LEXLAW adalah platform SaaS berbasis AI yang membantu Anda menganalisis kontrak, mencari regulasi, membuat draft dokumen hukum, dan memeriksa validitas sitasi hukum — semuanya dalam satu dashboard.</p></div>
                        </div>
                        <div class="saas-faq-item">
                            <button class="saas-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.classList.toggle('open')">
                                <span>Apakah data saya aman?</span><span class="saas-faq-arrow">▾</span>
                            </button>
                            <div class="saas-faq-a"><p>Ya. Data kontrak Anda tidak disimpan di server kami — hanya diproses sekali saat analisis. Semua data dienkripsi dan sesuai Kebijakan Privasi kami.</p></div>
                        </div>
                        <div class="saas-faq-item">
                            <button class="saas-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.classList.toggle('open')">
                                <span>Berapa lama waktu analisis?</span><span class="saas-faq-arrow">▾</span>
                            </button>
                            <div class="saas-faq-a"><p>Analisis kontrak membutuhkan waktu 10–30 detik tergantung panjang teks. Draft dokumen sekitar 15–45 detik.</p></div>
                        </div>
                        <div class="saas-faq-item">
                            <button class="saas-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.classList.toggle('open')">
                                <span>Format file apa yang didukung?</span><span class="saas-faq-arrow">▾</span>
                            </button>
                            <div class="saas-faq-a"><p>Contract Reviewer mendukung PDF, DOCX, dan TXT (maks 10MB). Output bisa diunduh sebagai PDF atau Word.</p></div>
                        </div>
                        <div class="saas-faq-item">
                            <button class="saas-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.classList.toggle('open')">
                                <span>Apakah AI menggantikan pengacara?</span><span class="saas-faq-arrow">▾</span>
                            </button>
                            <div class="saas-faq-a"><p>Tidak. LEXLAW adalah alat bantu referensi — bukan nasihat hukum profesional. Selalu verifikasi dengan pengacara berlisensi.</p></div>
                        </div>
                    </div>
                    {{-- Links --}}
                    <div class="saas-footer-col">
                        <h4 class="saas-footer-title">🔗 Tautan</h4>
                        <a href="/disclaimer" class="saas-footer-link">🛡️ Disclaimer & Penafian</a>
                        <a href="/terms-of-service" class="saas-footer-link">📜 Syarat & Ketentuan</a>
                        <a href="/refund-policy" class="saas-footer-link">💰 Kebijakan Refund</a>
                        <a href="/dashboard" class="saas-footer-link">📊 Dashboard</a>
                    </div>
                    {{-- Refund Policy --}}
                    <div class="saas-footer-col">
                        <h4 class="saas-footer-title">💰 Kebijakan Refund</h4>
                        <p class="saas-footer-text">LEXLAW memberikan jaminan kepuasan 7 hari sejak pendaftaran. Jika layanan tidak sesuai ekspektasi, Anda berhak mengajukan refund penuh dalam periode tersebut.</p>
                        <p class="saas-footer-text">Setelah 7 hari, refund tidak dapat diproses kecuali terdapat kendala teknis yang terbukti berasal dari pihak LEXLAW. Pengajuan refund dilakukan via email ke support@lexlaw.arktech.id.</p>
                        <a href="/refund-policy" class="saas-footer-link" style="margin-top:8px">→ Lihat detail lengkap</a>
                    </div>
                </div>
                <div class="saas-footer-bottom">
                    <span>© 2026 LEXLAW v2 — Legal Intelligence & SaaS Platform. All rights reserved.</span>
                    <span class="saas-footer-legal">Data regulasi bersumber dari dokumen resmi pemerintah Indonesia (public domain). Informasi bersifat referensi.</span>
                </div>
            </div>
        </footer>
    </div>

    <script>
    (function(){
        // Theme
        var themeIcon=document.getElementById('themeIcon'),themeLabel=document.getElementById('themeLabel');
        function applyTheme(t){themeIcon.textContent=t==='light'?'🌙':'☀️';themeLabel.textContent=t==='light'?'Mode Dark':'Mode Light'}
        document.getElementById('themeToggle').addEventListener('click',function(){
            var cur=document.documentElement.getAttribute('data-theme');
            var next=cur==='dark'?'light':'dark';
            document.documentElement.setAttribute('data-theme',next);
            localStorage.setItem('lawlex-theme',next);applyTheme(next);
        });
        applyTheme(localStorage.getItem('lawlex-theme')||'dark');

        // Sidebar toggle (desktop)
        var sidebar=document.getElementById('sidebar'),sbToggle=document.getElementById('sbToggle');
        var collapsed=localStorage.getItem('sb-collapsed')==='true';
        function applyCollapse(v){
            collapsed=v;
            if(window.innerWidth>768){sidebar.classList.toggle('collapsed',collapsed);sbToggle.textContent=collapsed?'←':'→';sbToggle.title=collapsed?'Tampilkan menu':'Sembunyikan menu'}
            localStorage.setItem('sb-collapsed',collapsed);
        }
        applyCollapse(collapsed);
        sbToggle.addEventListener('click',function(){applyCollapse(!collapsed)});

        // Mobile
        var overlay=document.getElementById('sidebarOverlay');
        function openMobile(){sidebar.classList.add('mobile-open');overlay.classList.add('active')}
        function closeMobile(){sidebar.classList.remove('mobile-open');overlay.classList.remove('active')}
        document.getElementById('mobileHamburger').addEventListener('click',function(){sidebar.classList.contains('mobile-open')?closeMobile():openMobile()});
        overlay.addEventListener('click',closeMobile);
        sidebar.querySelectorAll('.sb-nav a').forEach(function(a){a.addEventListener('click',function(){if(window.innerWidth<=768)closeMobile()})});

        // Resize
        window.addEventListener('resize',function(){
            if(window.innerWidth>768){closeMobile();applyCollapse(collapsed)}
            else{sidebar.classList.remove('collapsed');sbToggle.textContent='→'}
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>