<div class="topbar">
    <span class="topbar-title">{{ $title ?? 'LEXLAW v2' }}</span>
    <div class="topbar-actions">
        <a href="/security" title="Keamanan">🔐</a>
        <a href="{{ route('logout') }}" title="Logout">⏻</a>
    </div>
</div>

<style>
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
    border-bottom: 1px solid var(--border, #e5e7eb);
    background: var(--card, #fff);
    color: var(--text, #111827);
}
.topbar-title { font-weight: 600; font-size: 15px; }
.topbar-actions { display: flex; gap: 8px; }
.topbar-actions a { font-size: 16px; text-decoration: none; opacity: .8; }
.topbar-actions a:hover { opacity: 1; }
</style>