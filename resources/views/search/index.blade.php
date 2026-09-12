<x-layouts.base>
@section('title', 'Pencarian')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🔍 Pencarian</div>
            <h1 class="page-title">Cari di Workspace</h1>
        </div>
    </div>

    <div class="card" style="max-width:800px">
        <form method="GET" action="{{ route('search') }}" style="margin-bottom:20px">
            <div style="display:flex;gap:8px">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari dokumen, catatan, tugas..." style="flex:1" autofocus>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            <div style="display:flex;gap:8px;margin-top:8px">
                <a href="{{ route('search', ['q' => $q]) }}" class="btn {{ !$type ? 'btn-primary' : 'btn-secondary' }}" style="font-size:12px">Semua</a>
                <a href="{{ route('search', ['q' => $q, 'type' => 'documents']) }}" class="btn {{ $type === 'documents' ? 'btn-primary' : 'btn-secondary' }}" style="font-size:12px">Dokumen</a>
                <a href="{{ route('search', ['q' => $q, 'type' => 'notes']) }}" class="btn {{ $type === 'notes' ? 'btn-primary' : 'btn-secondary' }}" style="font-size:12px">Catatan</a>
                <a href="{{ route('search', ['q' => $q, 'type' => 'tasks']) }}" class="btn {{ $type === 'tasks' ? 'btn-primary' : 'btn-secondary' }}" style="font-size:12px">Tugas</a>
            </div>
        </form>

        @if($q)
            @if($results->count())
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($results as $r)
                <a href="{{ $r->url }}" style="padding:12px;border:1px solid var(--line);border-radius:8px;display:flex;align-items:center;gap:12px;color:var(--text)">
                    <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--accent-bg);color:var(--accent);flex-shrink:0">{{ $r->type_label }}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $r->title }}</div>
                        <div style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $r->excerpt }}</div>
                    </div>
                    <div style="font-size:11px;color:var(--muted);flex-shrink:0;text-align:right">
                        <div>{{ $r->sub }}</div>
                        <div>{{ $r->date }}</div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div style="padding:32px;text-align:center;color:var(--muted)">Tidak ada hasil untuk "{{ $q }}".</div>
            @endif
        @else
        <div style="padding:32px;text-align:center;color:var(--muted)">Ketik kata kunci untuk mencari.</div>
        @endif
    </div>
</div>
</x-layouts.base>
