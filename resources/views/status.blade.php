<x-layouts.base title="Status Sistem — LEXLAW v2">
    <div style="max-width:640px;margin:48px auto">
        <div style="text-align:center;margin-bottom:24px">
            <div style="font-size:40px">🩺</div>
            <h1 style="font-size:24px;font-weight:700;margin:12px 0 4px;color:var(--text)">Status Sistem LEXLAW</h1>
            <p style="color:var(--muted);font-size:13px">Halaman publik — status komponen inti layanan.</p>
        </div>
        @php
            $checks = [];
            try {
                \Illuminate\Support\Facades\DB::select('select 1');
                $checks[] = ['label' => 'Database', 'ok' => true, 'detail' => 'Terhubung'];
            } catch (\Throwable $e) {
                $checks[] = ['label' => 'Database', 'ok' => false, 'detail' => 'Gagal terhubung'];
            }
            $checks[] = ['label' => 'Aplikasi', 'ok' => true, 'detail' => 'Berjalan'];
            $checks[] = ['label' => 'Waktu Server', 'ok' => true, 'detail' => now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB'];
        @endphp
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($checks as $check)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:var(--bg2);border:1px solid var(--line);border-radius:10px">
                <div style="font-weight:600;color:var(--text);font-size:14px">{{ $check['label'] }}</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:13px;color:var(--muted)">{{ $check['detail'] }}</span>
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $check['ok'] ? '#22c55e' : '#ef4444' }}"></span>
                </div>
            </div>
            @endforeach
        </div>
        <p style="text-align:center;color:var(--muted);font-size:12px;margin-top:24px">Informasi lebih lanjut: <a href="/support" style="color:var(--accent)">Hubungi support</a></p>
    </div>
</x-layouts.base>