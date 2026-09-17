<x-layouts.base title="Super Admin — Perusahaan — LEXLAW v2">

<div class="page-head">
  <div>
    <div class="eyebrow">Super Admin</div>
    <h1 class="page-title">Manajemen Perusahaan</h1>
    <p class="page-desc">Kelola status langganan: approve, suspend, aktifkan/nonaktifkan, dan ubah status paket.</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('super-admin.analytics') }}" class="btn btn-secondary">Analytics</a>
    <a href="/super-admin/plans" class="btn btn-secondary">&larr; Plans</a>
  </div>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">
  {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">
  {{ session('error') }}
</div>
@endif

<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:13px">
  <thead>
    <tr style="border-bottom:2px solid var(--line)">
      <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600">Company</th>
      <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600">Plan</th>
      <th style="text-align:center;padding:10px 12px;color:var(--muted);font-weight:600">Status</th>
      <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600">Berlaku Hingga</th>
      <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600">Bukti</th>
      <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse($companies as $company)
    <tr style="border-bottom:1px solid var(--line)">
      <td style="padding:12px">
        <div style="font-weight:600">{{ $company->name }}</div>
        <div style="font-size:11px;color:var(--muted);font-family:monospace">{{ $company->tenant_id }}</div>
      </td>
      <td style="padding:12px;font-size:12px">{{ $company->plan->name ?? '-' }}</td>

      {{-- STATUS + inline form ubah status --}}
      <td style="padding:12px;text-align:center">
        @php
          $statusColors = [
              'active'    => ['#dcfce7','#16a34a'],
              'trialing'  => ['#fef9c3','#ca8a04'],
              'suspended' => ['#fee2e2','#dc2626'],
              'inactive'  => ['#f3f4f6','#6b7280'],
              'rejected'  => ['#fce7f3','#db2777'],
          ];
          [$bg, $fg] = $statusColors[$company->subscription_status] ?? ['#f3f4f6','#6b7280'];
        @endphp
        <form method="POST" action="{{ route('super-admin.companies.status', $company->id) }}" style="display:inline">
          @csrf
          <select name="subscription_status" onchange="this.form.submit()"
                  style="padding:4px 6px;border-radius:6px;border:1px solid {{ $fg }}30;font-size:11px;font-weight:600;background:{{ $bg }};color:{{ $fg }};cursor:pointer;appearance:auto">
            @foreach(['trialing','active','suspended','inactive','rejected'] as $s)
              <option value="{{ $s }}" {{ $company->subscription_status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </form>
      </td>

      <td style="padding:12px;font-size:12px;color:var(--muted)">
        {{ $company->subscribed_until ? $company->subscribed_until->format('d M Y') : ($company->trial_ends_at ? 'Trial: '.$company->trial_ends_at->format('d M Y') : '-') }}
      </td>

      <td style="padding:12px">
        @if(isset($proofs[$company->id]) && isset($proofs[$company->id]->new_values['proof_path']))
          <a href="/storage/{{ $proofs[$company->id]->new_values['proof_path'] }}" target="_blank" style="color:var(--accent);font-size:12px">Lihat Bukti ↗</a>
        @else
          <span style="color:var(--muted);font-size:11px">-</span>
        @endif
      </td>

      <td style="padding:12px;white-space:nowrap">
        {{-- ACTION BUTTONS --}}
        @if($company->subscription_status === 'active')
          <form method="POST" action="{{ route('super-admin.companies.suspend', $company->id) }}" style="display:inline">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#f59e0b;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Suspend {{ $company->name }}?')">
              ⏸ Suspend
            </button>
          </form>
          <form method="POST" action="{{ route('super-admin.companies.deactivate', $company->id) }}" style="display:inline;margin-left:4px">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#6b7280;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Nonaktifkan {{ $company->name }}? Akses hilang total.')">
              🚫 Nonaktif
            </button>
          </form>

        @elseif($company->subscription_status === 'suspended')
          <form method="POST" action="{{ route('super-admin.companies.activate', $company->id) }}" style="display:inline">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Aktifkan kembali {{ $company->name }}?')">
              ▶ Aktifkan
            </button>
          </form>
          <form method="POST" action="{{ route('super-admin.companies.deactivate', $company->id) }}" style="display:inline;margin-left:4px">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#6b7280;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Nonaktifkan {{ $company->name }}?')">
              🚫 Nonaktif
            </button>
          </form>

        @elseif($company->subscription_status === 'inactive')
          <form method="POST" action="{{ route('super-admin.companies.activate', $company->id) }}" style="display:inline">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Aktifkan {{ $company->name }}?')">
              ▶ Aktifkan
            </button>
          </form>

        @elseif($company->subscription_status === 'trialing')
          <form method="POST" action="{{ route('super-admin.companies.approve', $company->id) }}" style="display:inline">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Approve {{ $company->name }}? Aktifkan 30 hari.')">
              ✅ Approve
            </button>
          </form>
          <form method="POST" action="{{ route('super-admin.companies.reject', $company->id) }}" style="display:inline;margin-left:4px">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#ef4444;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Reject {{ $company->name }}?')">
              ✕ Reject
            </button>
          </form>

        @else
          <form method="POST" action="{{ route('super-admin.companies.activate', $company->id) }}" style="display:inline">
            @csrf
            <button type="submit" style="padding:4px 10px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600" onclick="return confirm('Aktifkan {{ $company->name }}?')">
              ▶ Aktifkan
            </button>
          </form>
        @endif
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="6" style="padding:24px;text-align:center;color:var(--muted)">Tidak ada perusahaan.</td>
    </tr>
    @endforelse
  </tbody>
</table>
</div>

<div style="margin-top:16px">{{ $companies->links() }}</div>

</x-layouts.base>
