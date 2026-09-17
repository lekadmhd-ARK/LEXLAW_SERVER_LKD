<x-layouts.base title="Log Aktivitas Auth — LEXLAW v2">

@php
  $events = ['register' => ['#3b82f6', 'Register'], 'login' => ['#22c55e', 'Login'], 'login_failed' => ['#ef4444', 'Login Gagal'], 'logout' => ['#6b7280', 'Logout']];
  $sortLink = function (string $key, string $label, string $currentSort, string $currentDir) {
      $dir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
      $active = $currentSort === $key;
      $query = array_merge(request()->query(), ['sort' => $key, 'dir' => $dir]);
      $arrow = !$active ? '⇅' : ($currentDir === 'asc' ? '↑' : '↓');
      return '<a href="' . e(route('super-admin.auth-activities', $query)) . '" class="th-sort' . ($active ? ' active' : '') . '" title="Urutkan: ' . $label . '">' . $arrow . '</a>';
  };
@endphp

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

<div class="card" style="padding:20px">
  <form method="get" class="aa-toolbar">
    <div class="aa-search">
      <svg class="aa-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari email, nama, IP, MAC, fingerprint…" class="aa-input">
    </div>
    <select name="event" class="aa-select">
      <option value="">Semua event</option>
      @foreach ($events as $ev => $info)
        <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ $info[1] }} ({{ $ev }})</option>
      @endforeach
    </select>
    <button class="btn btn-primary" type="submit">Filter</button>
    @if (request()->hasAny(['q', 'event', 'sort', 'dir']))
      <a href="/super-admin/auth-activities" class="btn btn-secondary">Reset</a>
    @endif
  </form>

  @if (request()->filled('q') || request()->filled('event'))
    <div style="margin-top:10px;font-size:12px;color:var(--muted)">
      Ditemukan <span style="color:var(--text);font-weight:600">{{ number_format($activities->total(), 0, ',', '.') }}</span> entri
      @if (request()->filled('q'))
        untuk pencarian <em>“{{ e(request('q')) }}”</em>
      @endif
      @if (request()->filled('event'))
        pada event <em>{{ ($events[request('event')] ?? [null, request('event')])[1] }}</em>
      @endif
    </div>
  @endif

  <div style="margin-top:16px;overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Waktu {!! $sortLink('time', 'Waktu', $currentSort, $currentDir) !!}</th>
          <th>User {!! $sortLink('user', 'User', $currentSort, $currentDir) !!}</th>
          <th>Event {!! $sortLink('event', 'Event', $currentSort, $currentDir) !!}</th>
          <th>IP {!! $sortLink('ip', 'IP', $currentSort, $currentDir) !!}</th>
          <th>Fingerprint {!! $sortLink('fingerprint', 'Fingerprint', $currentSort, $currentDir) !!}</th>
          <th>Lokasi {!! $sortLink('location', 'Lokasi', $currentSort, $currentDir) !!}</th>
          <th>MAC / IP lokal {!! $sortLink('mac', 'MAC / IP lokal', $currentSort, $currentDir) !!}</th>
          <th>User-Agent {!! $sortLink('agent', 'User-Agent', $currentSort, $currentDir) !!}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($activities as $i => $a)
        <tr>
          <td class="dt-cell dt-nowrap dt-muted">{{ $a->created_at?->format('d/m/Y H:i:s') }}</td>
          <td class="dt-cell dt-user">
            <div class="dt-name">{{ $a->user?->name ?? '—' }}</div>
            <div class="dt-sub">{{ $a->email ?? '—' }}</div>
          </td>
          <td class="dt-cell">
            @php($info = $events[$a->event] ?? ['#6b7280', $a->event])
            <span class="dt-badge" style="background:{{ $info[0] }}22;color:{{ $info[0] }}">{{ $info[1] }}</span>
          </td>
          <td class="dt-cell dt-mono">{{ $a->ip_address ?? '—' }}</td>
          <td class="dt-cell">
            @if($a->device_fingerprint)
              <span class="dt-mono dt-sm" title="{{ $a->device_fingerprint }}">{{ substr($a->device_fingerprint, 0, 12) }}…</span>
            @else
              <span class="dt-muted">—</span>
            @endif
          </td>
          <td class="dt-cell">
            @if($a->geo_country)
              <div class="dt-loc">
                {{ $a->geo_country }}{{ $a->geo_region ? ' — ' . $a->geo_region : '' }}{{ $a->geo_city ? ', ' . $a->geo_city : '' }}
              </div>
              @if($a->geo_lat !== null && $a->geo_lon !== null)
                <div class="dt-sub dt-mono">{{ round($a->geo_lat, 4) }}, {{ round($a->geo_lon, 4) }}</div>
              @endif
              @if($a->geo_isp)
                <div class="dt-sub">{{ $a->geo_isp }}</div>
              @endif
            @else
              <span class="dt-muted">—</span>
            @endif
          </td>
          <td class="dt-cell">
            <span class="dt-mono">{{ $a->mac_address ?? '—' }}</span>
            @if($a->local_ip && !$a->mac_address && $a->local_ip !== ($a->mac_address ?? null))
              <div class="dt-sub">lokal: {{ $a->local_ip }}</div>
            @endif
          </td>
          <td class="dt-cell dt-ua" title="{{ $a->user_agent ?? '' }}">{{ $a->user_agent ?? '—' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="dt-cell dt-empty">Belum ada aktivitas tercatat.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $activities->links('components.pagination') }}
</div>

<style>
.aa-toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.aa-search { position:relative; flex:1; min-width:240px; }
.aa-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; }
.aa-input {
  width:100%; padding:9px 12px 9px 36px; border:1px solid var(--line); border-radius:10px;
  background:var(--bg); color:var(--text); font-size:14px; outline:none; transition:border-color .15s, box-shadow .15s;
}
.aa-input::placeholder { color:var(--muted); }
.aa-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent); }
.aa-select {
  padding:9px 12px; border:1px solid var(--line); border-radius:10px; background:var(--bg); color:var(--text);
  font-size:14px; outline:none;
}
.aa-select:focus { border-color:var(--accent); box-shadow:0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent); }

.dt { width:100%; border-collapse:collapse; font-size:13px; }
.dt thead th {
  background:var(--bg); color:var(--muted); text-align:left; font-weight:600; font-size:12px;
  text-transform:uppercase; letter-spacing:.04em; padding:10px 12px; white-space:nowrap;
  border-bottom:1px solid var(--line); position:sticky; top:0;
}
.dt tbody tr { border-bottom:1px solid var(--line); transition:background .1s; }
.dt tbody tr:nth-child(even) { background:color-mix(in srgb, var(--bg) 45%, var(--bg2)); }
.dt tbody tr:hover { background:color-mix(in srgb, var(--accent) 7%, transparent); }
.dt-cell { padding:11px 12px; vertical-align:top; color:var(--text); }
.dt-nowrap { white-space:nowrap; }
.dt-muted { color:var(--muted); }
.dt-mono { font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
.dt-sm { font-size:12px; }
.dt-name { font-weight:600; color:var(--text); }
.dt-sub { font-size:12px; color:var(--muted); margin-top:2px; }
.dt-user { min-width:150px; }
.dt-badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:600; white-space:nowrap; }
.dt-loc { font-weight:500; }
.dt-ua { max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--muted); }
.dt-empty { text-align:center; color:var(--muted); padding:32px 12px; }

.th-sort { margin-left:6px; text-decoration:none; color:var(--muted); font-size:12px; opacity:.7; }
.th-sort:hover { opacity:1; }
.th-sort.active { color:var(--accent); opacity:1; }
</style>

</x-layouts.base>