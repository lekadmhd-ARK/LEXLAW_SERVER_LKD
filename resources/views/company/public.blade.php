<x-layouts.base title="{{ $company->name }} — LEXLAW v2">
    <div style="max-width:720px;margin:56px auto;padding:36px;background:var(--bg2);border:1px solid var(--line);border-radius:16px;text-align:center">
        @if($company->logo_url)
        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" style="width:96px;height:96px;border-radius:50%;object-fit:cover;margin-bottom:16px">
        @else
        <div style="width:96px;height:96px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;margin:0 auto 16px">{{ mb_substr($company->name, 0, 1) }}</div>
        @endif

        <div class="eyebrow" style="margin-bottom:8px">Perusahaan Terverifikasi — LEXLAW</div>
        <h1 style="font-size:28px;font-weight:700;color:var(--text);margin:0 0 6px">{{ $company->name }}</h1>

        @if($company->plan)
        <span style="display:inline-block;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:600;background:{{ $company->subscription_status === 'active' ? '#22c55e20' : '#f59e0b20' }};color:{{ $company->subscription_status === 'active' ? '#22c55e' : '#f59e0b' }};margin-top:8px">
            {{ $company->plan->name }} — {{ $company->subscription_status === 'active' ? 'Aktif' : 'Trial' }}
        </span>
        @endif

        <div style="display:flex;flex-direction:column;gap:8px;margin-top:28px;color:var(--muted);font-size:14px">
            @if($company->address)
            <div>📍 {{ $company->address }}</div>
            @endif
            @if($company->phone)
            <div>📞 {{ $company->phone }}</div>
            @endif
        </div>
    </div>
</x-layouts.base>