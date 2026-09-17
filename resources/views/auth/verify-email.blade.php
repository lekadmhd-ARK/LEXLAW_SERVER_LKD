<x-layouts.base title="Verifikasi Email — LEXLAW v2">
    <div style="max-width:560px;margin:48px auto;padding:32px;background:var(--bg2);border:1px solid var(--line);border-radius:12px">
        <div style="text-align:center">
            <div style="font-size:40px">📧</div>
            <h1 style="font-size:22px;font-weight:700;margin:12px 0 8px;color:var(--text)">Verifikasi Alamat Email</h1>
            @if(session('status'))
            <p style="color:var(--accent);font-size:14px;margin:8px 0">{{ session('status') }}</p>
            @endif
            <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:8px 0 20px">
                Kami telah mengirimkan link verifikasi ke <strong style="color:var(--text)">{{ auth()->user()->email }}</strong>.
                Buka email Anda dan klik tombol verifikasi untuk mengaktifkan semua fitur.
            </p>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Kirim Ulang Link Verifikasi</button>
            </form>
            <p style="color:var(--muted);font-size:12px;margin-top:16px">Belum menerima email? Periksa folder spam, atau klik tombol di atas.</p>
        </div>
    </div>
</x-layouts.base>