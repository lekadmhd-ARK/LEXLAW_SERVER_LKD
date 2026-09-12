<x-layouts.base>
@section('title', 'Keamanan Akun')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🛡️ Keamanan</div>
            <h1 class="page-title">Keamanan Akun</h1>
            <p class="page-desc">Kelola session aktif dan lihat riwayat login</p>
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
