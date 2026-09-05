<x-layouts.base>
@section('title', 'Team Workspaces')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">▣ Tim</div>
            <h1 class="page-title">Team Workspaces</h1>
            <p class="page-desc">Kolaborasi antar anggota tim untuk menangani kasus hukum bersama</p>
       </div>
        <a href="/team-workspaces/create" class="btn btn-primary">+ Workspace Baru</a>
   </div>

    @if(session('success'))
    <div class="alert" style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success')</div>
    @endif

    <div class="card">
        <div style="overflow-x:auto">
        <table class="table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1px solid var(--line);text-align:left">
                    <th style="padding:10px 12px">Nama</th>
                    <th style="padding:10px 12px">Tipe</th>
                    <th style="padding:10px 12px">Status</th>
                    <th style="padding:10px 12px">Anggota</th>
                    <th style="padding:10px 12px;text-align:right">Aksi</th>
               </tr>
           </thead>
            <tbody>
                @forelse($items as $w)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px">
                        <div style="font-weight:600;color:var(--text)">{{ $w->name ?? $w->folder_name</div>
                        <div style="font-size:12px;color:var(--text-muted)">{{ $w->description ?? $w->shared_notes ?? ''</div>
                   </td>
                    <td style="padding:12px">
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">
                            {{ $w->type ?? '—' }}
                       </span>
                   </td>
                    <td style="padding:12px">
                        @php $st = $w->status ?? 'active'; @endphp
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ $st === 'active' ? '#22c55e20' : '#94a3b820' }};color:{{ $st === 'active' ? '#22c55e' : '#94a3b8' }}">
                            {{ ucfirst($st) }}
                       </span>
                   </td>
                    <td style="padding:12px;color:var(--text-muted)">{{ $w->member_count ?? '—'</td>
                    <td style="padding:12px;text-align:right">
                        <a href="/team-workspaces/{{ $w->id }}" style="color:var(--accent);text-decoration:none;font-size:13px">Lihat</a>
                        <a href="/team-workspaces/{{ $w->id }}/edit" style="color:var(--accent);text-decoration:none;font-size:13px;margin-left:8px">Edit</a>
                   </td>
               </tr>
                @empty
                <tr><td colspan="5" style="padding:32px;text-align:center;color:var(--text-muted)">Belum ada workspace</td</tr>
                @endforelse
           </tbody>
       </table>
       </div>
        <div style="margin-top:16px;color:var(--text-muted);font-size:13px">{{ $items->links</div>
   </div>
</div>
</x-layouts.base>