<x-layouts.base>
@section('title', 'Ganti Password')
<div>
    <div class="page-head">
        <div><div class="eyebrow">🔐 Keamanan</div><h1 class="page-title">Ganti Password</h1><p class="page-desc">Perbarui kata sandi akun Anda</p></div>
        <a href="/dashboard" class="btn">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <div class="card" style="max-width:520px">
        <form method="POST" action="/password-change">
            @csrf
            <div style="margin-bottom:16px">
                <label class="label">Password Saat Ini</label>
                <input type="password" name="current_password" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
            </div>
            <div style="margin-bottom:16px">
                <label class="label">Password Baru</label>
                <input type="password" name="new_password" required minlength="8" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
            </div>
            <div style="margin-bottom:20px">
                <label class="label">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" required minlength="8" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <a href="/dashboard" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
</x-layouts.base>