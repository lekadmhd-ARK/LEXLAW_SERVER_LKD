<x-layouts.base title="{{ $consolidation->title }}">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">🧩 Konsolidasi · Versi {{ $consolidation->version }}</div>
                <h1 class="page-title">{{ $consolidation->title }}</h1>
                <p class="page-desc">
                    @if($consolidation->ai_metadata)
                    Confidence {{ $consolidation->ai_metadata['confidence'] ?? '-' }}% · Model {{ $consolidation->ai_metadata['model'] ?? '-' }}
                    @endif
                </p>
            </div>
            <a href="/consolidations" class="btn btn-primary">← Kembali</a>
        </div></div>

        @if(($consolidation->status ?? '') === 'review_required')
        <div style="padding:14px 18px;border-radius:8px;background:var(--warn);color:#000;margin-bottom:16px;font-size:14px">
            ⚠️ Hasil ini perlu review manual — confidence rendah. Periksa kembali setiap pasal sebelum publish.
        </div>
        @endif

        {{-- Sumber regulasi --}}
        @if($consolidation->source_regulations)
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
            @foreach($consolidation->source_regulations as $src)
            <span style="padding:5px 12px;border-radius:99px;font-size:12px;background:var(--accent-bg);color:var(--accent);border:1px solid var(--line)">
                {{ $src['title'] ?? '' }} (No.{{ $src['number'] ?? '-' }} Tahun {{ $src['year'] ?? '-' }})
            </span>
            @endforeach
        </div>
        @endif

        {{-- Daftar pasal --}}
        <div style="max-width:860px;display:flex;flex-direction:column;gap:12px">
            @forelse($pasals as $p)
            @php
                $flag = $p['flag'] ?? 'UNCHANGED';
                $bg = match($flag) {
                    'ADDED' => 'rgba(16,185,129,.12)',
                    'REMOVED' => 'rgba(239,68,68,.12)',
                    'MODIFIED' => 'rgba(245,158,11,.12)',
                    default => 'var(--bg2)',
                };
                $border = match($flag) {
                    'ADDED' => 'rgba(16,185,129,.4)',
                    'REMOVED' => 'rgba(239,68,68,.4)',
                    'MODIFIED' => 'rgba(245,158,11,.4)',
                    default => 'var(--line)',
                };
                $badge = match($flag) {
                    'ADDED' => ['✓ Ditambah', 'var(--ok)'],
                    'REMOVED' => ['✗ Dihapus', 'var(--err)'],
                    'MODIFIED' => ['✎ Diubah', 'var(--warn)'],
                    default => [null, null],
                };
            @endphp
            <div style="padding:18px;border:1px solid {{ $border }};border-radius:var(--radius);background:{{ $bg }}">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <strong style="font-size:15px">Pasal {{ $p['nomor'] }}</strong>
                    @if($p['judul'])<span style="font-size:13px;color:var(--muted)">· {{ $p['judul'] }}</span>@endif
                    @if($badge[0])
                    <span style="margin-left:auto;padding:2px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $badge[1] }};color:#fff">{{ $badge[0] }}</span>
                    @endif
                </div>
                <div style="font-size:14px;line-height:1.7;color:var(--text);white-space:pre-line">{{ $p['teks'] }}</div>
                @if(!empty($p['catatan']))
                <div style="margin-top:10px;font-size:12px;color:var(--muted);font-style:italic">📝 {{ $p['catatan'] }}</div>
                @endif
            </div>
            @empty
            <div style="padding:48px;text-align:center;color:var(--muted);border:1px dashed var(--line);border-radius:var(--radius)">
                Tidak ada data pasal.
            </div>
            @endforelse
        </div>
    </div>

    <style>
    .cr-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding:24px;background:linear-gradient(135deg,var(--accent) 0%,#8b5cf6 100%);border-radius:var(--radius);color:#fff;border:1px solid color-mix(in srgb,var(--accent) 40%,transparent);box-shadow:0 8px 30px -10px var(--accent)}
    .cr-hero .page-head{flex:1;display:block;margin:0;padding:0;border:0;background:transparent}
    .cr-hero .eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.2);margin-bottom:8px;color:#fff}
    .cr-hero .page-title{margin:0 0 4px;font-size:28px;font-weight:700;letter-spacing:-.5px;color:#fff}
    .cr-hero .page-desc{margin:0;font-size:14px;opacity:.9;line-height:1.5;color:#fff}
    .cr-hero .btn-primary{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;margin-top:12px}
    </style>
</x-layouts.base>
