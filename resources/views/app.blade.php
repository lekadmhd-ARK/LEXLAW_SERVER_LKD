<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEXLAW v2</title>
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
            --accent-bg: rgba(94, 106, 210, 0.15);
            --line: #303444;
            --success: #22c55e;
            --error: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; }
        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; height: auto; }
        
        /* Layout */
        .app-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 24px; width: 100%; }
        .content { max-width: 1200px; margin: 0 auto; }
        
        /* Topbar */
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .brand { font-size: 20px; font-weight: 600; color: var(--text); }
        .brand svg { width: 24px; height: 24px; }
        
        /* Theme toggle */
        .theme-toggle { position: fixed; top: 20px; right: 24px; z-index: 100; }
        .theme-toggle button { background: transparent; border: none; cursor: pointer; padding: 8px; }
        .theme-toggle svg { width: 28px; height: 28px; stroke: var(--text); }
        
        /* Cards */
        .card { background: var(--bg-secondary); border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        
        /* Tables */
        .table { width: 100%; border-collapse: collapse; }
        .table thead th { border-bottom: 1px solid var(--line); padding: 12px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .table tbody tr { border-bottom: 1px solid var(--line); }
        .table tbody td { padding: 12px 16px; }
        .table tbody tr:hover td { background: var(--bg-secondary); }
        
        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-secondary { background: transparent; color: var(--text); border: 1px solid var(--line); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-error { background: var(--error); color: #fff; }
        
        /* Navigation */
        .nav-links a { color: var(--text-muted); padding: 8px 12px; border-radius: 6px; margin-right: 4px; }
        .nav-links a.active { background: var(--accent); color: #fff; }
        
        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: var(--success); color: #fff; }
        .alert-error { background: var(--error); color: #fff; }
        
        /* Search inputs */
        .search-input { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: var(--text); font-size: 13px; }
        
        /* Select */
        .select { padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: var(--text); cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); }
        
        /* Status badges */
        .badge { padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge-success { background: var(--success); color: #fff; }
        .badge-error { background: var(--error); color: #fff; }
        .badge-warning { background: #f59e0b; color: #fff; }
        .badge-info { background: var(--accent-bg); color: var(--accent); }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <div style="width: 260px; background: var(--bg); min-height: 100vh; padding: 24px; border-right: 1px solid var(--line);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px;">
                <div>
                    <span style="font-size: 20px; font-weight: 600; color: var(--text);">LEXLAW v2</span>
                </div>
                <button class="theme-toggle" id="themeBtn" aria-label="Toggle theme">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                </button>
            </div>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="/regulations" class="{{ request()->is('regulations*') ? 'active' : '' }}">Regulasi</a></li>
                    <li><a href="/legal-glossary" class="{{ request()->is('legal-glossary*') ? 'active' : '' }}">Glossary</a></li>
                    <li><a href="/consolidations" class="{{ request()->is('consolidations*') ? 'active' : '' }}">Konsolidasi</a></li>
                    <li><a href="/team-workspaces" class="{{ request()->is('team-workspaces*') ? 'active' : '' }}">Workspaces</a></li>
                    <li><a href="/password-change" class="{{ request()->is('password-change') ? 'active' : '' }}">Password</a></li>
                    <li style="margin-top: 32px;"><a href="/logout" style="color: var(--error);">Logout</a></li>
                </ul>
            </nav>
            
            <div style="margin-top: 40px; font-size: 11px; color: var(--text-muted);">
                <p>© 2026 LEXLAW v2. All rights reserved.</p>
            </div>
        </div>
        
        <main class="main-content">
            @yield('content')
        </main>
    </div>
    
    <script>
        // Theme toggle
        (function() {
            const KEY = 'lexlaw-theme';
            const html = document.documentElement;
            const saved = localStorage.getItem(KEY);
            const initial = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            html.setAttribute('data-theme', initial);
            html.className = initial;
            document.getElementById('themeBtn').addEventListener('click', function() {
                const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                html.className = next;
                localStorage.setItem(KEY, next);
            });
        })();
        
        // Sidebar mobile toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('div[style*="width: 260px"]');
            const mainContent = document.querySelector('.main-content');
            const themeBtn = document.getElementById('themeBtn');
            
            // Close sidebar on link click
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.style.transform = 'translateX(-100%)';
                });
            });
            
            // Theme button
            themeBtn.addEventListener('click', () => {
                const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                html.className = next;
                localStorage.setItem('lexlaw-theme', next);
            });
        });
    </script>
</body>
</html>