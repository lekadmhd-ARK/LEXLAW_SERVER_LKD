<x-layouts.auth title="Lupa Password — LEXLAW v2">
    <div class="card-head">
        <h2>Lupa Password</h2>
        <p>Masukkan email akun Anda. Kami kirim tautan reset password.</p>
    </div>

    @if(session('status'))
        <div class="alert" style="border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.1);color:#6ee7b7">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="form">
        @csrf
        <div class="field">
            <label>Email</label>
            <div class="input-wrap">
                <span class="ico">✉</span>
                <input type="email" name="email" placeholder="anda@perusahaan.com" value="{{ old('email') }}" required autofocus>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
    </form>

    <div class="alt">
        Ingat password? <a href="{{ route('login') }}">Kembali ke Masuk</a>
    </div>

    <div class="alt">
        Butuh bantuan? <a href="https://wa.me/6281297414115" target="_blank">Chat via WhatsApp</a>
    </div>
</x-layouts.auth>