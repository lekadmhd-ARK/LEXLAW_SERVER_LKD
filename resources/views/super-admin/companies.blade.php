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

        <button type="button"
                class="btn-delete js-delete-open"
                data-id="{{ $company->id }}"
                data-name="{{ $company->name }}"
                data-tenant="{{ $company->tenant_id }}"
                data-users="{{ $company->users->count() }}"
                title="Hapus permanen client ini dari database">🗑 Hapus</button>
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

{{-- Modal konfirmasi hapus client --}}
<div class="dl-overlay" id="dl-overlay" hidden>
  <div class="dl-modal" role="dialog" aria-modal="true" aria-labelledby="dl-title">
    <div class="dl-head">
      <div>
        <h2 id="dl-title" style="margin:0;font-size:16px;color:var(--text)">Hapus Client Permanen</h2>
        <p class="dl-sub">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <button type="button" class="dl-x" onclick="deleteClose()" aria-label="Tutup">✕</button>
    </div>

    <div class="dl-body">
      <div style="font-weight:700;color:var(--text);font-size:14px" id="dl-company"></div>
      <div style="font-size:12px;color:var(--muted);font-family:monospace;margin-top:2px" id="dl-tenant"></div>

      <div class="dl-warn">
        Perusahaan beserta <b id="dl-users"></b> akan dihapus permanen dari database:
        semua user, regulasi, workspace, dokumen (termasuk file-nya), chat AI, log auth/audit, dan bukti pembayaran milik client ini ikut terhapus.
      </div>

      <label style="display:block;margin-top:14px;font-size:12px;color:var(--muted)">
        Ketik nama client untuk konfirmasi:
      </label>
      <input type="text" class="dl-input" id="dl-input" placeholder="contoh: {{ $companies->first()->name ?? 'Nama Client' }}" autocomplete="off">

      <form method="POST" id="dl-form" action="">
        @csrf
        @method('DELETE')
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:18px">
          <button type="button" class="btn btn-secondary" onclick="deleteClose()">Batal</button>
          <button type="submit" class="btn btn-danger" id="dl-confirm" disabled>Hapus Permanen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.btn-delete {
  margin-left:6px; padding:4px 10px; background:transparent; color:#ef4444; border:1px solid #ef444466;
  border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; transition:all .15s;
}
.btn-delete:hover { background:#ef4444; color:#fff; }
.btn.btn-danger { background:#ef4444; color:#fff; }
.btn.btn-danger:hover { opacity:.9; box-shadow:0 4px 12px rgba(239,68,68,.35); }
.btn.btn-danger:disabled { opacity:.45; cursor:not-allowed; box-shadow:none; }

.dl-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:16px; }
.dl-overlay[hidden] { display:none; }
.dl-modal {
  background:var(--bg); border:1px solid var(--line); border-radius:14px; width:100%; max-width:440px;
  box-shadow:0 20px 50px rgba(0,0,0,.35); overflow:hidden;
}
.dl-head { display:flex; justify-content:space-between; align-items:flex-start; padding:18px 20px 12px; }
.dl-sub { margin:2px 0 0; font-size:12px; color:var(--muted); }
.dl-x { background:none; border:none; color:var(--muted); font-size:14px; cursor:pointer; padding:4px; }
.dl-x:hover { color:var(--text); }
.dl-body { padding:0 20px 20px; }
.dl-warn {
  margin-top:12px; padding:10px 12px; border-radius:8px; font-size:12px; color:#b91c1c;
  background:#ef444415; border:1px solid #ef444430; line-height:1.5;
}
.dl-input {
  width:100%; margin-top:6px; padding:9px 12px; border:1px solid var(--line); border-radius:8px;
  background:var(--bg2); color:var(--text); font-size:14px; outline:none; box-sizing:border-box;
}
.dl-input:focus { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.15); }
</style>
<script>
window.deleteState = null;
function deleteOpen(data) {
  deleteState = data;
  document.getElementById('dl-overlay').hidden = false;
  document.getElementById('dl-company').textContent = data.name;
  document.getElementById('dl-tenant').textContent = 'tenant: ' + data.tenant + ' · ' + data.users + ' user';
  document.getElementById('dl-users').textContent = data.users + ' user';
  document.getElementById('dl-form').action = '/super-admin/companies/' + data.id;
  var inp = document.getElementById('dl-input');
  inp.value = '';
  inp.placeholder = 'ketik: ' + data.name;
  deleteSync();
  inp.focus();
}
function deleteSync() {
  var ok = document.getElementById('dl-input').value.trim() === deleteState.name;
  document.getElementById('dl-confirm').disabled = !ok;
}
function deleteClose() {
  document.getElementById('dl-overlay').hidden = true;
}
document.querySelectorAll('.js-delete-open').forEach(function (btn) {
  btn.addEventListener('click', function () {
    deleteOpen({
      id: parseInt(this.dataset.id, 10),
      name: this.dataset.name,
      tenant: this.dataset.tenant,
      users: parseInt(this.dataset.users, 10)
    });
  });
});
document.getElementById('dl-input').addEventListener('input', deleteSync);
document.getElementById('dl-overlay').addEventListener('click', function (e) {
  if (e.target.id === 'dl-overlay') deleteClose();
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') deleteClose();
});
</script>

</x-layouts.base>
