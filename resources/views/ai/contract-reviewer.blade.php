<x-layouts.base title="Contract Reviewer">
    <div class="cr-page">
        <!-- Hero Header -->
        <div class="cr-hero">
            <div class="cr-hero-icon-wrap">
                <span class="cr-hero-icon" aria-hidden="true">⚖️</span>
            </div>
            <div class="cr-hero-text">
                <div class="cr-badge"><span class="dot"></span> AI Legal Tools v2.0</div>
                <h1 class="cr-title">Contract Reviewer</h1>
                <p class="cr-sub">Paste teks atau upload file kontrak (PDF/DOCX/TXT). AI menganalisis risiko, klausul bermasalah, checklist hukum, dan rekomendasi perbaikan.</p>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="cr-steps" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3" aria-label="Langkah analisis kontrak">
            <div class="cr-step active" data-step="1"><span class="cr-step-num">1</span><span class="cr-step-label">Input</span></div>
            <div class="cr-step-line"></div>
            <div class="cr-step" data-step="2"><span class="cr-step-num">2</span><span class="cr-step-label">Analisis</span></div>
            <div class="cr-step-line"></div>
            <div class="cr-step" data-step="3"><span class="cr-step-num">3</span><span class="cr-step-label">Hasil</span></div>
        </div>

        <!-- Info Banner -->
        <div class="cr-info" role="region" aria-label="Informasi analisis">
            <div class="cr-info-item"><span class="cr-info-icon">🔒</span> Data kontrak **tidak disimpan** di server — hanya diproses sekali saat analisis.</div>
            <div class="cr-info-item"><span class="cr-info-icon">🤖</span> Model: **ARK** (via gateway lokal 9Router) — respons non-streaming, aman.</div>
            <div class="cr-info-item"><span class="cr-info-icon">⚡</span> Minimal **50 karakter**, maksimal **50.000 karakter** per analisis.</div>
            <div class="cr-info-item"><span class="cr-info-icon">📋</span> Hasil bisa disalin, diunduh PDF/Word (server-side, tidak tersimpan).</div>
        </div>

        <!-- Main Card -->
        <section class="cr-card" aria-labelledby="cr-input-heading">
            <header class="cr-card-head">
                <h2 id="cr-input-heading" class="cr-card-title">Masukkan Kontrak</h2>
            </header>

            <form id="cr-form" enctype="multipart/form-data" novalidate>
                {{ csrf_field() }}

                <!-- Textarea -->
                <div class="cr-field">
                    <label for="contract_text" class="cr-label">Teks Kontrak <span class="cr-required" aria-hidden="true">*</span></label>
                    <div class="cr-ta-wrap">
                        <textarea
                            id="contract_text"
                            name="contract_text"
                            class="cr-textarea"
                            placeholder="Tempel teks kontrak di sini...&#10;&#10;Contoh:&#10;SURAT PERJANJIAN KERJASAMA&#10;&#10;Pada hari ini, [Tanggal]...&#10;&#10;Para Pihak:&#10;1. PT. ABC (\"Pihak Pertama\")&#10;2. PT. XYZ (\"Pihak Kedua\")&#10;&#10;Sepakat untuk bekerja sama..."
                            rows="14"
                            aria-describedby="cr-char-count cr-text-hint"
                            required></textarea>
                        <div class="cr-ta-footer">
                            <span id="cr-char-count" class="cr-char-count">0 / 50.000 karakter</span>
                            <span id="cr-text-hint" class="cr-hint">Min 50 karakter • Deteksi otomatis PDF/DOCX jika upload</span>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="cr-field">
                    <label class="cr-label">Atau Upload File <span class="cr-optional">(opsional)</span></label>
                    <div class="cr-drop" id="cr-drop" role="button" tabindex="0" aria-label="Drop area untuk upload file kontrak">
                        <input type="file" id="contract_file" name="contract_file" accept=".pdf,.docx,.txt" class="cr-file-input" aria-describedby="cr-file-hint">
                        <div class="cr-drop-inner">
                            <svg class="cr-drop-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="cr-drop-main">Seret file di sini atau <strong>klik untuk pilih</strong></p>
                            <p class="cr-drop-sub" id="cr-file-hint">PDF, DOCX, TXT • Maks 10 MB</p>
                        </div>
                        <div class="cr-file-preview" id="cr-file-preview" hidden></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="cr-actions">
                    <button type="submit" id="cr-submit" class="cr-btn cr-btn-primary" disabled>
                        <span class="cr-btn-icon" aria-hidden="true">🔍</span>
                        <span class="cr-btn-text">Analisis Kontrak</span>
                        <span class="cr-btn-spinner" hidden aria-hidden="true"></span>
                    </button>
                    <button type="button" id="cr-reset" class="cr-btn cr-btn-ghost">
                        <span class="cr-btn-icon" aria-hidden="true">🗑️</span>
                        <span class="cr-btn-text">Bersihkan</span>
                    </button>
                </div>

                <!-- Rate limit indicator -->
                <div class="cr-rate" id="cr-rate" hidden aria-live="polite"></div>
            </form>
        </section>

        <!-- Result Card (hidden until success) -->
        <section class="cr-card cr-result-card" id="cr-result" hidden aria-labelledby="cr-result-heading">
            <header class="cr-card-head cr-result-head">
                <div class="cr-result-title-wrap">
                    <h2 id="cr-result-heading" class="cr-card-title">Hasil Analisis</h2>
                    <span class="cr-uid" id="cr-uid"><span class="cr-uid-label">UID</span> <span class="cr-uid-value">—</span></span>
                </div>
                <div class="cr-result-meta">
                    <time class="cr-timestamp" id="cr-timestamp" datetime="">—</time>
                    <div class="cr-btn-group">
                        <button type="button" id="cr-copy" class="cr-btn cr-btn-sm cr-btn-outline" aria-label="Salin hasil analisis">
                        <button type="button" id="cr-download-pdf" class="cr-btn cr-btn-sm cr-btn-outline" aria-label="Unduh PDF"><span>PDF</span></button>
                        <button type="button" id="cr-download-docx" class="cr-btn cr-btn-sm cr-btn-outline" aria-label="Unduh Word"><span>Word</span></button>
                            <svg class="cr-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin</span>
                        </button>
                        <button type="button" id="cr-download-pdf" class="cr-btn cr-btn-sm cr-btn-outline" aria-label="Unduh PDF">
                            <svg class="cr-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>PDF</span>
                        </button>
                        <button type="button" id="cr-download-docx" class="cr-btn cr-btn-sm cr-btn-outline" aria-label="Unduh Word">
                            <svg class="cr-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                            <span>Word</span>
                        </button>
                    </div>
                </div>
            </header>
            <div class="cr-result-body" id="cr-result-body">
                <div class="cr-placeholder" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <p>Menunggu analisis...</p>
                </div>
            </div>
            <footer class="cr-disclaimer" role="contentinfo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <strong>Disclaimer:</strong> Analisis ini dihasilkan AI untuk referensi awal dan <strong>bukan nasihat hukum resmi</strong>. Konsultasikan dengan pengacara berkualifikasi untuk keputusan hukum yang mengikat. UID: <code id="cr-disclaimer-uid">—</code>
                </div>
            </footer>
        </section>

        <!-- Empty State Card (when no result yet) -->
        <section class="cr-card cr-empty" id="cr-empty" aria-hidden="true">
            <div class="cr-empty-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <h3>Belum Ada Analisis</h3>
                <p>Masukkan teks kontrak atau upload file, lalu klik <strong>Analisis Kontrak</strong> untuk memulai.</p>
            </div>
        </section>
    </div>

    <!-- Styles (scoped to this page) -->
    <style>
    /* CSS Variables from base layout are available: --bg, --card, --card-border, --text, --muted, --primary, --primary-hover, --accent, --radius, --shadow, --transition */
    .cr-page { max-width: 960px; margin: 0 auto; padding: 24px 16px; }

    /* Hero */
    .cr-hero { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: var(--radius); color: white; box-shadow: 0 8px 30px -10px var(--primary); }
    .cr-hero-icon-wrap { flex-shrink: 0; width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,.2); }
    .cr-hero-icon { font-size: 28px; }
    .cr-hero-text { flex: 1; min-width: 0; }
    .cr-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.2); margin-bottom: 8px; backdrop-filter: blur(4px); }
    .cr-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: cr-pulse 1.5s infinite; }
    @keyframes cr-pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    .cr-title { margin: 0 0 4px; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
    .cr-sub { margin: 0; font-size: 14px; opacity: .9; line-height: 1.5; }

    /* Steps */
    .cr-steps { display: flex; align-items: center; justify-content: center; gap: 0; margin: 24px 0 16px; position: relative; }
    .cr-step { display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; }
    .cr-step-num { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background: var(--card); border: 2px solid var(--card-border); color: var(--muted); transition: all var(--transition); }
    .cr-step-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
    .cr-step.active .cr-step-num { background: var(--primary); border-color: var(--primary); color: white; box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 20%, transparent); }
    .cr-step.active .cr-step-label { color: var(--primary); }
    .cr-step[data-step="2"].active .cr-step-num, .cr-step[data-step="3"].active .cr-step-num { background: var(--accent); border-color: var(--accent); }
    .cr-step-line { flex: 1; max-width: 80px; height: 2px; background: var(--card-border); margin: 16px 0 0; position: relative; }
    .cr-step-line::after { content:""; position:absolute; top:0; left:0; height:2px; background:var(--primary); width:0%; transition:width .4s ease; }
    .cr-step.active ~ .cr-step-line::after { width: 100%; }

    /* Info Banner */
    .cr-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .cr-info-item { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: color-mix(in srgb, var(--primary) 8%, var(--card)); border: 1px solid color-mix(in srgb, var(--primary) 15%, var(--card-border)); border-radius: 10px; font-size: 13px; line-height: 1.5; }
    .cr-info-icon { flex-shrink: 0; font-size: 14px; margin-top: 1px; }
    .cr-info-item strong { color: var(--text); }

    /* Card */
    .cr-card { background: var(--card); border: 1px solid var(--card-border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .cr-card-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; border-bottom: 1px solid var(--card-border); flex-wrap: wrap; }
    .cr-card-title { margin: 0; font-size: 16px; font-weight: 600; color: var(--text); }
    .cr-result-head { background: linear-gradient(90deg, color-mix(in srgb, var(--primary) 6%, var(--card)), color-mix(in srgb, var(--accent) 6%, var(--card))); }
    .cr-result-title-wrap { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .cr-uid { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--card); border: 1px solid var(--card-border); font-size: 11px; font-family: ui-monospace,SFMono-Regular,Menlo,monospace; }
    .cr-uid-label { text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 600; }
    .cr-uid-value { color: var(--primary); font-weight: 600; }
    .cr-result-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .cr-timestamp { font-size: 12px; color: var(--muted); font-variant-numeric: tabular-nums; }

    /* Fields */
    .cr-field { padding: 20px; border-bottom: 1px solid var(--card-border); }
    .cr-field:last-of-type { border-bottom: none; }
    .cr-label { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 13px; font-weight: 600; color: var(--text); }
    .cr-required { color: #ef4444; font-size: 10px; }
    .cr-optional { color: var(--muted); font-weight: 400; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }

    /* Textarea */
    .cr-ta-wrap { position: relative; }
    .cr-textarea { width: 100%; min-height: 220px; padding: 14px 16px; font-family: ui-monospace,SFMono-Regular,Menlo,monospace; font-size: 13px; line-height: 1.6; color: var(--text); background: var(--bg); border: 2px solid var(--card-border); border-radius: 12px; resize: vertical; transition: border-color var(--transition), box-shadow var(--transition); outline: none; }
    .cr-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent); }
    .cr-textarea::placeholder { color: var(--muted); opacity: .7; }
    .cr-ta-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 11px; color: var(--muted); }
    .cr-char-count { font-variant-numeric: tabular-nums; font-family: ui-monospace,SFMono-Regular,Menlo,monospace; }
    .cr-char-count.warning { color: #f59e0b; }
    .cr-char-count.error { color: #ef4444; }
    .cr-hint { opacity: .7; }

    /* Drop Zone */
    .cr-drop { position: relative; border: 2px dashed var(--card-border); border-radius: 12px; background: var(--bg); transition: all var(--transition); cursor: pointer; }
    .cr-drop:hover, .cr-drop.drag-over { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, var(--bg)); }
    .cr-drop:focus-within { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent); }
    .cr-file-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .cr-drop-inner { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 20px; text-align: center; pointer-events: none; }
    .cr-drop-icon { width: 48px; height: 48px; color: var(--muted); margin-bottom: 12px; transition: color var(--transition); }
    .cr-drop:hover .cr-drop-icon, .cr-drop.drag-over .cr-drop-icon { color: var(--primary); }
    .cr-drop-main { margin: 0 0 6px; font-size: 15px; font-weight: 500; color: var(--text); }
    .cr-drop-sub { margin: 0; font-size: 12px; color: var(--muted); }
    .cr-file-preview { padding: 12px 16px; display: flex; align-items: center; gap: 10px; }
    .cr-file-preview.show { display: flex; }
    .cr-file-preview-icon { width: 36px; height: 36px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
    .cr-file-preview-info { flex: 1; min-width: 0; }
    .cr-file-preview-name { font-weight: 500; font-size: 13px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cr-file-preview-size { font-size: 11px; color: var(--muted); }
    .cr-file-preview-remove { padding: 6px; border-radius: 8px; background: transparent; border: 1px solid var(--card-border); color: var(--muted); cursor: pointer; transition: all var(--transition); }
    .cr-file-preview-remove:hover { background: #ef4444; border-color: #ef4444; color: white; }

    /* Buttons */
    .cr-actions { display: flex; gap: 12px; padding: 20px; flex-wrap: wrap; }
    .cr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; transition: all var(--transition); white-space: nowrap; }
    .cr-btn:disabled { opacity: .5; cursor: not-allowed; }
    .cr-btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 14px -4px var(--primary); }
    .cr-btn-primary:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 6px 20px -6px var(--primary); }
    .cr-btn-ghost { background: transparent; color: var(--text); border: 1px solid var(--card-border); }
    .cr-btn-ghost:hover { background: var(--bg); border-color: var(--primary); color: var(--primary); }
    .cr-btn-outline { background: transparent; color: var(--text); border: 1px solid var(--card-border); padding: 8px 14px; font-size: 12px; }
    .cr-btn-outline:hover { background: var(--bg); border-color: var(--primary); color: var(--primary); }
    .cr-btn-sm { padding: 8px 12px; font-size: 12px; }
    .cr-btn-icon { display: flex; align-items: center; justify-content: center; }
    .cr-btn-icon svg { width: 16px; height: 16px; }
    .cr-btn-spinner { width: 16px; height: 16px; border: 2px solid transparent; border-top-color: currentColor; border-radius: 50%; animation: cr-spin .7s linear infinite; }
    @keyframes cr-spin { to { transform: rotate(360deg); } }

    /* Rate Limit */
    .cr-rate { padding: 0 20px 12px; font-size: 12px; color: #f59e0b; display: flex; align-items: center; gap: 8px; }
    .cr-rate::before { content:"⏳"; font-size: 14px; }

    /* Result Body */
    .cr-result-body { padding: 24px; min-height: 200px; }
    .cr-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 160px; color: var(--muted); text-align: center; gap: 12px; }
    .cr-placeholder svg { width: 48px; height: 48px; opacity: .4; }
    .cr-result-body .prose { color: var(--text); line-height: 1.75; font-size: 14px; }
    .cr-result-body .prose h1, .cr-result-body .prose h2, .cr-result-body .prose h3 { color: var(--text); margin-top: 1.5em; margin-bottom: .5em; font-weight: 700; }
    .cr-result-body .prose h1 { font-size: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: .3em; }
    .cr-result-body .prose h2 { font-size: 1.25rem; }
    .cr-result-body .prose h3 { font-size: 1.1rem; }
    .cr-result-body .prose strong { font-weight: 700; }
    .cr-result-body .prose ul, .cr-result-body .prose ol { padding-left: 1.25rem; margin: .75rem 0; }
    .cr-result-body .prose li { margin: .35rem 0; }
    .cr-result-body .prose code { background: color-mix(in srgb, var(--primary) 10%, var(--bg)); padding: 2px 6px; border-radius: 6px; font-family: ui-monospace,SFMono-Regular,Menlo,monospace; font-size: .9em; }
    .cr-result-body .prose pre { background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 10px; overflow: auto; margin: 1rem 0; font-size: 12px; line-height: 1.6; }
    .cr-result-body .prose pre code { background: transparent; padding: 0; color: inherit; }
    .cr-result-body .prose blockquote { border-left: 3px solid var(--primary); padding-left: 1rem; margin: 1rem 0; color: var(--muted); font-style: italic; }
    .cr-result-body .prose hr { border: none; border-top: 1px solid var(--card-border); margin: 1.5rem 0; }

    /* Disclaimer */
    .cr-disclaimer { display: flex; align-items: flex-start; gap: 10px; padding: 16px 20px; background: color-mix(in srgb, #f59e0b 8%, var(--card)); border-top: 1px solid var(--card-border); font-size: 12px; line-height: 1.6; }
    .cr-disclaimer svg { flex-shrink: 0; width: 18px; height: 18px; color: #f59e0b; margin-top: 2px; }
    .cr-disclaimer code { background: var(--bg); padding: 2px 6px; border-radius: 4px; font-family: ui-monospace,SFMono-Regular,Menlo,monospace; font-size: .95em; color: var(--primary); }

    /* Empty State */
    .cr-empty { background: var(--card); border: 1px dashed var(--card-border); }
    .cr-empty-inner { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 240px; text-align: center; padding: 32px 20px; color: var(--muted); gap: 12px; }
    .cr-empty-inner svg { width: 64px; height: 64px; opacity: .3; }
    .cr-empty-inner h3 { margin: 0; font-size: 18px; font-weight: 600; color: var(--text); }
    .cr-empty-inner p { margin: 0; font-size: 14px; }

    /* Button Group */
    .cr-btn-group { display: flex; gap: 8px; flex-wrap: wrap; }

    /* Responsive */
    @media (max-width: 640px) {
        .cr-hero { flex-direction: column; text-align: center; }
        .cr-hero-icon-wrap { margin: 0 auto; }
        .cr-title { font-size: 22px; }
        .cr-sub { font-size: 13px; }
        .cr-steps { overflow-x: auto; padding-bottom: 8px; }
        .cr-step-line { max-width: 50px; }
        .cr-actions { flex-direction: column; }
        .cr-btn { width: 100%; }
        .cr-btn-group { width: 100%; justify-content: stretch; }
        .cr-btn-group .cr-btn { flex: 1; }
        .cr-result-meta { flex-direction: column; align-items: flex-start; }
    }
    </style>

    <!-- Scripts -->
    <script>
    (() => {
        const form = document.getElementById('cr-form');
        const ta = document.getElementById('contract_text');
        const fileInput = document.getElementById('contract_file');
        const drop = document.getElementById('cr-drop');
        const preview = document.getElementById('cr-file-preview');
        const submitBtn = document.getElementById('cr-submit');
        const resetBtn = document.getElementById('cr-reset');
        const charCount = document.getElementById('cr-char-count');
        const rateEl = document.getElementById('cr-rate');
        const resultCard = document.getElementById('cr-result');
        const emptyCard = document.getElementById('cr-empty');
        const resultBody = document.getElementById('cr-result-body');
        const uidEl = document.getElementById('cr-uid');
        const uidValue = document.getElementById('cr-uid-value');
        const disclaimerUid = document.getElementById('cr-disclaimer-uid');
        const timestampEl = document.getElementById('cr-timestamp');
        const copyBtn = document.getElementById('cr-copy');
        const downloadPdfBtn = document.getElementById('cr-download-pdf');
        const downloadDocxBtn = document.getElementById('cr-download-docx');
        const steps = document.querySelectorAll('.cr-step');

        const MAX_CHARS = 50000;
        const MIN_CHARS = 50;

        // Char counter
        function updateCharCount() {
            const len = ta.value.length;
            charCount.textContent = len.toLocaleString() + ' / 50.000 karakter';
            charCount.classList.remove('warning','error');
            if (len > MAX_CHARS) charCount.classList.add('error');
            else if (len > MAX_CHARS * 0.8) charCount.classList.add('warning');
            submitBtn.disabled = len < MIN_CHARS || len > MAX_CHARS;
        }
        ta.addEventListener('input', updateCharCount);
        updateCharCount();

        // File drop
        ['dragenter','dragover'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('drag-over'); }));
        ['dragleave','drop'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.remove('drag-over'); }));
        drop.addEventListener('drop', ev => {
            const f = ev.dataTransfer.files[0];
            if (f) handleFile(f);
        });
        drop.addEventListener('click', () => fileInput.click());
        drop.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });
        fileInput.addEventListener('change', e => { if (e.target.files[0]) handleFile(e.target.files[0]); });

        function handleFile(file) {
            const validTypes = ['application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','text/plain'];
            if (!validTypes.includes(file.type)) { alert('Format tidak didukung. Gunakan PDF, DOCX, atau TXT.'); return; }
            if (file.size > 10 * 1024 * 1024) { alert('File terlalu besar. Maks 10 MB.'); return; }
            showPreview(file);
            // Could auto-extract text here via FormData to server, but keep simple: user submits form
        }

        function showPreview(file) {
            preview.hidden = false;
            preview.classList.add('show');
            const ext = file.name.split('.').pop().toLowerCase();
            preview.innerHTML = `
                <div class="cr-file-preview-icon">${ext.toUpperCase()}</div>
                <div class="cr-file-preview-info">
                    <div class="cr-file-preview-name">${file.name}</div>
                    <div class="cr-file-preview-size">${(file.size/1024).toFixed(1)} KB</div>
                </div>
                <button type="button" class="cr-file-preview-remove" aria-label="Hapus file">✕</button>
            `;
            preview.querySelector('.cr-file-preview-remove').onclick = () => {
                fileInput.value = '';
                preview.hidden = true;
                preview.classList.remove('show');
                preview.innerHTML = '';
            };
        }

        // Reset
        resetBtn.addEventListener('click', () => {
            form.reset();
            preview.hidden = true; preview.classList.remove('show'); preview.innerHTML = '';
            updateCharCount();
            hideResult();
            setStep(1);
        });

        // Submit
        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (submitBtn.disabled) return;
            setStep(2);
            submitBtn.disabled = true;
            submitBtn.querySelector('.cr-btn-text').textContent = 'Menganalisis...';
            submitBtn.querySelector('.cr-btn-spinner').hidden = false;
            rateEl.hidden = true;

            const fd = new FormData(form);
            try {
                const res = await fetch('{{ route("ai.contract-review") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Server error');
                showResult(data);
            } catch (err) {
                alert('Gagal: ' + err.message);
                setStep(1);
            } finally {
                submitBtn.disabled = false;
                submitBtn.querySelector('.cr-btn-text').textContent = 'Analisis Kontrak';
                submitBtn.querySelector('.cr-btn-spinner').hidden = true;
            }
        });

        function setStep(n) {
            steps.forEach((s, i) => s.classList.toggle('active', i+1 <= n));
        }

        function showResult(data) {
            emptyCard.hidden = true;
            resultCard.hidden = false;
            resultCard.scrollIntoView({behavior:'smooth', block:'start'});

            uidValue.textContent = data.uid || '—';
            disclaimerUid.textContent = data.uid || '—';
            const now = new Date();
            timestampEl.textContent = now.toLocaleString('id-ID', {dateStyle:'full', timeStyle:'short'}) + ' WIB';
            timestampEl.dateTime = now.toISOString();

            // Render markdown-ish
            resultBody.innerHTML = '<div class="prose">' + markdownish(data.answer || data.content || '') + '</div>';
            setStep(3);
        }

        function hideResult() {
            resultCard.hidden = true;
            emptyCard.hidden = false;
            setStep(1);
        }

        // Simple markdown-ish renderer
        function markdownish(txt) {
            return txt
                .replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>')
                .replace(/^### (.*$)/gm, '<h3>$1</h3>')
                .replace(/^## (.*$)/gm, '<h2>$1</h2>')
                .replace(/^# (.*$)/gm, '<h1>$1</h1>')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/`(.+?)`/g, '<code>$1</code>')
                .replace(/^\- (.*$)/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>')
                .replace(/^\d+\. (.*$)/gm, '<li>$1</li>')
                .replace(/\n\n/g, '</p><p>')
                .replace(/^(?!<[huo])/gm, '<p>')
                .replace(/(<p>\s*<\/p>)/g, '')
                .replace(/<p><\/p>/g, '');
        }

        // Copy
        copyBtn.addEventListener('click', async () => {
            const txt = resultBody.innerText.trim();
            if (!txt) return;
            await navigator.clipboard.writeText(txt);
            const orig = copyBtn.innerHTML;
            copyBtn.innerHTML = '<svg class="cr-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Tersalin!</span>';
            copyBtn.classList.add('cr-btn-success');
            setTimeout(() => { copyBtn.innerHTML = orig; copyBtn.classList.remove('cr-btn-success'); }, 2000);
        });

        // Download PDF
        downloadPdfBtn.addEventListener('click', () => downloadDoc('pdf'));
        downloadDocxBtn.addEventListener('click', () => downloadDoc('docx'));

        function downloadDoc(type) {
            const fd = new FormData();
            fd.append('uid', uidValue.textContent);
            fd.append('type', type);
            fd.append('content', resultBody.innerText);
            fd.append('generated_at', timestampEl.textContent);
            fetch('{{ route("ai.contract-review.download") }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content} })
                .then(r => { if(!r.ok) throw new Error('Gagal'); return r.blob(); })
                .then(blob => { const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='lexlaw-contract-analysis-'+uidValue.textContent+'.'+(type==='pdf'?'pdf':'docx'); a.click(); URL.revokeObjectURL(url); })
                .catch(() => alert('Unduh gagal'));
        }
    })();
    </script>
</x-layouts.base>