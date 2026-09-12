<x-layouts.base>
@section('title', 'Report: ' . $workspace->name)
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">📊 Report</div>
            <h1 class="page-title">{{ $workspace->name }}</h1>
            <p class="page-desc">Detail analytics workspace</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="grid-4" style="margin-bottom:20px">
        <div class="card stat">
            <div class="num">{{ $stats['documents_count'] }}</div>
            <div class="label">Dokumen</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $stats['completed_tasks'] }}/{{ $stats['total_tasks'] }}</div>
            <div class="label">Tugas Selesai</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $stats['total_hours'] }}h</div>
            <div class="label">Total Waktu</div>
        </div>
        <div class="card stat">
            <div class="num" style="color:var(--warn)">{{ $stats['billable_hours'] }}h</div>
            <div class="label">Billable</div>
        </div>
    </div>

    @if(count($memberHours))
    <div class="card" style="max-width:640px">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Jam Kerja per Anggota</h3>
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($memberHours as $name => $hours)
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:13px">
                    <span style="font-weight:500">{{ $name }}</span>
                    <span style="color:var(--muted)">{{ $hours }} jam</span>
                </div>
                <div style="height:8px;background:var(--line);border-radius:4px;overflow:hidden">
                    <div style="height:100%;width:{{ min(($hours / max($memberHours)) * 100, 100) }}%;background:var(--accent);border-radius:4px"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</x-layouts.base>
