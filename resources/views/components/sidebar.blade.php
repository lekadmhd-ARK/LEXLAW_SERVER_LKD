@props(['active' => 'dashboard'])

<div class="sidebar" style="width:260px;background:var(--bg);min-height:100vh;padding:24px;border-right:1px solid var(--line);position:sticky;top:0">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px">
        <span style="font-size:20px;font-weight:600">⚖️ LEXLAW v2</span>
    </div>

    <!-- Search -->
    <form action="{{ route('search') }}" method="GET" style="margin-bottom:16px">
        <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);opacity:.5">🔍</span>
            <input type="search" name="q" placeholder="Cari workspace..." style="width:100%;padding-left:32px">
        </div>
    </form>

    @php
    $trialCompany = auth()->user()?->company;
    $trialActive = $trialCompany && $trialCompany->subscription_status === 'trialing';
    $trialDaysLeft = $trialActive && $trialCompany->trial_ends_at
        ? max(1, (int) ceil(now()->diffInDays($trialCompany->trial_ends_at)))
        : 0;
    @endphp
    @if($trialActive)
    <div style="padding:12px 14px;border-radius:10px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.35);margin-bottom:16px;font-size:12px;line-height:1.5">
        🔄 <b>Masa Trial</b><br>
        <span style="color:var(--muted)">Sisa <b style="color:#f59e0b">{{ $trialDaysLeft }} hari</b> ·
        berakhir {{ $trialCompany->trial_ends_at->format('d M Y') }}</span><br>
        <a href="{{ route('billing') }}" style="color:#f59e0b;font-weight:600">Pilih paket & berlangganan →</a>
    </div>
    @endif

    <nav style="display:flex;flex-direction:column;gap:4px">
        <!-- Main Navigation -->
        <a href="/dashboard" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">📊 Dashboard</a>
        <a href="/regulations" class="nav-link {{ $active === 'regulations' ? 'active' : '' }}">⚖️ Regulations</a>
        <a href="/legal-glossary" class="nav-link {{ $active === 'glossary' ? 'active' : '' }}">◇ Glossary</a>
        <a href="/consolidations" class="nav-link {{ $active === 'consolidations' ? 'active' : '' }}">◎ Consolidations</a>
        <a href="/team-workspaces" class="nav-link {{ $active === 'workspaces' ? 'active' : '' }}">▣ Workspaces</a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- Case Law -->
        <a href="/putusans" class="nav-link {{ $active === 'putusans' ? 'active' : '' }}">📜 Putusan</a>

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

        <!-- SaaS Layer -->
        <a href="/reports" class="nav-link {{ $active === 'reports' ? 'active' : '' }}">📊 Reports</a>
        <a href="/security" class="nav-link {{ $active === 'security' ? 'active' : '' }}">🛡️ Security</a>
        <a href="/settings/branding" class="nav-link {{ $active === 'branding' ? 'active' : '' }}">🎨 Branding</a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- Notifications -->
        <a href="{{ route('notifications.index') }}" class="nav-link {{ $active === 'notifications' ? 'active' : '' }}">
            🔔 Notifications
            @if(auth()->check() && auth()->user()->unreadNotifications->count())
            <span style="float:right;background:var(--err);color:#fff;border-radius:99px;padding:1px 8px;font-size:11px">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
            @endif
        </a>

        <div style="height:1px;background:var(--line);margin:12px 0"></div>

        <!-- Bantuan -->
        <a href="/support" class="nav-link {{ $active === 'support' ? 'active' : '' }}">🛟 Hubungi Support</a>

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
