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
                <p class="cr-sub">Analisis risiko, identifikasi klausul bermasalah, checklist hukum, dan rekomendasi perbaikan kontrak.</p>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="cr-steps">
            <div class="cr-step active" data-step="1"><span class="cr-step-num">1</span><span class="cr-step-label">Input</span></div>
            <div class="cr-step-line"></div>
            <div class="cr-step" data-step="2"><span class="cr-step-num">2</span><span class="cr-step-label">Analisis</span></div>
            <div class="cr-step-line"></div>
            <div class="cr-step" data-step="3"><span class="cr-step-num">3</span><span class="cr-step-label">Hasil</span></div>
        </div>

        <!-- Grid: Kiri = Form, Kanan = Tips -->
        <div class="cr-grid">
            <div class="cr-main">
                <!-- Form Card -->
                <div class="cr-card">
                    <div class="cr-card-header">📄 Masukkan Dokumen Kontrak</div>
                    <form id="cr-form" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="cr-field">
                            <label class="cr-label" for="contract_text">Teks Kontrak <span class="cr-optional">(atau upload file di bawah)</span></label>
                            <div class="cr-input-note">Pilih salah satu: <strong>tempel teks</strong> di kolom ini <strong>ATAU</strong> upload file PDF/DOCX/TXT di bawah. Minimal salah satu wajib diisi.</div>
                            <textarea class="cr-input" id="contract_text" name="contract_text" rows="12" placeholder="Tempel teks kontrak di sini (pasal demi pasal)..."></textarea>
                            <div class="cr-field-footer">
                                <span class="cr-count" id="cr-count">0 / 50.000 karakter</span>
                                <span class="cr-hint">Min 50 karakter jika diisi</span>
                            </div>
                        </div>
                        <div class="cr-field">
                            <label class="cr-label">Atau Upload File <span class="cr-optional">(PDF / DOCX / TXT)</span></label>
                            <div class="cr-dropzone" id="cr-dropzone" role="button" tabindex="0">
                                <input type="file" id="contract_file" name="contract_file" accept=".pdf,.docx,.txt" class="cr-fileinput" aria-describedby="cr-file-hint">
                                <svg class="cr-drop-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <p class="cr-drop-main">Tarik file ke sini atau <strong>klik untuk memilih</strong></p>
                                <p class="cr-drop-sub" id="cr-file-hint">PDF, DOCX, TXT — Maks 10 MB</p>
                                <div class="cr-drop-preview" id="cr-drop-preview" hidden></div>
                            </div>
                        </div>
                        <div class="cr-actions">
                            <button type="submit" id="cr-submit" class="cr-btn cr-btn-primary" disabled>
                                <span class="cr-btn-text">🔍 Analisis Kontrak</span>
                                <span class="cr-spinner" hidden></span>
                            </button>
                            <button type="button" id="cr-reset" class="cr-btn cr-btn-outline">🗑️ Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Result Card (hidden) -->
                <div class="cr-card cr-card-result" id="cr-result" hidden>
                    <div class="cr-card-header cr-result-header">
                        <span>⚖️ Hasil Analisis Hukum</span>
                        <span class="cr-uid"><span class="cr-uid-label">UID</span> <span class="cr-uid-value" id="cr-uid-value">—</span></span>
                    </div>
                    <div class="cr-result-meta">
                        <span class="cr-timestamp" id="cr-timestamp">—</span>
                        <div class="cr-meta-btns">
                            <button type="button" id="cr-copy" class="cr-btn cr-btn-sm cr-btn-outline">📋 Salin</button>
                            <button type="button" id="cr-download-pdf" class="cr-btn cr-btn-sm cr-btn-outline">📥 PDF</button>
                            <button type="button" id="cr-download-docx" class="cr-btn cr-btn-sm cr-btn-outline">📥 Word</button>
                        </div>
                    </div>
                    <div class="cr-result-body" id="cr-result-body">
                        <div class="cr-result-placeholder">⏳ Menunggu hasil analisis...</div>
                    </div>
                    <div class="cr-disclaimer">
                        ⚠️ <strong>Disclaimer:</strong> Analisis ini dihasilkan AI untuk referensi awal dan <strong>bukan nasihat hukum resmi</strong>. UID: <code id="cr-disclaimer-uid">—</code>
                    </div>
                </div>
            </div>

            <!-- Sidebar Tips -->
            <div class="cr-side">
                <div class="cr-card cr-card-tips">
                    <div class="cr-card-header">💡 Panduan Penggunaan</div>
                    <ul class="cr-tips-list">
                        <li><strong>Privasi Terjaga</strong> — Dokumen tidak disimpan di server; otomatis dihapus setelah analisis.</li>
                        <li><strong>AI Engine (ARK)</strong> — Didukung model hukum bisnis Indonesia untuk mendeteksi celah hukum.</li>
                        <li><strong>Checklist Hukum</strong> — Menganalisis kesesuaian dengan KUH Perdata & UU terkait.</li>
                        <li><strong>Format Output</strong> — Ringkasan, klausul bermasalah, analisis risiko, rekomendasi.</li>
                    </ul>
                    <div class="cr-tips-box">
                        <div class="cr-tips-box-title">⚡ Tips Terbaik</div>
                        <p>Masukkan pasal penalti, denda, terminasi, dan force majeure agar AI mendeteksi klausul merugikan secara akurat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* ========================================
       Variables mapping dari base layout:
       --bg, --bg2, --text, --muted, --accent, --accent-bg, --line, --ok, --err, --warn, --radius
       ======================================== */
    .cr-page { max-width: 100%; padding: 0; }

    /* Hero */
    .cr-hero {
        display: flex; align-items: flex-start; gap: 16px;
        margin-bottom: 24px; padding: 24px;
        background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
        border-radius: var(--radius); color: #fff;
        border: 1px solid rgba(255,255,255,.2);
        box-shadow: 0 8px 30px -10px var(--accent);
    }
    .cr-hero-icon-wrap { flex-shrink: 0; width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.25); }
    .cr-hero-icon { font-size: 28px; }
    .cr-hero-text { flex: 1; min-width: 0; }
    .cr-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.2); margin-bottom: 8px; }
    .cr-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: crPulse 1.5s infinite; }
    @keyframes crPulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    .cr-title { margin: 0 0 4px; font-size: 28px; font-weight: 700; letter-spacing: -.5px; }
    .cr-sub { margin: 0; font-size: 14px; opacity: .9; line-height: 1.5; }

    /* Steps */
    .cr-steps { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 28px; }
    .cr-step { display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; }
    .cr-step-num { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background: var(--bg2); border: 2px solid var(--line); color: var(--muted); transition: all .3s; }
    .cr-step-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
    .cr-step.active .cr-step-num { background: var(--accent); border-color: var(--accent); color: #fff; box-shadow: 0 0 0 4px var(--accent-bg); }
    .cr-step.active .cr-step-label { color: var(--accent); }
    .cr-step-line { flex: 1; max-width: 80px; height: 2px; background: var(--line); }

    /* Grid */
    .cr-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
    @media (max-width: 900px) { .cr-grid { grid-template-columns: 1fr; } }

    /* Card — PAKAI --bg2 DAN --line dari base layout */
    .cr-card {
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 20px;
        transition: border-color .2s;
    }
    .cr-card:hover { border-color: var(--accent); }
    .cr-card-header {
        padding: 16px 20px; font-weight: 700; font-size: 15px;
        color: var(--text); border-bottom: 1px solid var(--line);
    }
    .cr-card-result { margin-top: 0; }
    .cr-card-result .cr-card-header { background: var(--accent-bg); }

    /* Fields */
    .cr-field { padding: 20px; border-bottom: 1px solid var(--line); }
    .cr-field:last-of-type { border-bottom: none; }
    .cr-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--text); }
    .cr-mandatory { color: var(--err); font-size: 10px; }
    .cr-optional { color: var(--muted); font-weight: 400; font-size: 11px; }

    /* Input / Textarea — border terlihat jelas */
    .cr-input {
        width: 100%; min-height: 200px; padding: 14px 16px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; line-height: 1.6;
        color: var(--text); background: var(--bg);
        border: 2px solid var(--line); border-radius: 10px; resize: vertical; outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .cr-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-bg); }
    .cr-input::placeholder { color: var(--muted); }
    .cr-field-footer { display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; color: var(--muted); }
    .cr-input-note { background: var(--accent-bg); border: 1px solid var(--line); border-left: 3px solid var(--accent); border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; font-size: 12px; color: var(--text); line-height: 1.6; }
    .cr-input-note strong { color: var(--accent); }
    .cr-count { font-family: monospace; font-variant-numeric: tabular-nums; }
    .cr-hint { opacity: .7; }

    /* Dropzone — border dashed terlihat jelas */
    .cr-dropzone {
        position: relative; border: 2px dashed var(--line);
        border-radius: 10px; background: var(--bg); transition: all .2s; cursor: pointer;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 28px 16px; text-align: center;
    }
    .cr-dropzone:hover, .cr-dropzone.dragover { border-color: var(--accent); background: var(--accent-bg); }
    .cr-fileinput { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .cr-drop-icon { width: 36px; height: 36px; color: var(--muted); margin-bottom: 8px; }
    .cr-drop-main { margin: 0 0 4px; font-size: 14px; font-weight: 500; color: var(--text); }
    .cr-drop-sub { margin: 0; font-size: 11px; color: var(--muted); }
    .cr-drop-preview { margin-top: 10px; font-size: 12px; color: var(--accent); font-weight: 600; }

    /* Actions */
    .cr-actions { display: flex; gap: 12px; padding: 20px; }
    .cr-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 20px; font-size: 14px; font-weight: 600; border-radius: 10px;
        border: none; cursor: pointer; transition: all .2s;
    }
    .cr-btn:disabled { opacity: .4; cursor: not-allowed; }
    .cr-btn-primary { background: var(--accent); color: #fff; }
    .cr-btn-primary:hover:not(:disabled) { opacity: .9; box-shadow: 0 4px 14px var(--accent-bg); }
    .cr-btn-outline { background: transparent; color: var(--text); border: 1px solid var(--line); }
    .cr-btn-outline:hover { border-color: var(--accent); color: var(--accent); }
    .cr-btn-sm { padding: 6px 12px; font-size: 12px; }
    .cr-spinner { width: 16px; height: 16px; border: 2px solid transparent; border-top-color: currentColor; border-radius: 50%; animation: crSpin .7s linear infinite; }
    @keyframes crSpin { to { transform: rotate(360deg); } }

    /* Tips Card (sidebar) */
    .cr-card-tips .cr-card-header { border-bottom: 1px solid var(--line); }
    .cr-tips-list { margin: 0; padding: 16px 16px 16px 32px; font-size: 13px; line-height: 1.6; color: var(--muted); }
    .cr-tips-list li { margin-bottom: 10px; }
    .cr-tips-list strong { color: var(--text); }
    .cr-tips-box { margin: 0 16px 16px; padding: 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 12px; color: var(--text); line-height: 1.5; background: var(--bg); }
    .cr-tips-box-title { font-weight: 700; color: var(--accent); margin-bottom: 4px; }
    .cr-tips-box p { margin: 0; }

    /* Result */
    .cr-result-meta { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid var(--line); flex-wrap: wrap; gap: 8px; }
    .cr-timestamp { font-size: 12px; color: var(--muted); }
    .cr-meta-btns { display: flex; gap: 8px; }
    .cr-uid { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--bg); border: 1px solid var(--line); font-size: 11px; font-family: monospace; }
    .cr-uid-label { color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .cr-uid-value { color: var(--accent); font-weight: 600; }
    .cr-result-body { padding: 24px; min-height: 160px; font-size: 14px; line-height: 1.75; color: var(--text); }
    .cr-result-placeholder { display: flex; align-items: center; justify-content: center; min-height: 120px; color: var(--muted); font-size: 14px; }
    .cr-disclaimer { padding: 16px 20px; background: var(--accent-bg); border-top: 1px solid var(--line); font-size: 12px; color: var(--muted); display: flex; gap: 8px; line-height: 1.6; }
    .cr-disclaimer code { background: var(--bg); padding: 2px 6px; border-radius: 4px; font-family: monospace; color: var(--accent); }

    /* Responsive */
    @media (max-width: 640px) {
        .cr-hero { flex-direction: column; text-align: center; }
        .cr-hero-icon-wrap { margin: 0 auto; }
        .cr-title { font-size: 22px; }
        .cr-actions { flex-direction: column; }
        .cr-btn { width: 100%; }
        .cr-meta-btns { width: 100%; }
        .cr-result-meta { flex-direction: column; align-items: flex-start; }
    }
    </style>

    <script>
    (() => {
        const form = document.getElementById('cr-form');
        const ta = document.getElementById('contract_text');
        const fileInput = document.getElementById('contract_file');
        const dropzone = document.getElementById('cr-dropzone');
        const preview = document.getElementById('cr-drop-preview');
        const submitBtn = document.getElementById('cr-submit');
        const resetBtn = document.getElementById('cr-reset');
        const countEl = document.getElementById('cr-count');
        const resultCard = document.getElementById('cr-result');
        const resultBody = document.getElementById('cr-result-body');
        const uidValue = document.getElementById('cr-uid-value');
        const disclaimerUid = document.getElementById('cr-disclaimer-uid');
        const timestampEl = document.getElementById('cr-timestamp');
        const copyBtn = document.getElementById('cr-copy');
        const downloadPdfBtn = document.getElementById('cr-download-pdf');
        const downloadDocxBtn = document.getElementById('cr-download-docx');
        const steps = document.querySelectorAll('.cr-step');
        const MAX = 50000, MIN = 50;

        let hasFile = false;
        function updateCount() {
            const len = ta.value.length;
            countEl.textContent = len.toLocaleString() + ' / 50.000';
            countEl.style.color = len > MAX ? 'var(--err)' : len > MAX * .8 ? 'var(--warn)' : '';
            // Submit aktif jika: teks >= MIN ATAU ada file
            const textValid = len >= MIN && len <= MAX;
            submitBtn.disabled = !textValid && !hasFile;
        }
        ta.addEventListener('input', updateCount);
        updateCount();

        dropzone.addEventListener('click', () => fileInput.click());
        ['dragenter','dragover'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.add('dragover'); }));
        ['dragleave','drop'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.remove('dragover'); }));
        dropzone.addEventListener('drop', ev => { if (ev.dataTransfer.files[0]) handleFile(ev.dataTransfer.files[0]); });
        fileInput.addEventListener('change', e => { if (e.target.files[0]) handleFile(e.target.files[0]); });

        function handleFile(f) {
            preview.hidden = false;
            preview.textContent = '📎 ' + f.name + ' (' + (f.size / 1024).toFixed(1) + ' KB)';
            hasFile = true;
            updateCount();
        }

        function removeFile() {
            fileInput.value = '';
            preview.hidden = true;
            preview.textContent = '';
            hasFile = false;
            updateCount();
        }

        resetBtn.addEventListener('click', () => {
            form.reset(); removeFile(); updateCount();
            resultCard.hidden = true; steps.forEach((s,i) => s.classList.toggle('active', i===0));
        });

        form.addEventListener('submit', async e => {
            e.preventDefault(); if (submitBtn.disabled) return;
            steps.forEach((s,i) => s.classList.toggle('active', i <= 1));
            submitBtn.disabled = true;
            submitBtn.querySelector('.cr-btn-text').textContent = '⏳ Menganalisis...';
            submitBtn.querySelector('.cr-spinner').hidden = false;
            try {
                const res = await fetch('{{ route("ai.contract-review") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: new FormData(form)
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Server error');
                uidValue.textContent = data.uid || '—';
                disclaimerUid.textContent = data.uid || '—';
                timestampEl.textContent = data.generated_at || new Date().toLocaleString();
                resultBody.innerHTML = '<div style="white-space:pre-wrap; font-family:inherit;">' + (data.answer || '') + '</div>';
                resultCard.hidden = false;
                resultCard.scrollIntoView({ behavior:'smooth', block:'start' });
                steps.forEach(s => s.classList.add('active'));
            } catch (err) { alert('Gagal: ' + err.message); steps.forEach((s,i) => s.classList.toggle('active', i===0)); }
            finally { submitBtn.disabled = false; submitBtn.querySelector('.cr-btn-text').textContent = '🔍 Analisis Kontrak'; submitBtn.querySelector('.cr-spinner').hidden = true; }
        });

        copyBtn.addEventListener('click', async () => { await navigator.clipboard.writeText(resultBody.innerText); alert('Tersalin!'); });

        function downloadDoc(type) {
            const fd = new FormData();
            fd.append('uid', uidValue.textContent); fd.append('type', type);
            fd.append('content', resultBody.innerText); fd.append('generated_at', timestampEl.textContent);
            fetch('{{ route("ai.contract-review.download") }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content} })
                .then(r => { if(!r.ok) throw new Error(); return r.blob(); })
                .then(blob => { const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='lexlaw-analysis-'+uidValue.textContent+'.'+type; a.click(); });
        }
        downloadPdfBtn.addEventListener('click', () => downloadDoc('pdf'));
        downloadDocxBtn.addEventListener('click', () => downloadDoc('docx'));
    })();
    </script>
</x-layouts.base>
