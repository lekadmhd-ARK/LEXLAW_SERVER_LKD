<x-layouts.auth title="Masuk — LEXLAW v2">
    <div class="card-head">
        <h2>Selamat Datang Kembali</h2>
        <p>Masuk untuk mengakses workspace hukum Anda.</p>
    </div>

    @if(session('error'))
        <div class="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login" class="form">
        @csrf
        <div class="field">
            <label>Email</label>
            <div class="input-wrap">
                <span class="ico">✉</span>
                <input type="email" name="email" placeholder="anda@perusahaan.com" value="{{ old('email') }}" required autofocus>
            </div>
        </div>
        <div class="field">
            <label>Password</label>
            <div class="input-wrap">
                <span class="ico">🔒</span>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        <div class="row-between">
            <label class="check"><input type="checkbox" name="remember"> Ingat saya</label>
            <a href="#" class="link">Lupa password?</a>
        </div>
        <button type="submit" class="btn btn-primary">Masuk →</button>
    </form>

    <div class="divider">atau</div>

    <a href="/register" class="btn" style="border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--text)">
        ✨ Daftar Akun Gratis
    </a>

    <div class="alt">
        Butuh bantuan? <a href="https://wa.me/6281297414115" target="_blank">Chat via WhatsApp</a>
    </div>
</x-layouts.auth>
