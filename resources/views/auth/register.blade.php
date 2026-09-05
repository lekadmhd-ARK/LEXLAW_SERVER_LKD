<x-layouts.auth title="Daftar - LEXLAW v2">
    <h1>Daftar Akun</h1>
    <p>Buat akun tenant perusahaan Anda di LEXLAW v2.</p>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/register" class="form">
        @csrf
        <div>
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Daftar Sekarang</button>
    </form>

    <div class="foot">Sudah punya akun? <a href="{{ route('login') }}">Login</a></div>
</x-layouts.auth>