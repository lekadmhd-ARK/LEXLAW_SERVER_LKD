<x-layouts.auth title="Login - LEXLAW v2">
    <h1>Login</h1>
    <p>Masuk untuk mengakses workspace hukum Anda.</p>

    @if(session('error'))
        <div class="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login" class="form">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login → Dashboard</button>
    </form>

    <div class="foot">Belum punya akun? <a href="{{ route('register') }}">Register</a></div>
</x-layouts.auth>
