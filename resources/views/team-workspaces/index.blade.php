<x-layouts.base>
@section('title', 'Team Workspaces')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">▣ Kolaborasi Tim</div>
            <h1 class="page-title">Team Workspaces</h1>
            <p class="page-desc">Kelola ruang kerja bersama untuk menangani perkara dan dokumen hukum tim</p>
        </div>
        <a href="{{ route('team-workspaces.create') }}" class="btn btn-primary">+ Workspace Baru</a>
    </div>

    @if(session('success'))
    <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="table-scroll">
        <table class="table table-min">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Anggota</th>
                    <th>Dibuat</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $w)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--text)">{{ $w->name }}</div>
                        <div style="font-size:12px;color:var(--muted);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $w->description ?: '—' }}</div>
                    </td>
                    <td>
                        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:500;background:var(--accent-bg);color:var(--accent)">
                            {{ $w->type_name }}
                        </span>
                    </td>
                    <td>
                        <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:500;background:{{ $w->is_active ? '#22c55e20' : '#94a3b820' }};color:{{ $w->is_active ? '#22c55e' : '#94a3b8' }}">
                            {{ $w->status_label }}
                        </span>
                    </td>
                    <td style="color:var(--muted)">{{ $w->member_count }}</td>
                    <td style="font-size:12px;color:var(--muted)">
                        {{ $w->created_at ? $w->created_at->format('d M Y') : '—' }}
                        @if($w->creator)
                        <div style="font-size:11px;color:var(--muted)">{{ $w->creator->name }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="{{ route('team-workspaces.show', $w) }}" style="color:var(--accent);font-size:13px">Lihat</a>
                        <a href="{{ route('team-workspaces.edit', $w) }}" style="color:var(--accent);font-size:13px;margin-left:12px">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:48px;text-align:center;color:var(--muted)">Belum ada workspace. <a href="{{ route('team-workspaces.create') }}" style="color:var(--accent)">Buat workspace pertama</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div style="margin-top:16px;color:var(--muted);font-size:13px">{{ $items->links() }}</div>
    </div>
</div>
</x-layouts.base>
