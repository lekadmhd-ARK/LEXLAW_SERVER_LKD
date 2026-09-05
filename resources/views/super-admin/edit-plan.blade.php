@extends('layouts.base')

@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">💰 Manajemen Langganan</div>
        <h1 class="page-title">Edit Harga Langganan</h1>
    </div>
    <a href="/dashboard" class="btn btn-secondary">← Kembali ke Dashboard</a>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">
    {{ session('success') }}
</div>
@endif

@if($canEditPrice)
<div class="card" style="padding:24px">
    <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:20px">⚠️ Perubahan Harga Langganan</div>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:24px">
        Fitur ini hanya untuk <strong>Super-Admin</strong>. Perubahan harga akan berlaku bagi semua tenant/user di sistem.
    </p>

    <form method="POST" action="/super-admin/plans/edit" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:8px">Harga Bulanan Baru (IDR)</label>
            <input type="number" name="monthly_price" id="price" min="10000" max="1000000" 
                   value="{{ old('monthly_price') }}" 
                   class="form-input" 
                   style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:16px">
        </div>

        <div>
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:8px">Harga Tahunan Baru (IDR)</label>
            <input type="number" name="yearly_price" id="yearly-price" min="100000" max="5000000"
                   value="{{ old('yearly_price') }}"
                   class="form-input"
                   style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:16px">
            <small style="font-size:12px;color:var(--text-muted);display:block;margin-top:6px">
                (Biaya 12x bulanan, diskon 17% secara otomatis)
            </small>
        </div>

        <div style="margin-top:24px">
            <p style="font-size:12px;color:#ef4444;margin-bottom:12px">
                ⚠️ Peringatan: Setelah disimpan, harga baru akan otomatis berlaku bagi:
            </p>
            <ul style="margin:6px 0 0 20px;font-size:13px;color:var(--text-muted);line-height:1.6">
                <li>Semua user yang sudah berlangganan</li>
                <li>User baru yang mendaftar setelah perubahan</li>
                <li>Perpanjangan langganan masa depan</li>
            </ul>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:32px">
            <a href="/super-admin/plans" class="btn btn-secondary" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">💾 Simpan Perubahan Harga</button>
        </div>
    </form>
</div>
@else
<div class="card" style="padding:24px;text-align:center;color:var(--text-muted)">
    <div style="font-size:48px;margin:30px 0;opacity:0.5">🔒</div>
    <h3 style="margin-bottom:12px">Akses Ditolak</h3>
    <p>Fitur edit harga langganan hanya bisa diakses oleh <strong>Super-Admin</strong>.</p>
    <p style="font-size:14px">Silakan hubungi administrator sistem atau pergi ke dashboard.</p>
    <a href="/dashboard" class="btn btn-primary">Kembali ke Dashboard</a>
</div>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('price').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\..*/, '$1');
});

document.getElementById('yearly-price').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\..*/, '$1');
});
</script>
@endpush