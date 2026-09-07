<x-layouts.base title="Konsolidasi">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">🧩 Konsolidasi Regulasi</div>
                <h1 class="page-title">Konsolidasi Peraturan</h1>
                <p class="page-desc">Gabungkan naskah induk + perubahannya menjadi satu naskah utuh dengan penanda perubahan.</p>
            </div>
            <a href="/consolidations/create" class="btn btn-primary">+ Konsolidasi Baru</a>
        </div></div>

        @if(session('success'))
        <div class="alert" style="padding:12px 16px;border-radius:8px;background:var(--ok);color:#fff;margin-bottom:16px">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert" style="padding:12px 16px;border-radius:8px;background:var(--err);color:#fff;margin-bottom:16px">{{ session('error') }}</div>
        @endif

        @if($items->count())
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($items as $item)
            <a href="/consolidations/{{ $item->id }}" style="display:block;padding:18px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg2);color:var(--text);text-decoration:none;transition:border-color .15s">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
                    <div>
                        <h3 style="margin:0 0 4px;font-size:16px;font-weight:700">{{ $item->title }}</h3>
                        <div style="font-size:12px;color:var(--muted)">Versi {{ $item->version }} · {{ $item->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--accent-bg);color:var(--accent)">{{ strtoupper($item->status ?? 'draft') }}</span>
                        @if($item->ai_metadata && isset($item->ai_metadata['confidence']))
                        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--bg);color:var(--muted);border:1px solid var(--line)">Confidence {{ $item->ai_metadata['confidence'] }}%</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div style="padding:48px;text-align:center;color:var(--muted);border:1px dashed var(--line);border-radius:var(--radius)">
            Belum ada konsolidasi. Klik "+ Konsolidasi Baru" untuk mulai.
        </div>
        @endif

        <div style="margin-top:20px">{{ $items->links() }}</div>
    </div>

    <style>
    .cr-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding:24px;background:linear-gradient(135deg,var(--accent) 0%,#8b5cf6 100%);border-radius:var(--radius);color:#fff;border:1px solid color-mix(in srgb,var(--accent) 40%,transparent);box-shadow:0 8px 30px -10px var(--accent)}
    .cr-hero .page-head{flex:1;display:block;margin:0;padding:0;border:0;background:transparent}
    .cr-hero .eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.2);margin-bottom:8px;color:#fff}
    .cr-hero .page-title{margin:0 0 4px;font-size:28px;font-weight:700;letter-spacing:-.5px;color:#fff}
    .cr-hero .page-desc{margin:0;font-size:14px;opacity:.9;line-height:1.5;color:#fff}
    .cr-hero .btn-primary{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;margin-top:12px}
    .cr-hero .btn-primary:hover{background:rgba(255,255,255,.3)}
    a:hover{border-color:var(--accent)}
    </style>
</x-layouts.base>
