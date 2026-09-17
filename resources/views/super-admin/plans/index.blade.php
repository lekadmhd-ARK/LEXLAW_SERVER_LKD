<x-layouts.base title="Manajemen Paket — LEXLAW v2">

<div class="page-head">
  <div>
    <div class="eyebrow">Super Admin</div>
    <h1 class="page-title">Manajemen Paket Langganan</h1>
    <p class="page-desc">Kelola paket langganan: tambah, edit, hapus, dan aktifkan/nonaktifkan paket.</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('super-admin.analytics') }}" class="btn btn-secondary">Analytics</a>
    <a href="/super-admin/companies" class="btn btn-secondary">Companies</a>
    <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary">+ Tambah Paket</a>
  </div>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
  @forelse($plans as $plan)
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:24px;display:flex;flex-direction:column;gap:12px;position:relative">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <div style="font-size:18px;font-weight:700;color:var(--text)">{{ $plan->name }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">slug: {{ $plan->slug }}</div>
      </div>
      @if($plan->is_active)
        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:#22c55e20;color:#22c55e">Active</span>
      @else
        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:#6b728020;color:#6b7280">Inactive</span>
      @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
      <div style="background:var(--bg);border-radius:8px;padding:10px 12px">
        <div style="color:var(--muted)">Harga Bulanan</div>
        <div style="font-weight:700;font-size:15px;color:var(--text);margin-top:4px">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</div>
      </div>
      <div style="background:var(--bg);border-radius:8px;padding:10px 12px">
        <div style="color:var(--muted)">Harga Tahunan</div>
        <div style="font-weight:700;font-size:15px;color:var(--text);margin-top:4px">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;font-size:11px;color:var(--muted);text-align:center">
      <div style="background:var(--bg);border-radius:6px;padding:8px"><b style="color:var(--text)">{{ $plan->max_users }}</b><br>Users</div>
      <div style="background:var(--bg);border-radius:6px;padding:8px"><b style="color:var(--text)">{{ $plan->max_regulations }}</b><br>Regulasi</div>
      <div style="background:var(--bg);border-radius:6px;padding:8px"><b style="color:var(--text)">{{ $plan->max_ai_queries }}</b><br>AI Queries</div>
    </div>

    @if($plan->features && count($plan->features))
    <div style="font-size:11px;line-height:1.6">
      @foreach($plan->features as $f)
        <div>✓ {{ $f }}</div>
      @endforeach
    </div>
    @endif

    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
      <a href="{{ route('super-admin.plans.edit.one', $plan->id) }}" style="padding:6px 14px;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;text-decoration:none;font-weight:600">Edit</a>
      @if($plan->companies_count > 0)
        <span style="padding:6px 14px;background:#6b728020;color:#6b7280;border-radius:6px;font-size:11px">{{ $plan->companies_count }} perusahaan</span>
      @else
        <form method="POST" action="{{ route('super-admin.plans.destroy', $plan->id) }}" style="display:inline" onsubmit="return confirm('Hapus paket {{ $plan->name }}?')">
          @csrf @method('DELETE')
          <button type="submit" style="padding:6px 14px;background:#ef4444;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600">Hapus</button>
        </form>
      @endif
    </div>
  </div>
  @empty
  <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted)">Belum ada paket langganan.</div>
  @endforelse
</div>

</x-layouts.base>
