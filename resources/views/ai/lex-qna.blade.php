<x-layouts.base title="AI Lex Q&A">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">✦ AI Lex Q&A</div>
                <h1 class="page-title">Legal Q&A Assistant</h1>
                <p class="page-desc">Tanyakan perihal hukum Indonesia — AI menjawab berdasarkan database regulasi</p>
            </div>
            <div style="display:flex;gap:8px">
                <form method="POST" action="/ai/lex-qna/clear" onsubmit="return confirm('Hapus semua percakapan?')">
                    @csrf
                    <button class="btn btn-secondary" style="padding:8px 16px;font-size:13px">🗑️ Bersihkan Chat</button>
                </form>
                <a href="/dashboard" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>

        <div class="card" style="padding:0;height:600px;display:flex;flex-direction:column">
            <div id="chatContainer" style="flex:1;overflow-y:auto;padding:20px">
                @php $history = session('lexqna_history', []); @endphp
                @if(!empty($history))
                    @foreach($history as $msg)
                        @if($msg['role'] === 'user')
                            {{-- Chat User: Sebelah KANAN --}}
                            <div style="display:flex;justify-content:flex-end;margin-bottom:10px">
                                <div style="max-width:70%;background:var(--accent);color:#fff;padding:10px 16px;border-radius:16px 16px 4px 16px;box-shadow:0 1px 2px rgba(0,0,0,0.08)">
                                    <div style="font-size:10px;font-weight:600;opacity:0.8;margin-bottom:4px;text-transform:uppercase;text-align:right">Anda</div>
                                    <div style="font-size:14px;line-height:1.45;white-space:pre-wrap">{{ $msg['content'] }}</div>
                                </div>
                            </div>
                        @else
                            {{-- Chat AI: Sebelah KIRI --}}
                            <div style="display:flex;justify-content:flex-start;margin-bottom:10px">
                                <div style="max-width:80%;background:var(--bg-secondary);border:1px solid var(--line);padding:12px 16px;border-radius:16px 16px 16px 4px">
                                    <div style="font-size:10px;font-weight:600;color:var(--accent);margin-bottom:6px;text-transform:uppercase;display:flex;align-items:center;gap:6px">
                                        <span style="font-size:14px">🤖</span> LEX AI
                                    </div>
                                    <div style="font-size:14px;line-height:1.5;color:var(--text)">
                                        @php
                                            $paragraphs = preg_split('/\n\s*\n/', $msg['content']);
                                            $paragraphs = array_filter($paragraphs, function($p) { return trim($p) !== ''; });
                                        @endphp
                                        @foreach($paragraphs as $paragraph)
                                            <p style="margin-bottom:8px;text-indent:16px">{{ trim($paragraph) }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div style="text-align:center;color:var(--text-muted);font-size:14px;margin-top:80px">
                        ✦ Mulai bertanya tentang hukum Indonesia<br>
                        <span style="font-size:12px">Contoh: "Apa pasal KUHP tentang pencurian data?"</span>
                    </div>
                @endif
            </div>

            <div style="padding:16px 20px;border-top:1px solid var(--line);background:var(--bg-secondary)">
                <form method="POST" action="/ai/lex-qna" id="chatForm" style="display:flex;gap:12px;align-items:flex-end">
                    @csrf
                    <div style="flex:1">
                        <textarea id="questionInput" name="question" placeholder="Tanyakan perihal hukum Anda..." rows="1" required style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;outline:none;resize:none;overflow:hidden;min-height:44px;max-height:120px;line-height:1.4"></textarea>
                    </div>
                    <button type="submit" id="sendBtn" class="btn btn-primary" style="padding:12px 24px;font-size:14px;font-weight:600;height:44px;white-space:nowrap;min-width:140px;display:flex;align-items:center;justify-content:center;gap:8px">
                        <span id="btnText">✦ Tanyakan</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        #sendBtn:disabled { opacity: 0.7; cursor: not-allowed; background: var(--text-muted) !important; }
        .spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var container = document.getElementById('chatContainer');
            if (container) container.scrollTop = container.scrollHeight;

            var input = document.getElementById('questionInput');
            var btn = document.getElementById('sendBtn');
            var form = document.getElementById('chatForm');

            if (input) {
                input.addEventListener('input', function() {
                    this.style.height = '44px';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (input.value.trim() && !btn.disabled) {
                            btn.disabled = true;
                            btn.innerHTML = '<span class="spinner"></span> Memproses...';
                            form.submit();
                        }
                    }
                });
                input.focus();
            }

            if (form && btn) {
                form.addEventListener('submit', function(e) {
                    if (btn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    if (!input.value.trim()) {
                        e.preventDefault();
                        input.focus();
                        return;
                    }
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span> Memproses...';
                });
            }
        });
    </script>
</x-layouts.base>
