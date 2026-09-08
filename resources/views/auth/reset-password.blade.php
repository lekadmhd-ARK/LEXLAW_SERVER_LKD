<x-layouts.auth title="Reset Password — LEXLAW v2">
    <div class="card-head">
        <h2>Buat Password Baru</h2>
        <p>Lengkapi form berikut untuk mengatur ulang password akun Anda.</p>
    </div>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update.reset') }}" class="form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field">
            <label>Email</label>
            <div class="input-wrap">
                <span class="ico">✉</span>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly>
            </div>
        </div>
        <div class="field">
            <label>Password Baru</label>
            <div class="input-wrap">
                <span class="ico">🔒</span>
                <input type="password" name="password" placeholder="Minimal 8 karakter" required autofocus>
            </div>
        </div>
        <div class="field">
            <label>Konfirmasi Password</label>
            <div class="input-wrap">
                <span class="ico">🔒</span>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>

    <div class="alt">
        Ingat password? <a href="{{ route('login') }}">Kembali ke Masuk</a>
    </div>
</x-layouts.auth>