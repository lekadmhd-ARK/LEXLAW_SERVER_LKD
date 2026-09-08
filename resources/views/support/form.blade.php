<x-layouts.auth title="Hubungi Support — LEXLAW v2">
    <div class="card-head">
        <h2>Hubungi Support</h2>
        <p>Kirim pertanyaan atau kendala Anda. Tim kami akan membalas ke email Anda.</p>
    </div>

    @if(session('success'))
        <div class="alert" style="border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.1);color:#6ee7b7">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('support.store') }}" class="form">
        @csrf
        <div class="field">
            <label>Subjek</label>
            <div class="input-wrap">
                <span class="ico">✉</span>
                <input type="text" name="subject" placeholder="Mis. Kendala kontrak reviewer" value="{{ old('subject') }}" required maxlength="190">
            </div>
        </div>
        <div class="field">
            <label>Pesan</label>
            <textarea name="message" rows="6" placeholder="Jelaskan kendala Anda secara lengkap..." required minlength="10" style="width:100%;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:10px;color:var(--text);padding:12px;outline:none;font-size:13.5px;resize:vertical">{{ old('message') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Pesan →</button>
    </form>

    <div class="alt">
        Lebih cepat? <a href="https://wa.me/6281297414115" target="_blank">Chat via WhatsApp</a>
    </div>
    <div class="alt">
        <a href="{{ route('dashboard') }}">← Kembali ke Dashboard</a>
    </div>
</x-layouts.auth>