<x-layouts.base title="Verifikasi Dua Langkah — LEXLAW v2">
    <div style="max-width:480px;margin:48px auto;padding:32px;background:var(--bg2);border:1px solid var(--line);border-radius:12px">
        <div style="text-align:center">
            <div style="font-size:40px">🔐</div>
            <h1 style="font-size:22px;font-weight:700;margin:12px 0 8px;color:var(--text)">Verifikasi Dua Langkah</h1>
            <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:8px 0 20px">
                Masukkan kode 6 digit yang dikirim ke <strong style="color:var(--text)">{{ auth()->user()->email }}</strong>.
                Kode berlaku 10 menit.
            </p>
        </div>

        @if(session('status'))
        <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px;font-size:13px">{{ session('status') }}</div>
        @endif

        @if($errors->any())
        <div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px;font-size:13px">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required
                   placeholder="000000"
                   style="width:100%;text-align:center;font-size:24px;letter-spacing:12px;padding:14px;border-radius:10px;border:1px solid var(--line);background:var(--bg);color:var(--text);outline:none">
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:16px">Verifikasi</button>
        </form>

        <form method="POST" action="{{ route('two-factor.resend') }}" style="text-align:center;margin-top:12px">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:13px">Kirim ulang kode</button>
        </form>
    </div>
</x-layouts.base>