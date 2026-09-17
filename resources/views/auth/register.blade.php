<x-layouts.auth title="Daftar — LEXLAW v2">
    <div class="card-head">
        <h2>Buat Akun Gratis</h2>
        <p>Daftar sekarang dan mulai gunakan AI hukum Indonesia.</p>
    </div>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/register" class="form">
        @csrf
        <div class="field">
            <label>Nama Lengkap</label>
            <div class="input-wrap">
                <span class="ico">👤</span>
                <input type="text" name="name" placeholder="Nama Anda" value="{{ old('name') }}" required autofocus>
            </div>
        </div>
        <div class="field">
            <label>Email</label>
            <div class="input-wrap">
                <span class="ico">✉</span>
                <input type="email" name="email" placeholder="anda@perusahaan.com" value="{{ old('email') }}" required>
            </div>
        </div>
        <div class="field">
            <label>Password</label>
            <div class="input-wrap">
                <span class="ico">🔒</span>
                <input type="password" name="password" placeholder="Min. 8 karakter" required>
            </div>
        </div>
        <div class="field">
            <label>Konfirmasi Password</label>
            <div class="input-wrap">
                <span class="ico">🔒</span>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
        </div>
        <div class="field">
            <label>Nama Perusahaan</label>
            <div class="input-wrap">
                <span class="ico">🏢</span>
                <input type="text" name="company_name" placeholder="Contoh: Leksana & Rekan" value="{{ old('company_name') }}" required>
            </div>
        </div>
        <div class="field" style="margin-top:8px">
            <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--text-muted);line-height:1.5">
                <input type="checkbox" name="consent" required style="margin-top:3px">
                <span>Saya menyetujui <a href="{{ route('privacy') }}" target="_blank" style="color:var(--accent)">Kebijakan Privasi</a> dan <a href="{{ route('tos') }}" target="_blank" style="color:var(--accent)">Syarat &amp; Ketentuan</a> LEXLAW, termasuk pengumpulan data teknis untuk keamanan.</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Buat Akun Gratis →</button>
    </form>

    <div class="alt">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
    </div>
</x-layouts.auth>