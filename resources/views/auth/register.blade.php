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
        <button type="submit" class="btn btn-primary">Buat Akun Gratis →</button>
    </form>

    <div class="alt">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
    </div>
</x-layouts.auth>