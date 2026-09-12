<x-layouts.base>
@section('title', 'Branding')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🎨 Branding</div>
            <h1 class="page-title">Kustomisasi Perusahaan</h1>
            <p class="page-desc">Logo, warna, dan branding workspace</p>
        </div>
    </div>

    @if(session('success'))
    <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px;max-width:640px">{{ session('success') }}</div>
    @endif

    <div class="card" style="max-width:640px">
        <form method="POST" action="{{ route('branding.update') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px">
            @csrf
            <div>
                <label class="label">Nama Perusahaan</label>
                <input type="text" name="name" value="{{ $company->name ?? '' }}">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="label">Primary Color</label>
                    <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#5e6ad2' }}" style="height:40px">
                </div>
                <div>
                    <label class="label">Accent Color</label>
                    <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? '#6d5ae6' }}" style="height:40px">
                </div>
            </div>

            @if(!empty($settings['logo_url']))
            <div>
                <label class="label">Logo Saat Ini</label>
                <img src="{{ $settings['logo_url'] }}" alt="Logo" style="max-height:60px;border-radius:8px;border:1px solid var(--line);padding:4px">
            </div>
            @endif

            <div>
                <label class="label">Upload Logo Baru</label>
                <input type="file" name="logo" accept="image/*">
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    @if(!empty($settings['primary_color']))
    <div class="card" style="max-width:640px;margin-top:16px">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:12px">Preview</h3>
        <div style="padding:16px;border-radius:8px;background:var(--bg2);border:1px solid var(--line)">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                @if(!empty($settings['logo_url']))
                <img src="{{ $settings['logo_url'] }}" style="height:24px">
                @endif
                <span style="font-weight:600;color:{{ $settings['primary_color'] }}">{{ $company->name }}</span>
            </div>
            <a href="#" class="btn btn-primary" style="pointer-events:none;background:{{ $settings['primary_color'] }};border-color:{{ $settings['primary_color'] }}">Tombol Primary</a>
        </div>
    </div>
    @endif
</div>
</x-layouts.base>
