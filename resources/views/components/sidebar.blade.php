@props(['active' => 'dashboard'])

<div class="sidebar" style="width:260px;background:var(--bg);min-height:100vh;padding:24px;border-right:1px solid var(--line);position:sticky;top:0">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px">
        <span style="font-size:20px;font-weight:600">⚖️ LEXLAW v2</span>
    </div>

    <nav style="display:flex;flex-direction:column;gap:4px">
        <!-- Main Navigation -->
        <a href="/dashboard" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">📊 Dashboard</a>
        <a href="/regulations" class="nav-link {{ $active === 'regulations' ? 'active' : '' }}">⚖️ Regulations</a>
        <a href="/legal-glossary" class="nav-link {{ $active === 'glossary' ? 'active' : '' }}">◇ Glossary</a>
        <a href="/consolidations" class="nav-link {{ $active === 'consolidations' ? 'active' : '' }}">◎ Consolidations</a>
        <a href="/team-workspaces" class="nav-link {{ $active === 'workspaces' ? 'active' : '' }}">▣ Workspaces</a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- AI Modules -->
        <a href="/ai/lex-qna" class="nav-link {{ $active === 'lex-qna' ? 'active' : '' }}">✦ Lex Q&A</a>
        <a href="/ai/draft" class="nav-link {{ $active === 'draft' ? 'active' : '' }}">✎ Draft DOCX</a>
        <a href="/ai/validity" class="nav-link {{ $active === 'validity' ? 'active' : '' }}">✓ Validity Checker</a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- Billing -->
        <a href="/billing" class="nav-link {{ $active === 'billing' ? 'active' : '' }}">◆ Billing / QRIS</a>
        <a href="/password-change" class="nav-link {{ $active === 'password' ? 'active' : '' }}">🔐 Password</a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- Logout -->
        <a href="/logout" class="nav-link" style="color:#ef4444">↳ Logout</a>
    </nav>

    <div style="margin-top:40px;font-size:11px;color:var(--text-muted)">
        © 2026 LEXLAW v2
    </div>
</div>

<style>
    .nav-link {
        padding: 10px 12px;
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 14px;
    }
    .nav-link.active {
        background: var(--accent);
        color: #fff;
    }
</style>