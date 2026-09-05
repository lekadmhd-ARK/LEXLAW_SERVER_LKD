<x-layouts.base title="Contract Reviewer">
    <div class="cr-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        {{-- Header Section --}}
        <div class="cr-header" style="margin-bottom: 32px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 24px;">⚖️</span>
                <h1 style="font-size: 24px; font-weight: 700; color: var(--text); margin: 0;">Analisis Kontrak AI</h1>
            </div>
            <p style="color: var(--muted); font-size: 15px; max-width: 600px; line-height: 1.5;">
                Evaluasi risiko hukum, temukan klausul berbahaya, dan dapatkan rekomendasi perbaikan instan menggunakan teknologi kecerdasan buatan.
            </p>
        </div>

        <form action="{{ route('ai.contract-review') }}" method="POST" enctype="multipart/form-data" id="contractForm">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; align-items: start;">
                
                {{-- Left Column: Main Input --}}
                <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--line); background: var(--bg2); border-radius: 12px;">
                    <div style="padding: 16px 20px; border-bottom: 1px solid var(--line); background: rgba(var(--accent-rgb), 0.05);">
                        <h3 style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">1. Input Dokumen</h3>
                    </div>
                    <div style="padding: 24px;">
                        <label class="label" style="display: block; margin-bottom: 10px; font-weight: 600; color: var(--text);">Teks Kontrak</label>
                        <textarea name="contract_text" id="contractText" 
                            placeholder="Tempelkan isi kontrak Anda di sini (Minimal 50 karakter)..."
                            style="width: 100%; min-height: 350px; padding: 16px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; color: var(--text); font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; outline: none; transition: border-color 0.2s;"
                            required></textarea>
                        
                        <div style="margin-top: 20px; padding: 20px; border: 2px dashed var(--line); border-radius: 8px; text-align: center; background: var(--bg); transition: all 0.2s;" id="dropzone">
                            <input type="file" name="contract_file" id="fileInput" accept=".pdf,.docx,.txt" style="display: none;">
                            <div style="cursor: pointer;" onclick="document.getElementById('fileInput').click()">
                                <span style="font-size: 24px; display: block; margin-bottom: 8px;">📁</span>
                                <span style="font-size: 14px; color: var(--text); font-weight: 500;">Klik untuk unggah file</span>
                                <span style="font-size: 12px; color: var(--muted); display: block; margin-top: 4px;">PDF, DOCX, atau TXT (Maks 10MB)</span>
                            </div>
                            <div id="fileInfo" style="display: none; margin-top: 12px; font-size: 13px; color: var(--ok); font-weight: 500;"></div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Settings & Actions --}}
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    {{-- Settings Card --}}
                    <div class="card" style="padding: 0; border: 1px solid var(--line); background: var(--bg2); border-radius: 12px;">
                        <div style="padding: 16px 20px; border-bottom: 1px solid var(--line);">
                            <h3 style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text);">2. Pengaturan</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div style="margin-bottom: 20px;">
                                <label class="label" style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--text);">Jenis Kontrak</label>
                                <select name="contract_type" style="width: 100%; padding: 10px 12px; background: var(--bg); border: 1px solid var(--line); border-radius: 6px; color: var(--text); font-size: 14px; outline: none;">
                                    <option value="umum">Perjanjian Umum</option>
                                    <option value="jual_beli">Jual Beli</option>
                                    <option value="sewa_menyewa">Sewa Menyewa</option>
                                    <option value="kontrak_kerja">Kontrak Kerja / HR</option>
                                    <option value="kerjasama">Kemitraan / Kerjasama</option>
                                    <option value="nda">Kerahasiaan (NDA)</option>
                                </select>
                            </div>
                            
                            <div style="padding: 12px; background: var(--accent-bg); border-radius: 8px; border: 1px solid var(--accent);">
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <span style="font-size: 16px;">💡</span>
                                    <p style="font-size: 12px; color: var(--text); line-height: 1.4; margin: 0;">
                                        Gunakan model <strong>ARK</strong> untuk analisis hukum Indonesia yang lebih akurat dan mendalam.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Card --}}
                    <div class="card" style="padding: 20px; border: 1px solid var(--line); background: var(--bg2); border-radius: 12px;">
                        <button type="submit" id="analyzeBtn"
                            style="width: 100%; padding: 14px; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: transform 0.1s, opacity 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span>Mulai Analisis AI</span>
                        </button>
                        <p style="text-align: center; font-size: 11px; color: var(--muted); margin-top: 12px;">
                            Estimasi waktu proses: 10-30 detik tergantung panjang teks.
                        </p>
                    </div>
                </div>
            </div>
        </form>

        {{-- Result Section (Hidden by default) --}}
        <div id="resultSection" style="display: none; margin-top: 32px;">
            <div class="card" style="padding: 0; border: 1px solid var(--line); background: var(--bg2); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div style="padding: 16px 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; background: rgba(34, 197, 94, 0.05);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: var(--ok); font-size: 20px;">✅</span>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--text); margin: 0;">Hasil Analisis Profesional</h3>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-secondary btn-sm" id="copyResult" style="padding: 6px 12px; font-size: 12px;">📋 Salin</button>
                    </div>
                </div>
                <div style="padding: 32px; color: var(--text); line-height: 1.8; font-size: 15px;" id="analysisOutput">
                    {{-- AI content injected here --}}
                </div>
                <div style="padding: 16px 24px; border-top: 1px solid var(--line); background: var(--bg); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--muted);" id="resultUid">UID: ---</span>
                    <span style="font-size: 11px; color: var(--muted);">© 2026 LAWLEX v2 AI System</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card { transition: border-color 0.2s; }
        #dropzone.dragover { border-color: var(--accent); background: var(--accent-bg); }
        .cr-btn:active { transform: scale(0.98); }
        #analysisOutput h1, #analysisOutput h2, #analysisOutput h3 { color: var(--accent); margin-top: 24px; margin-bottom: 12px; }
        #analysisOutput p { margin-bottom: 16px; }
        #analysisOutput ul, #analysisOutput ol { margin-bottom: 16px; padding-left: 20px; }
        #analysisOutput li { margin-bottom: 8px; }
        #analysisOutput strong { color: var(--text); font-weight: 700; }
        
        /* Dark/Light mode overrides for specific elements if needed */
        [data-theme="light"] .card { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const dropzone = document.getElementById('dropzone');

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileInfo.textContent = `📄 ${e.target.files[0].name} (${(e.target.files[0].size / 1024 / 1024).toFixed(2)}MB)`;
                fileInfo.style.display = 'block';
            }
        });

        // Form Submission via AJAX to show results on the same page nicely
        document.getElementById('contractForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('analyzeBtn');
            const resultSection = document.getElementById('resultSection');
            const output = document.getElementById('analysisOutput');
            const uidText = document.getElementById('resultUid');
            
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menganalisis Kontrak...`;
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch(e.target.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    output.innerHTML = formatMarkdown(data.answer);
                    uidText.textContent = `UID: ${data.uid}`;
                    resultSection.style.display = 'block';
                    resultSection.scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan sistem.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi ke server.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `Mulai Analisis AI`;
            }
        });

        function formatMarkdown(text) {
            // Simple markdown renderer for result presentation
            return text
                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                .replace(/\*(.*)\*/gim, '<em>$1</em>')
                .replace(/^\- (.*$)/gim, '<li>$1</li>')
                .replace(/\n/gim, '<br>');
        }

        document.getElementById('copyResult').addEventListener('click', function() {
            const content = document.getElementById('analysisOutput').innerText;
            navigator.clipboard.writeText(content).then(() => {
                const originalText = this.innerText;
                this.innerText = '✅ Tersalin';
                setTimeout(() => this.innerText = originalText, 2000);
            });
        });
    </script>
</x-layouts.base>