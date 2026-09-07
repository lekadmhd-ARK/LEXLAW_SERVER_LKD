<x-layouts.base title="{{ $legalGlossary->term }}">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">📖 {{ $legalGlossary->kategori_hukum ?? 'Glosari Hukum' }}</div>
                <h1 class="page-title">{{ $legalGlossary->term }}</h1>
                @if($legalGlossary->singkatan)
                <p class="page-desc">{{ $legalGlossary->singkatan }}</p>
                @endif
            </div>
            <a href="/legal-glossary" class="btn btn-primary">← Kembali</a>
        </div></div>

        <div style="max-width:840px">
            {{-- Definisi singkat --}}
            @if($legalGlossary->definisi_singkat)
            <div style="padding:20px;border-left:4px solid var(--accent);background:var(--bg2);border-radius:var(--radius);margin-bottom:20px">
                <p style="margin:0;font-size:18px;font-weight:600;line-height:1.5">{{ $legalGlossary->definisi_singkat }}</p>
            </div>
            @endif

            {{-- Definisi lengkap --}}
            <div style="padding:20px;border:1px solid var(--line);border-radius:var(--radius);margin-bottom:20px">
                <h2 style="margin:0 0 12px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Penjelasan Lengkap</h2>
                <div style="font-size:15px;line-height:1.7;color:var(--text);white-space:pre-line">{{ $legalGlossary->penjelasan_lengkap }}</div>
            </div>

            {{-- Contoh implementasi --}}
            @if($legalGlossary->contoh_implementasi)
            <div style="padding:20px;border:1px solid var(--line);border-radius:var(--radius);margin-bottom:20px;background:var(--bg2)">
                <h2 style="margin:0 0 12px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">💼 Contoh Penerapan</h2>
                <p style="margin:0;font-size:14px;line-height:1.6;color:var(--text)">{{ $legalGlossary->contoh_implementasi }}</p>
            </div>
            @endif

            {{-- Dasar hukum terkait --}}
            @if($legalGlossary->dasar_hukum_terkait && count((array)$legalGlossary->dasar_hukum_terkait))
            <div style="padding:20px;border:1px solid var(--line);border-radius:var(--radius);margin-bottom:20px">
                <h2 style="margin:0 0 12px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">⚖️ Dasar Hukum Terkait</h2>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @foreach($legalGlossary->dasar_hukum_terkait as $dh)
                    <span style="padding:6px 12px;border-radius:8px;font-size:13px;background:var(--accent-bg);color:var(--accent);border:1px solid var(--line)">
                        {{ is_array($dh) ? ($dh['number'] ?? ($dh['type'] ?? '')) : $dh }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div style="display:flex;gap:12px;margin-bottom:32px">
                <button id="bookmarkBtn" class="btn btn-secondary" style="padding:10px 18px">🔖 Simpan Istilah</button>
                <a href="/legal-glossary/{{ $legalGlossary->id }}/edit" class="btn btn-secondary" style="padding:10px 18px;text-decoration:none">✏️ Edit</a>
            </div>
        </div>

        {{-- Istilah terkait --}}
        @if($related->count())
        <h2 style="margin:32px 0 16px;font-size:16px;font-weight:700">Istilah Terkait</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px">
            @foreach($related as $r)
            <a href="/legal-glossary/{{ $r->id }}" style="padding:14px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg2);color:var(--text);text-decoration:none">
                <div style="font-weight:600;font-size:14px;margin-bottom:4px">{{ $r->term }}</div>
                <div style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $r->definisi_singkat ?? '' }}</div>
            </a>
            @endforeach
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
    </style>

    <script>
    // Bookmark via localStorage (simpan istilah pribadi)
    document.getElementById('bookmarkBtn').addEventListener('click', function() {
        const key = 'glossary_bookmarks';
        let bookmarks = JSON.parse(localStorage.getItem(key) || '[]');
        const id = {{ $legalGlossary->id }};
        if (bookmarks.includes(id)) {
            bookmarks = bookmarks.filter(x => x !== id);
            this.textContent = '🔖 Simpan Istilah';
        } else {
            bookmarks.push(id);
            this.textContent = '✓ Tersimpan';
        }
        localStorage.setItem(key, JSON.stringify(bookmarks));
    });
    </script>
</x-layouts.base>
