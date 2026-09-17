<x-layouts.base title="Log Aktivitas Auth — LEXLAW v2">

<div class="page-head">
  <div>
    <div class="eyebrow">Super Admin</div>
    <h1 class="page-title">Log Aktivitas Auth</h1>
    <p class="page-desc">Register, login (sukses/gagal), dan logout — IP + User-Agent + MAC (best-effort).</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="/super-admin/analytics" class="btn btn-secondary">Analytics</a>
    <a href="/super-admin/companies" class="btn btn-secondary">Companies</a>
  </div>
</div>

<form method="get" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari email, nama, atau IP…" class="input"
         style="padding:8px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg2);color:var(--text);min-width:220px">
  <select name="event" class="input" style="padding:8px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg2);color:var(--text)">
    <option value="">Semua event</option>
    @foreach (['register','login','login_failed','logout'] as $ev)
      <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ $ev }}</option>
    @endforeach
  </select>
  <button class="btn btn-primary" type="submit">Filter</button>
  <a href="/super-admin/auth-activities" class="btn btn-secondary">Reset</a>
</form>

<div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:8px;overflow-x:auto">
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
      <tr style="color:var(--muted);text-align:left">
        <th style="padding:10px 8px">Waktu</th>
        <th style="padding:10px 8px">User</th>
        <th style="padding:10px 8px">Event</th>
        <th style="padding:10px 8px">IP</th>
        <th style="padding:10px 8px">MAC / IP lokal</th>
        <th style="padding:10px 8px">User-Agent</th>
      </tr>
    </thead>
    <tbody>
      @forelse($activities as $a)
      <tr style="border-top:1px solid var(--line)">
        <td style="padding:10px 8px;white-space:nowrap;color:var(--muted)">{{ $a->created_at?->format('d/m/Y H:i:s') }}</td>
        <td style="padding:10px 8px">
          <div style="font-weight:600;color:var(--text)">{{ $a->user?->name ?? '—' }}</div>
          <div style="color:var(--muted);font-size:12px">{{ $a->email ?? '—' }}</div>
        </td>
        <td style="padding:10px 8px">
          @php
            $colors = ['login' => '#22c55e', 'register' => '#3b82f6', 'login_failed' => '#ef4444', 'logout' => '#6b7280'];
            $color = $colors[$a->event] ?? '#6b7280';
          @endphp
          <span style="background:{{ $color }}22;color:{{ $color }};padding:3px 8px;border-radius:999px;font-size:11px;font-weight:600">{{ $a->event }}</span>
        </td>
        <td style="padding:10px 8px;font-family:monospace;color:var(--text)">{{ $a->ip_address ?? '—' }}</td>
        <td style="padding:10px 8px">
          <span style="font-family:monospace;color:var(--text)">{{ $a->mac_address ?? '—' }}</span>
          @if($a->local_ip && $a->local_ip !== ($a->mac_address ?? null) && !$a->mac_address)
            <div style="color:var(--muted);font-size:11px">lokal: {{ $a->local_ip }}</div>
          @endif
        </td>
        <td style="padding:10px 8px;color:var(--muted);max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $a->user_agent ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="6" style="padding:20px;text-align:center;color:var(--muted)">Belum ada aktivitas tercatat.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div style="margin-top:16px">{{ $activities->links() }}</div>

</x-layouts.base>