<x-layouts.base>
@section('title', 'Keamanan Akun')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🛡️ Keamanan</div>
            <h1 class="page-title">Keamanan Akun</h1>
            <p class="page-desc">Kelola autentikasi dua langkah, session aktif, dan riwayat login</p>
        </div>
    </div>

    <!-- Two-Factor Authentication -->
    @php $isSuperAdmin = auth()->user()->role == 1; @endphp
    <div class="card" style="max-width:800px;margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <div>
                <h3 style="font-size:16px;font-weight:600">Autentikasi Dua Langkah (2FA)</h3>
                <p style="font-size:13px;color:var(--muted);margin-top:4px;max-width:520px">
                    @if($isSuperAdmin)
                        Wajib untuk Super Admin — kode OTP dikirim ke email setiap kali login untuk keamanan.
                    @else
                        Melindungi akun dengan kode OTP yang dikirim ke email saat login.
                        Disarankan untuk role Owner &amp; Admin.
                    @endif
                </p>
            </div>
            @php $isActive = auth()->user()->two_factor_enabled || $isSuperAdmin; @endphp
            <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $isActive ? '#22c55e20' : '#6b728020' }};color:{{ $isActive ? '#22c55e' : '#6b7280' }}">
                {{ $isActive ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div style="margin-top:16px;display:flex;gap:8px">
            @if($isSuperAdmin)
            <span style="font-size:13px;color:var(--muted)">Wajib — tidak dapat dinonaktifkan</span>
            @elseif(auth()->user()->two_factor_enabled)
            <form method="POST" action="{{ route('security.two-factor.disable') }}" onsubmit="return confirm('Nonaktifkan autentikasi dua langkah?')">@csrf
                <button type="submit" class="btn btn-secondary" style="border-color:#ef4444;color:#ef4444">Nonaktifkan</button>
            </form>
            @else
            <form method="POST" action="{{ route('security.two-factor.enable') }}">@csrf
                <button type="submit" class="btn btn-primary">Aktifkan</button>
            </form>
            @endif
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="card" style="max-width:800px;margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:600">Session Aktif ({{ $sessions->count() }})</h3>
            @if($sessions->count() > 1)
            <form method="POST" action="{{ route('security.revoke-all') }}" onsubmit="return confirm('Cabut semua session lain?')">@csrf
                <button type="submit" class="btn btn-secondary" style="font-size:12px">Cabut Semua Lainnya</button>
            </form>
            @endif
        </div>

        @if($sessions->count())
        <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP</th>
                    <th>Terakhir Aktif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $s)
                <tr>
                    <td>
                        <div style="font-weight:500">{{ $s->device }}</div>
                        <div style="font-size:11px;color:var(--muted);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $s->user_agent }}</div>
                    </td>
                    <td style="font-size:12px;color:var(--muted)">{{ $s->ip_address }}</td>
                    <td style="font-size:12px;color:var(--muted)">{{ $s->last_active }}</td>
                    <td style="text-align:right">
                        @if($s->id === session()->getId())
                        <span style="font-size:11px;color:var(--ok)">Session ini</span>
                        @else
                        <form method="POST" action="{{ route('security.revoke', $s->id) }}" onsubmit="return confirm('Cabut session ini?')">@csrf @method('DELETE')
                            <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:12px">Cabut</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div style="padding:24px;text-align:center;color:var(--muted)">Tidak ada session aktif.</div>
        @endif
    </div>

    <!-- Login History -->
    <div class="card" style="max-width:800px">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Riwayat Login</h3>
        @if($logins->count())
        <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logins as $log)
                <tr>
                    <td style="font-size:12px;color:var(--muted)">{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td>
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ str_contains($log->action, 'failed') ? '#ef444420' : '#22c55e20' }};color:{{ str_contains($log->action, 'failed') ? '#ef4444' : '#22c55e' }}">
                            {{ str_contains($log->action, 'failed') ? 'Gagal' : 'Berhasil' }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:var(--muted)">{{ $log->ip_address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada riwayat login.</div>
        @endif
    </div>
</div>
</x-layouts.base>
