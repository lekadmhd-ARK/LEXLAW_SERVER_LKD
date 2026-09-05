<x-layouts.base title="Contract Reviewer">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">⚖ AI Legal Tools</div>
                <h1 class="page-title">Contract Reviewer</h1>
            </div>
            <div class="page-meta">
                <span class="badge bg-secondary">v2.0</span>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Review Kontrak dengan AI</h5>
                <p class="text-muted small mb-3">Tempelkan teks kontrak di bawah ini, unggah file PDF/DOCX/TXT, lalu klik "Analisis Kontrak". Sistem akan memberikan analisis risiko dan rekomendasi.</p>
                <form id="contractForm" action="{{ route('ai.contract-review') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="contract_text" id="contractText" class="form-control" rows="8" placeholder="Tempelkan teks kontrak di sini..." required style="background:rgba(255,255,255,0.05);border:1px solid var(--line);border-radius:8px;color:var(--text);resize:vertical;"></textarea>
                        <div class="invalid-feedback d-block mt-1">
                            <small>Silakan masukkan teks kontrak (minimal 50 karakter).</small>
                        </div>
                        <div class="mt-2">
                            <label for="fileUpload" class="form-label small text-muted mb-1">Atau unggah file:</label>
                            <input class="form-control" type="file" id="fileUpload" name="contract_file" accept=".pdf,.docx,.txt,.doc">
                            <small class="text-muted">Format: PDF, DOCX, TXT (maks 5MB)</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary px-4 py-2" id="analyzeBtn" style="background:var(--accent);border:1px solid var(--accent);border-radius:8px;">
                            <i class="oi oi-magic"></i> Analisis Kontrak
                        </button>
                        <div class="d-flex align-items-center" id="rateLimitIndicator" style="display:none;">
                            <span class="spinner-border spinner-border-sm text-warning me-2"></span>
                            <small class="text-muted">Menghindar dari rate limit... <span id="rateLimitCountdown">429</span>s</small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="resultContainer" style="display:none;" class="mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Hasil Analisis Kontrak</h5>
                        <span class="badge bg-secondary" id="resultUid">LEX-CR-...</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Dianalisis pada: <span id="resultTimestamp">...</span></small>
                    </div>
                    <div class="result-content mb-4" id="aiResult">
                        <p class="text-muted">Memuat hasil analisis...</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copyBtn">
                            <i class="oi oi-clipboard"></i> Salin Hasil
                        </button>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">UID Dokumen: <span id="uidText" class="font-monospace">LEX-CR-...</span></small>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.getElementById('analyzeBtn').addEventListener('click', function(e) {
                const btn = this;
                const form = document.getElementById('contractForm');
                const text = document.getElementById('contractText').value.trim();
                const file = document.getElementById('fileUpload').files[0];

                if (text.length < 50 && !file) {
                    e.preventDefault();
                    document.querySelector('#contractText + .invalid-feedback').style.display = 'block';
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menganalisis...';
                document.getElementById('rateLimitIndicator').style.display = 'flex';
                let timeLeft = 429;
                const countdown = setInterval(() => {
                    timeLeft--;
                    document.getElementById('rateLimitCountdown').textContent = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        document.getElementById('rateLimitIndicator').style.display = 'none';
                    }
                }, 1000);

                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="oi oi-magic"></i> Analisis Kontrak';
                    clearInterval(countdown);
                    document.getElementById('rateLimitIndicator').style.display = 'none';
                }, 10000);
            });

            // Copy to clipboard
            document.getElementById('copyBtn').addEventListener('click', async function() {
                try {
                    await navigator.clipboard.writeText(document.getElementById('aiResult').textContent || document.getElementById('aiResult').innerText);
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="oi oi-check"></i> Tersalin!';
                    this.classList.add('btn-success');
                    this.classList.remove('btn-outline-secondary');
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-secondary');
                    }, 2000);
                } catch (err) {
                    alert('Gagal menyalin: ' + err.message);
                }
            });
        </script>
    </div>
</x-layouts.base>