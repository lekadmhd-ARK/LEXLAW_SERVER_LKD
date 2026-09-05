<x-layouts.base>
@section('title', $title ?? 'LEXLAW v2')
<div class="app-layout">
    <x-sidebar />
    <div class="main-content">
        <x-topbar />
        <main class="content">
            {{ $slot }}
        </main>
    </div>
</div>

<!-- Dark/Light Toggle Button -->
<div class="theme-toggle">
    <button id="themeBtn" title="Toggle dark/light mode">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"/>
            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
    </button>
</div>

<!-- Theme Toggle Script -->
<script>
(function(){
    const KEY = 'lexlaw-theme';
    const html = document.documentElement;
    const saved = localStorage.getItem(KEY);
    const initial = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    html.setAttribute('data-theme', initial);
    html.className = initial;
    document.getElementById('themeBtn').addEventListener('click', function(){
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        html.className = next;
        localStorage.setItem(KEY, next);
    });
})();
</script>
</x-layouts.base>