<x-layouts.base title="Validity Checker">
    <div id="app-root">
        {{-- Theme Toggle --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
            <button id="themeToggle" onclick="toggleTheme()" style="padding:8px 16px;border-radius:6px;border:1px solid #2a2d35;color:#e0e0e0;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px">
                <span id="themeIcon">☀️</span> <span id="themeLabel">Mode Light</span>
            </button>
        </div>

        <div class="page-head">
            <div>
                <div class="eyebrow">🔍 Validity Checker</div>
                <h1 class="page-title">Verifikasi Sitasi Regulasi</h1>
                <p class="page-desc">Masukkan teks hukum untuk mengecek validitas sitasi UU/PP/Perpres</p>
            </div>
            <a href="/dashboard" class="btn btn-secondary">← Kembali</a>
        </div>

        {{-- Input Form --}}
        <div class="card theme-card" style="padding:24px">
            <form action="/ai/validity" method="POST" id="validityForm">
                @csrf
                <div style="margin-bottom:16px">
                    <label class="theme-label" style="display:block;margin-bottom:6px;font-size:13px;font-weight:500">Teks / Paragraf Hukum</label>
                    <textarea name="text" rows="8" required placeholder="Masukkan teks yang mengandung sitasi regulasi..." class="theme-input" style="width:100%;padding:14px 16px;border:1px solid;border-radius:8px;font-size:14px;line-height:1.5;outline:none;resize:vertical"></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px">
                    <button type="reset" class="btn btn-secondary" style="padding:12px 20px">Reset</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="padding:12px 24px;font-weight:600">🔍 Cek Validitas</button>
                </div>
            </form>
        </div>

        {{-- Hasil Pengecekan --}}
        @if(!empty($results) && count($results) > 0)
        <div class="card theme-card" style="margin-top:24px;padding:0;overflow:hidden">
            <div class="theme-border-bottom" style="padding:16px 24px">
                <div class="theme-accent" style="font-size:11px;font-weight:600;text-transform:uppercase">📋 Hasil Pengecekan</div>
                <div class="theme-text" style="font-size:14px;margin-top:2px">Ditemukan {{ count($results) }} sitasi regulasi</div>
            </div>
            <div style="padding:0">
                @foreach($results as $i => $ref)
                <div class="theme-border-bottom" style="padding:16px 24px;{{ $i < count($results)-1 ? '' : 'border-bottom:none!important' }}display:flex;gap:16px;align-items:flex-start">
                    <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;{{ $ref['found'] ? 'background:rgba(34,197,94,0.12);color:#22c55e' : 'background:rgba(239,68,68,0.12);color:#ef4444' }}">
                        {{ $ref['found'] ? '✅' : '❌' }}
                    </div>
                    <div style="flex:1">
                        <div class="theme-text" style="font-size:15px;font-weight:600">{{ $ref['reference'] }}</div>
                        <div class="theme-muted" style="font-size:13px;margin-top:4px">
                            @if($ref['found'])
                                <span style="color:#22c55e">✓ Ditemukan</span>
                                @if(!empty($ref['year'])) — Tahun {{ $ref['year'] }} @endif
                                @if(!empty($ref['category'])) — {{ $ref['category'] }} @endif
                            @else
                                <span style="color:#ef4444">✗ Tidak ditemukan di database</span>
                            @endif
                        </div>
                        @if(!empty($ref['database_match']))
                        <div class="theme-inner" style="margin-top:8px;padding:8px 12px;border-radius:6px;font-size:12px">
                            <strong class="theme-text">Match:</strong> <span class="theme-muted">{{ $ref['database_match'] }}</span>
                        </div>
                        @endif
                        @if(!empty($ref['source_url']))
                        <div style="margin-top:6px">
                            <a href="{{ $ref['source_url'] }}" target="_blank" rel="noopener" class="theme-accent" style="font-size:12px;text-decoration:underline">📖 Buka di situs resmi</a>
                        </div>
                        @endif
                        @if(!empty($ref['ai_analysis']))
                        <div class="theme-muted" style="margin-top:6px;font-size:12px;white-space:pre-wrap;line-height:1.5">{{ $ref['ai_analysis'] }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Analisis AI --}}
        @if(!empty($aiAnalysis))
        <div class="card theme-ai-card" style="margin-top:24px">
            <div style="padding:16px 24px">
                <div class="theme-ai-title" style="font-size:14px;font-weight:600">🤖 Analisis AI tentang Keaktifan</div>
                <div class="theme-text" style="font-size:14px;line-height:1.5;margin-top:8px;white-space:pre-wrap">{{ $aiAnalysis }}</div>
                @if(!empty($results))
                    @foreach($results as $r)
                        @if(!empty($r['source_url']))
                        <div class="theme-muted" style="margin-top:8px;font-size:12px">
                            <span class="theme-accent">Sumber resmi:</span> <a href="{{ $r['source_url'] }}" target="_blank" rel="noopener" class="theme-accent" style="text-decoration:underline">{{ $r['source_url'] }}</a>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
        @endif

        {{-- Ringkasan --}}
        @if(!empty($results))
        <div style="margin-top:16px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
            <div class="card theme-card" style="padding:16px;text-align:center">
                <div style="font-size:24px;font-weight:700;color:#22c55e">{{ count(array_filter($results, fn($r) => $r['found'])) }}</div>
                <div class="theme-muted" style="font-size:12px;margin-top:4px">Valid</div>
            </div>
            <div class="card theme-card" style="padding:16px;text-align:center">
                <div style="font-size:24px;font-weight:700;color:#ef4444">{{ count(array_filter($results, fn($r) => !$r['found'])) }}</div>
                <div class="theme-muted" style="font-size:12px;margin-top:4px">Tidak Ditemukan</div>
            </div>
            <div class="card theme-card" style="padding:16px;text-align:center">
                <div class="theme-accent" style="font-size:24px;font-weight:700">{{ count($results) }}</div>
                <div class="theme-muted" style="font-size:12px;margin-top:4px">Total Sitasi</div>
            </div>
        </div>
        @endif

        @if(!empty($message))
        <div class="card theme-warn-card" style="margin-top:24px">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 24px">
                <div style="width:36px;height:36px;border-radius:8px;background:rgba(234,179,8,0.15);display:flex;align-items:center;justify-content:center;font-size:16px">ℹ️</div>
                <div class="theme-text" style="font-size:14px">{{ $message }}</div>
            </div>
        </div>
        @endif
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        #submitBtn:disabled { opacity:.7; cursor:not-allowed; }
        .spinner { width:14px; height:14px; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; vertical-align:middle; margin-right:6px; }

        /* === DARK MODE (default) === */
        #theme-dark { --bg:#08090a; --bg-card:#0d0f12; --bg-input:#111318; --border:#1e2128; --border-input:#2a2d35; --text:#e0e0e0; --muted:#9ca3af; --accent:#818cf8; }
        #theme-dark .theme-card { background:var(--bg-card); border:1px solid var(--border); color:var(--text); }
        #theme-dark .theme-input { background:var(--bg-input); color:var(--text); border-color:var(--border-input); }
        #theme-dark .theme-label { color:var(--muted); }
        #theme-dark .theme-text { color:var(--text); }
        #theme-dark .theme-muted { color:var(--muted); }
        #theme-dark .theme-accent { color:var(--accent); }
        #theme-dark .theme-inner { background:var(--bg-input); border:1px solid var(--border-input); }
        #theme-dark .theme-border-bottom { border-bottom:1px solid var(--border); }
        #theme-dark .theme-ai-card { background:#0d1a2d; border:1px solid #1e3a5f; }
        #theme-dark .theme-ai-title { color:#60a5fa; }
        #theme-dark .theme-warn-card { background:#1c1305; border:1px solid #854d0e; }
        #theme-dark #themeToggle { background:#1e2128; border-color:#2a2d35; }

        /* === LIGHT MODE === */
        #theme-light { --bg:#ffffff; --bg-card:#f9fafb; --bg-input:#ffffff; --border:#e5e7eb; --border-input:#d1d5db; --text:#111827; --muted:#6b7280; --accent:#5e6ad2; }
        #theme-light .theme-card { background:var(--bg-card); border:1px solid var(--border); color:var(--text); }
        #theme-light .theme-input { background:var(--bg-input); color:var(--text); border-color:var(--border-input); }
        #theme-light .theme-label { color:var(--muted); }
        #theme-light .theme-text { color:var(--text); }
        #theme-light .theme-muted { color:var(--muted); }
        #theme-light .theme-accent { color:var(--accent); }
        #theme-light .theme-inner { background:var(--bg-input); border:1px solid var(--border-input); }
        #theme-light .theme-border-bottom { border-bottom:1px solid var(--border); }
        #theme-light .theme-ai-card { background:#eff6ff; border:1px solid #bfdbfe; }
        #theme-light .theme-ai-title { color:#2563eb; }
        #theme-light .theme-warn-card { background:#fefce8; border:1px solid #ca8a04; }
        #theme-light #themeToggle { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
    </style>

    <script>
        function toggleTheme() {
            var el = document.getElementById('app-root');
            var isLight = el.id === 'theme-dark';
            el.id = isLight ? 'theme-light' : 'theme-dark';
            localStorage.setItem('lawlex-theme', el.id);
            document.getElementById('themeIcon').textContent = isLight ? '🌙' : '☀️';
            document.getElementById('themeLabel').textContent = isLight ? 'Mode Dark' : 'Mode Light';
        }
        (function() {
            var saved = localStorage.getItem('lawlex-theme');
            if (saved === 'theme-light') {
                document.getElementById('app-root').id = 'theme-light';
                document.getElementById('themeIcon').textContent = '🌙';
                document.getElementById('themeLabel').textContent = 'Mode Dark';
            }
        })();
        document.getElementById('validityForm').addEventListener('submit', function(){
            var b = document.getElementById('submitBtn');
            if (b.disabled) return false;
            b.disabled = true;
            b.innerHTML = '<span class="spinner"></span> Menganalisis...';
        });
    </script>
</x-layouts.base>