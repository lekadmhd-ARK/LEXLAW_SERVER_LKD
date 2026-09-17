<x-layouts.base title="{{ isset($plan) ? 'Edit' : 'Tambah' }} Paket — LEXLAW v2">

<div class="page-head">
  <div>
    <div class="eyebrow">Super Admin</div>
    <h1 class="page-title">{{ isset($plan) ? 'Edit Paket: ' . $plan->name : 'Tambah Paket Langganan Baru' }}</h1>
  </div>
  <a href="{{ route('super-admin.plans') }}" class="btn btn-secondary">&larr; Kembali</a>
</div>

@if($errors->any())
<div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">
  {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ isset($plan) ? route('super-admin.plans.update.one', $plan->id) : route('super-admin.plans.store') }}" style="display:grid;gap:16px">
  @csrf
  @if(isset($plan)) @method('PUT') @endif

  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:24px">
    <div style="font-weight:600;margin-bottom:16px;color:var(--text)">Informasi Paket</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Nama Paket *</label>
        <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Slug *</label>
        <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
    </div>
  </div>

  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:24px">
    <div style="font-weight:600;margin-bottom:16px;color:var(--text)">Harga &amp; Limit</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Harga Bulanan (Rp) *</label>
        <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" min="0" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Harga Tahunan (Rp) *</label>
        <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}" min="0" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">AI Enabled</label>
        <select name="ai_enabled" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
          <option value="1" {{ old('ai_enabled', $plan->ai_enabled ?? 1) ? 'selected' : '' }}>Ya</option>
          <option value="0" {{ old('ai_enabled', $plan->ai_enabled ?? 1) ? '' : 'selected' }}>Tidak</option>
        </select>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px">
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Max Users *</label>
        <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users ?? 5) }}" min="1" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Max Regulasi *</label>
        <input type="number" name="max_regulations" value="{{ old('max_regulations', $plan->max_regulations ?? 100) }}" min="1" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
      <div>
        <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Max AI Queries *</label>
        <input type="number" name="max_ai_queries" value="{{ old('max_ai_queries', $plan->max_ai_queries ?? 50) }}" min="0" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
      </div>
    </div>
  </div>

  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:24px">
    <div style="font-weight:600;margin-bottom:16px;color:var(--text)">Fitur &amp; Status</div>
    <div>
      <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:4px">Fitur (satu per baris)</label>
      <textarea name="features" rows="4" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;resize:vertical" placeholder="100 AI query/bulan&#10;5 user&#10;500 regulasi">{{ old('features', isset($plan) && is_array($plan->features) ? implode("\n", $plan->features) : '') }}</textarea>
    </div>
    <div style="margin-top:12px">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text)">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? 1) ? 'checked' : '' }} style="width:16px;height:16px">
        Aktif (tampilkan ke user saat register)
      </label>
    </div>
  </div>

  <div style="display:flex;gap:12px;justify-content:flex-end">
    <a href="{{ route('super-admin.plans') }}" style="padding:10px 20px;border:1px solid var(--line);border-radius:8px;color:var(--muted);text-decoration:none;font-size:13px">Batal</a>
    <button type="submit" style="padding:10px 24px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer">
      {{ isset($plan) ? '💾 Simpan Perubahan' : '+ Buat Paket' }}
    </button>
  </div>
</form>

</x-layouts.base>
