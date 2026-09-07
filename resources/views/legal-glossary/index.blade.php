<x-layouts.base title="Glosari Hukum">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">📚 Glosari Hukum</div>
                <h1 class="page-title">Kamus Istilah Hukum</h1>
                <p class="page-desc">{{ $total }} istilah hukum — lengkap, terindeks, dan mudah dicari.</p>
            </div>
            <a href="/legal-glossary/create" class="btn btn-primary">+ Tambah Istilah</a>
        </div></div>

        @if(session('success'))
        <div class="alert" style="padding:12px 16px;border-radius:8px;background:var(--ok);color:#fff;margin-bottom:16px;opacity:.95">{{ session('success') }}</div>
        @endif

        {{-- Smart Search --}}
        <div class="cr-card" style="padding:16px;margin-bottom:20px;position:relative">
            <input type="text" id="smartSearch" placeholder="Cari istilah atau singkatan… (mis: PMH, PTUN, force majeure)" autocomplete="off"
                value="{{ request('q') }}"
                style="width:100%;padding:14px 16px;font-size:15px;border:2px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text)">
            <div id="suggestBox" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);box-shadow:0 12px 32px -12px rgba(0,0,0,.4);max-height:360px;overflow:auto"></div>
        </div>

        {{-- Indeks A-Z --}}
        <div class="az-nav" style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:16px">
            <a href="/legal-glossary" style="padding:6px 10px;border-radius:6px;font-size:13px;font-weight:600;background:var(--bg2);border:1px solid var(--line);color:var(--text);text-decoration:none">Semua</a>
            @foreach(range('A','Z') as $letter)
            <a href="/legal-glossary?letter={{ $letter }}" style="padding:6px 10px;border-radius:6px;font-size:13px;font-weight:600;background:var(--bg2);border:1px solid var(--line);color:var(--text);text-decoration:none">{{ $letter }}</a>
            @endforeach
        </div>

        {{-- Filter Kategori --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
            <a href="/legal-glossary" class="chip {{ !request('category') ? 'chip-active' : '' }}" style="padding:6px 14px;border-radius:99px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none;{{ !request('category') ? 'background:var(--accent);color:#fff;border-color:var(--accent);' : '' }}">Semua</a>
            @foreach($categories as $cat)
            <a href="/legal-glossary?category={{ urlencode($cat) }}" class="chip" style="padding:6px 14px;border-radius:99px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none;{{ request('category') === $cat ? 'background:var(--accent);color:#fff;border-color:var(--accent);' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>

        {{-- Term Cards --}}
        @if($items->count())
        <div class="term-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
            @foreach($items as $g)
            <a href="/legal-glossary/{{ $g->id }}" class="term-card" style="display:block;padding:18px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg2);color:var(--text);text-decoration:none;transition:transform .15s, border-color .15s">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px">
                    <h3 style="margin:0;font-size:16px;font-weight:700;line-height:1.3">{{ $g->term }}</h3>
                    @if($g->kategori_hukum)
                    <span style="flex-shrink:0;padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent);white-space:nowrap">{{ $g->kategori_hukum }}</span>
                    @endif
                </div>
                @if($g->singkatan)
                <div style="font-size:12px;color:var(--muted);margin-bottom:6px">{{ $g->singkatan }}</div>
                @endif
                <p style="margin:0;font-size:13px;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">{{ $g->definisi_singkat ?? \Illuminate\Support\Str::limit($g->penjelasan_lengkap, 140) }}</p>
            </a>
            @endforeach
        </div>
        @else
        <div style="padding:48px;text-align:center;color:var(--muted);border:1px dashed var(--line);border-radius:var(--radius)">
            Tidak ada istilah yang cocok.
        </div>
        @endif

        @if($items->hasPages())
        <div style="margin-top:24px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            {{-- Previous --}}
            @if($items->onFirstPage())
            <span style="padding:8px 14px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--muted);opacity:.5">‹ Prev</span>
            @else
            <a href="{{ $items->previousPageUrl() }}" style="padding:8px 14px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none">‹ Prev</a>
            @endif

            {{-- Page numbers (window) --}}
            @php
                $cp = $items->currentPage();
                $lp = $items->lastPage();
                $start = max(1, $cp - 2);
                $end = min($lp, $cp + 2);
            @endphp
            @if($start > 1)
            <a href="{{ $items->url(1) }}" style="padding:8px 12px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none">1</a>
            @if($start > 2)<span style="color:var(--muted);font-size:13px">…</span>@endif
            @endif
            @for($page = $start; $page <= $end; $page++)
                @if($page == $cp)
                <span style="padding:8px 14px;border-radius:8px;font-size:13px;background:var(--accent);color:#fff;border:1px solid var(--accent);font-weight:600">{{ $page }}</span>
                @else
                <a href="{{ $items->url($page) }}" style="padding:8px 14px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none">{{ $page }}</a>
                @endif
            @endfor
            @if($end < $lp)
            @if($end < $lp - 1)<span style="color:var(--muted);font-size:13px">…</span>@endif
            <a href="{{ $items->url($lp) }}" style="padding:8px 12px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none">{{ $lp }}</a>
            @endif

            {{-- Next --}}
            @if($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}" style="padding:8px 14px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none">Next ›</a>
            @else
            <span style="padding:8px 14px;border-radius:8px;font-size:13px;border:1px solid var(--line);background:var(--bg2);color:var(--muted);opacity:.5">Next ›</span>
            @endif
        </div>
        @endif
    </div>

    <style>
    .cr-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding:24px;background:linear-gradient(135deg,var(--accent) 0%,#8b5cf6 100%);border-radius:var(--radius);color:#fff;border:1px solid color-mix(in srgb,var(--accent) 40%,transparent);box-shadow:0 8px 30px -10px var(--accent)}
    .cr-hero .page-head{flex:1;display:block;margin:0;padding:0;border:0;background:transparent}
    .cr-hero .eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.2);margin-bottom:8px;color:#fff}
    .cr-hero .page-title{margin:0 0 4px;font-size:28px;font-weight:700;letter-spacing:-.5px;color:#fff}
    .cr-hero .page-desc{margin:0;font-size:14px;opacity:.9;line-height:1.5;color:#fff}
    .cr-hero .btn-primary{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;margin-top:12px}
    .cr-hero .btn-primary:hover{background:rgba(255,255,255,.3)}
    .term-card:hover{transform:translateY(-2px);border-color:var(--accent)}
    </style>

    <script>
    // Smart search auto-complete
    const input = document.getElementById('smartSearch');
    const box = document.getElementById('suggestBox');
    let debounce;
    input.addEventListener('input', function() {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 2) { box.style.display = 'none'; return; }
        debounce = setTimeout(() => {
            fetch('/legal-glossary/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.results || !data.results.length) {
                    box.innerHTML = '<div style="padding:14px;color:var(--muted);font-size:13px">Tidak ada hasil.</div>';
                } else {
                    box.innerHTML = data.results.map(item => `
                        <a href="/legal-glossary/${item.id}" style="display:block;padding:12px 16px;border-bottom:1px solid var(--line);color:var(--text);text-decoration:none">
                            <div style="font-weight:600;font-size:14px">${item.term} ${item.kategori ? `<span style="font-size:11px;color:var(--accent);margin-left:6px">${item.kategori}</span>` : ''}</div>
                            ${item.singkatan ? `<div style="font-size:12px;color:var(--muted)">${item.singkatan}</div>` : ''}
                            <div style="font-size:12px;color:var(--muted);margin-top:2px">${(item.definisi || '').slice(0, 90)}</div>
                        </a>
                    `).join('');
                }
                box.style.display = 'block';
            });
        }, 250);
    });
    // Enter -> full search
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            window.location = '/legal-glossary?q=' + encodeURIComponent(this.value.trim());
        }
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.cr-card')) box.style.display = 'none';
    });
    </script>
</x-layouts.base>
