<x-layouts.base>
@section('title', 'Konsolidasi')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🔄 Konsolidasi</div>
            <h1 class="page-title">Konsolidasi Regulasi</h1>
            <p class="page-desc">Daftar konsolidasi regulasi aktif untuk dokumentasi hukum terpadu</p>
       </div>
        <a href="/consolidations/create" class="btn btn-primary">+ Buat Konsolidasi</a>
   </div>

    @if(session('success'))
    <div class="alert" style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success')</div>
    @endif

    <div class="card">
        <div style="overflow-x:auto">
        <table class="table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1px solid var(--line);text-align:left">
                    <th style="padding:10px 12px">Topik</th>
                    <th style="padding:10px 12px">Status</th>
                    <th style="padding:10px 12px">Diperbarui</th>
                    <th style="padding:10px 12px;text-align:right">Aksi</th>
               </tr>
           </thead>
            <tbody>
                @forelse($items as $c)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px">
                        <div style="font-weight:600;color:var(--text)">{{ $c->title</div>
                        <div style="font-size:12px;color:var(--text-muted)">{{ $c->description ?? ''</div>
                   </td>
                    <td style="padding:12px">
                        @php $st = $c->status ?? 'draft'; @endphp
                        <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ $st === 'final' ? '#22c55e20' : '#f59e0b20' }};color:{{ $st === 'final' ? '#22c55e' : '#f59e0b' }}">
                            {{ ucfirst($st) }}
                       </span>
                   </td>
                    <td style="padding:12px;color:var(--text-muted)">{{ $c->updated_at?->diffForHumans() ?? '—'</td>
                    <td style="padding:12px;text-align:right">
                        <a href="/consolidations/{{ $c->id }}" style="color:var(--accent);text-decoration:none;font-size:13px">Lihat</a>
                        <a href="/consolidations/{{ $c->id }}/edit" style="color:var(--accent);text-decoration:none;font-size:13px;margin-left:8px">Edit</a>
                   </td>
               </tr>
                @empty
                <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--text-muted)">Belum ada konsolidasi</td</tr>
                @endforelse
           </tbody>
       </table>
       </div>
        <div style="margin-top:16px;color:var(--text-muted);font-size:13px">{{ $items->links()</div>
   </div>
</div>
</x-layouts.base>