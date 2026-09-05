@props(['title' => 'LEXLAW v2'])
<!DOCTYPE html>
<html lang="en" class="dark" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - LEXLAW v2</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c10;
            --bg-secondary: #11141a;
            --text: #e4e7eb;
            --text-muted: #787c85;
            --accent: #5e6ad2;
            --accent-bg: rgba(94,106,210,0.15);
            --line: #303444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; transition: background .2s, color .2s; }
        a { color: inherit; text-decoration: none; }
        .app-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 24px; width: 100%; }
        .content { max-width: 1200px; margin: 0 auto; }
        .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .page-title { font-size: 24px; font-weight: 600; }
        .page-desc { color: var(--text-muted); font-size: 13px; margin-top: 4px; }
        .eyebrow { font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 4px; text-transform: uppercase; }
        .card { background: var(--bg-secondary); border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .stat .num { font-size: 28px; font-weight: 700; color: var(--accent); }
        .stat .label { font-size: 13px; font-weight: 600; color: var(--text); margin-top: 4px; }
        .stat .hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { border-bottom: 1px solid var(--line); padding: 10px 12px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-align: left; }
        .table td { padding: 10px 12px; border-bottom: 1px solid var(--line); }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-secondary { background: transparent; color: var(--text); border: 1px solid var(--line); }
        .label { display: block; font-size: 13px; font-weight: 500; color: var(--text); margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <div style="width:260px;background:var(--bg);min-height:100vh;padding:24px;border-right:1px solid var(--line);position:sticky;top:0">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px">
                <span style="font-size:20px;font-weight:600">⚖️ LEXLAW v2</span>
            </div>
            <nav style="display:flex;flex-direction:column;gap:4px">
                <a href="/dashboard" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                <a href="/regulations" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('regulations*') ? 'active' : '' }}">⚖️ Regulations</a>
                <a href="/legal-glossary" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('legal-glossary*') ? 'active' : '' }}">◇ Glossary</a>
                <a href="/consolidations" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('consolidations*') ? 'active' : '' }}">◎ Consolidations</a>
                <a href="/team-workspaces" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('team-workspaces*') ? 'active' : '' }}">▣ Workspaces</a>
                <div style="height:1px;background:var(--line);margin:12px 0"></div>
                <a href="/ai/lex-qna" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('ai/lex-qna*') ? 'active' : '' }}">✦ Lex Q&A</a>
                <a href="/ai/draft" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('ai/draft*') ? 'active' : '' }}">✎ Draft DOCX</a>
                <a href="/ai/validity" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('ai/validity*') ? 'active' : '' }}">✓ Validity Checker</a>
                <div style="height:1px;background:var(--line);margin:12px 0"></div>
                <a href="/billing" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('billing*') ? 'active' : '' }}">◆ Billing / QRIS</a>
                <a href="/password-change" style="padding:10px 12px;border-radius:8px;color:var(--text-muted);font-size:14px" class="{{ request()->is('password-change') ? 'active' : '' }}">🔐 Password</a>
                <div style="height:1px;background:var(--line);margin:12px 0"></div>
                <a href="/logout" style="padding:10px 12px;border-radius:8px;color:#ef4444;font-size:14px">↳ Logout</a>
            </nav>
            <div style="margin-top:40px;font-size:11px;color:var(--text-muted)">
                © 2026 LEXLAW v2
            </div>
        </div>
        <main class="main-content">
            {{ $slot }}
        </main>
    </div>
    <script>
    (function(){
        var KEY='lexlaw-theme',html=document.documentElement,saved=localStorage.getItem(KEY),initial=saved||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
        html.setAttribute('data-theme',initial);html.className=initial;
        document.querySelectorAll('a.active').forEach(function(a){a.style.background='var(--accent)';a.style.color='#fff';});
    })();
    </script>
</body>
</html>