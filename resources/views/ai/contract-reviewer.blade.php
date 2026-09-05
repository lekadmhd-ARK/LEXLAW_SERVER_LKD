<x-layouts.base title="Contract Reviewer">
    <div class="cr-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        {{-- Header Section --}}
        <div class="cr-header" style="margin-bottom: 32px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 24px;">⚖️</span>
                <h1 style="font-size: 24px; font-weight: 700; color: var(--text); margin: 0;">Analisis Kontrak AI</h1>
            </div>
            <p style="color: var(--muted); font-size: 15px; max-width: 600px; line-height: 1.5;">
                Evaluasi risiko hukum, temukan klausul berbahaya, dan dapatkan rekomendasi perbaikan instan.
            </p>
        </div>

        {{-- Input Method Selection --}}
        <div class="card" style="padding: 0; border: 1px solid var(--line); background: var(--bg2); border-radius: 12px; margin-bottom: 24px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--line); background: rgba(var(--accent-rgb), 0.05);">
                <h3 style="font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">Pilih Metode Input</h3>
            </div>
            <div style="padding: 20px;">
                <div class="radio-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    {{-- Text Input Option --}}
                    <div class="radio-option" style="padding: 16px; border: 2px solid var(--line); border-radius: 8px; background: var(--bg); transition: all 0.2s; cursor: pointer;" id="opt-text" data-type="text">
                        <div style="font-size: 24px; margin-bottom: 8px;">📝</div>
                        <div>
                            <div style="font-weight: 600; color: var(--text);">Input Teks</div>
                            <div style="font-size: 13px; color: var(--muted);">Tempelkan kontrak langsung</div>
                        </div>
                    </div>
                    {{-- File Upload Option --}}
                    <div class="radio-option" style="padding: 16px; border: 2px solid var(--line); border-radius: 8px; background: var(--bg); transition: all 0.2s; cursor: pointer;" id="opt-file" data-type="file">
                        <div style="font-size: 24px; margin-bottom: 8px;">📁</div>
                        <div>
                            <div style="font-weight: 600; color: var(--text);">Unggah File</div>
                            <div style="font-size: 13px; color: var(--muted);">PDF, DOCX, atau TXT (maks 10MB)</div>
                        </div>
                    </div>
                </div>
                <div id="feedback-area" style="padding: 12px; border-radius: 6px; margin-top: 12px; display: none; font-size: 13px; line-height: 1.5;">
                    <span id="feedback-icon"></span>
                    <span id="feedback-text"></span>
                </div>
            </div>
        </div>

        {{-- Form Container (visible based on selection) --}}
        <form action="{{ route('ai.contract-review') }}" method="POST" enctype="multipart/form-data" id="contractForm">
            @csrf
            
            {{-- Hidden fields --}}
            <input type="hidden" name="contract_type" value="umum">
            
            {{-- Text Input Area --}}
            <div id="text-area" style="display: none; margin-bottom: 24px;">
                <div style="margin-bottom: 16px;">
                    <label class="label" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">Teks Kontrak</label>
                    <textarea name="contract_text" id="contractText"
                        placeholder="Tempelkan isi kontrak Anda di sini (Minimal 50 karakter)..."
                        style="width: 100%; min-height: 350px; padding: 16px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; color: var(--text); font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; outline: none; transition: border-color 0.2s;"
                        required></textarea>
                    <small style="color: var(--muted); font-size: 12px;">Minimal 50 karakter untuk analisis yang akurat</small>
                </div>
            </div>

            {{-- File Upload Area --}}
            <div id="file-area" style="display: none; margin-bottom: 24px;">
                <div style="margin-bottom: 16px;">
                    <label class="label" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">Unggah File Kontrak</label>
                    <div style="position: relative;">
                        <input type="file" name="contract_file" id="contractFile"
                            accept=".pdf,.docx,.txt"
                            style="width: 100%; padding: 12px 16px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; color: var(--text); font-size: 14px; cursor: pointer; outline: none;">
                        <div id="file-info" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; padding: 12px 16px; pointer-events: none; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; color: var(--muted); font-size: 13px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
                            <span>Klik atau seret file di sini</span>
                        </div>
                    </div>
                    <div id="file-name" style="margin-top: 8px; font-size: 13px; color: var(--ok); display: none; font-weight: 500;"></div>
                </div>
                <small style="color: var(--muted); font-size: 12px;">Format: PDF, DOCX, atau TXT (maks 10MB)</small>
            </div>

            {{-- Action Area --}}
            <div style="margin-top: 32px; text-align: center;">
                <button type="submit" id="analyzeBtn"
                    style="padding: 14px 32px; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: transform 0.1s, opacity 0.2s; display: inline-block;"
                    onclick="this.disabled=true; this.innerHTML='<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Menganalisis...';">
                    Mulai Analisis AI
                </button>
            </div>
        </form>
    </div>

    <style>
        .radio-option:hover { border-color: var(--accent); background: var(--accent-bg); }
        .radio-option.selected { border-color: var(--accent); background: var(--accent-box); }
        #feedback-area { display: block; }
        
        /* Dark/Light mode overrides */
        [data-theme="light"] .card { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>

    <script>
        // Initialize variables first
        const optText = document.getElementById('opt-text');
        const optFile = document.getElementById('opt-file');
        const textArea = document.getElementById('text-area');
        const fileArea = document.getElementById('file-area');
        const analyzeBtn = document.getElementById('analyzeBtn');
        const feedbackArea = document.getElementById('feedback-area');
        const feedbackIcon = document.getElementById('feedback-icon');
        const feedbackText = document.getElementById('feedback-text');
        
        // Set default state (text option selected)
        optText.classList.add('selected');
        textArea.style.display = 'block';
        
        // Radio option click handlers
        optText.addEventListener('click', function() {
            selectOption('text');
        });
        
        optFile.addEventListener('click', function() {
            selectOption('file');
        });
        
        function selectOption(type) {
            // Update visual selection
            if (type === 'text') {
                optText.classList.add('selected');
                optFile.classList.remove('selected');
                textArea.style.display = 'block';
                fileArea.style.display = 'none';
                feedbackArea.style.display = 'none';
                analyzeBtn.disabled = false;
                analyzeBtn.innerHTML = 'Mulai Analisis AI';
                document.getElementById('contractText').focus();
            } else {
                optFile.classList.add('selected');
                optText.classList.remove('selected');
                textArea.style.display = 'none';
                fileArea.style.display = 'block';
                analyzeBtn.disabled = false;
                analyzeBtn.innerHTML = 'Mulai Analisis AI';
            }
        }
        
        // File input change handler
        const fileInput = document.getElementById('contractFile');
        const fileName = document.getElementById('file-name');
        
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                
                if (sizeMB > 10) {
                    showFeedback('error', 'File terlalu besar! Maksimal 10MB.');
                    fileInput.value = '';
                    return;
                }
                
                fileName.textContent = `📄 ${file.name} (${sizeMB}MB)`;
                fileName.style.display = 'block';
                showFeedback('success', 'File siap diunggah.');
            }
        });
        
        // Show feedback function
        function showFeedback(type, message) {
            feedbackIcon.className = type === 'success' ? '✅' : '❌';
            feedbackText.textContent = message;
            feedbackArea.style.display = 'block';
            
            setTimeout(() => {
                feedbackArea.style.display = 'none';
            }, 5000);
        }
        
        // Form submit handler
        document.getElementById('contractForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const hasText = document.getElementById('contractText').value.trim().length >= 50;
            const hasFile = document.getElementById('contractFile').files.length > 0;
            
            // Validation: Hanya boleh salah satu saja
            if (hasText && hasFile) {
                showFeedback('error', 'Silakan gunakan HANYA text MATAU file, tidak keduanya.');
                return;
            }
            
            if (!hasText && !hasFile) {
                showFeedback('error', 'Silakan masukkan teks kontrak atau unggah file terlebih dahulu.');
                return;
            }
            
            // If text is selected but too short, show warning
            if (type === 'text' && !hasText) {
                showFeedback('error', 'Teks kontrak minimal 50 karakter.');
                return;
            }
            
            // Proceed with analysis
            analyzeBtn.disabled = true;
            analyzeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menganalisis...';
            
            const formData = new FormData(document.getElementById('contractForm'));
            
            try {
                const response = await fetch(document.getElementById('contractForm').action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const output = document.getElementById('analysisOutput');
                    const uidText = document.getElementById('resultUid');
                    
                    output.innerHTML = formatMarkdown(data.answer);
                    uidText.textContent = `UID: ${data.uid}`;
                    
                    // Show result section
                    const resultSection = document.getElementById('resultSection');
                    resultSection.style.display = 'block';
                    resultSection.scrollIntoView({ behavior: 'smooth' });
                } else {
                    showFeedback('error', 'Gagal: ' + (data.message || 'Terjadi kesalahan sistem.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi ke server.');
            } finally {
                analyzeBtn.disabled = false;
                analyzeBtn.innerHTML = 'Mulai Analisis AI';
            }
        });
        
        function formatMarkdown(text) {
            return text
                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                .replace(/\*(.*)\*/gim, '<em>$1</em>')
                .replace(/^\- (.*$)/gim, '<li>$1</li>')
                .replace(/\n/gim, '<br>');
        }
    </script>
</x-layouts.base>