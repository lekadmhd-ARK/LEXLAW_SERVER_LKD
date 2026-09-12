<x-layouts.base>
@section('title', 'Reports')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">📊 Reports</div>
            <h1 class="page-title">Analytics & Reporting</h1>
            <p class="page-desc">Overview seluruh workspace di perusahaan Anda</p>
        </div>
    </div>

    @if(!auth()->user()->isAdmin())
    <div class="card" style="max-width:640px">
        <div style="padding:24px;text-align:center;color:var(--muted)">Hanya Admin/Owner yang bisa melihat halaman ini.</div>
    </div>
    @else

    <div class="grid-4" style="margin-bottom:20px">
        <div class="card stat">
            <div class="num">{{ $companyStats['total_workspaces'] }}</div>
            <div class="label">Total Workspaces</div>
            <div class="hint">{{ $companyStats['active_workspaces'] }} aktif</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $companyStats['total_members'] }}</div>
            <div class="label">Total Anggota</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $companyStats['total_documents'] }}</div>
            <div class="label">Dokumen</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $companyStats['total_hours'] }}</div>
            <div class="label">Jam Tercatat</div>
            <div class="hint">{{ $companyStats['billable_hours'] }}h billable</div>
        </div>
    </div>

    <div class="grid-4" style="margin-bottom:20px">
        <div class="card stat">
            <div class="num">{{ $companyStats['total_tasks'] }}</div>
            <div class="label">Total Tugas</div>
        </div>
        <div class="card stat">
            <div class="num" style="color:var(--ok)">{{ $companyStats['completed_tasks'] }}</div>
            <div class="label">Tugas Selesai</div>
        </div>
        <div class="card stat">
            <div class="num" style="color:{{ $companyStats['total_tasks'] > 0 ? 'var(--accent)' : 'var(--muted)' }}">
                {{ $companyStats['total_tasks'] > 0 ? round($companyStats['completed_tasks'] / $companyStats['total_tasks'] * 100) : 0 }}%
            </div>
            <div class="label">Completion Rate</div>
        </div>
        <div class="card stat">
            <div class="num" style="color:var(--warn)">{{ $companyStats['billable_hours'] }}</div>
            <div class="label">Jam Billable</div>
        </div>
    </div>

    <div class="card" style="max-width:800px">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Per Workspace</h3>
        <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Workspace</th>
                    <th>Anggota</th>
                    <th>Dokumen</th>
                    <th>Tugas</th>
                    <th>Jam</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($workspaceStats as $ws)
                <tr>
                    <td style="font-weight:500">{{ $ws->name }}</td>
                    <td style="color:var(--muted)">{{ $ws->member_count }}</td>
                    <td style="color:var(--muted)">{{ $ws->document_count }}</td>
                    <td style="color:var(--muted)">{{ $ws->completed_tasks }}/{{ $ws->task_count }}</td>
                    <td style="color:var(--muted)">{{ round($ws->total_minutes / 60, 1) }}h</td>
                    <td style="text-align:right">
                        <a href="{{ route('reports.workspace', $ws->id) }}" style="color:var(--accent);font-size:13px">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    @endif
</div>
</x-layouts.base>
