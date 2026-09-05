<x-layouts.base>
@section('title', 'Legal Glossary')
<div>
    <div class="page-head">
        <div><div class="eyebrow">📚 Legal Glossary</div><h1 class="page-title">Kamus Hukum Indonesia</h1><p class="page-desc">Istilah hukum formal lengkap untuk para praktisi hukum</p></div>
        <a href="/legal-glossary/create" class="btn btn-primary">+ Tambah Istilah</a>
    </div>

    @if(session('success'))
    <div class="alert" style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
            <input type="text" id="glossSearch" placeholder="Cari istilah..." style="flex:1;min-width:200px;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
            <select id="glossCat" style="padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                <option value="">Semua Kategori</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div style="overflow-x:auto">
        <table class="table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1px solid var(--line);text-align:left">
                    <th style="padding:10px 12px">Istilah</th>
                    <th style="padding:10px 12px">Definisi</th>
                    <th style="padding:10px 12px">Kategori</th>
                    <th style="padding:10px 12px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $g)
                <tr data-search="{{ strtolower($g->term.' '.$g->definition) }}" data-cat="{{ $g->category ?? '' }}" style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px"><strong>{{ $g->term }}</strong></td>
                    <td style="padding:12px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $g->definition }}</td>
                    <td style="padding:12px"><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">{{ $g->category ?? '—' }}</span></td>
                    <td style="padding:12px;text-align:right">
                        <a href="/legal-glossary/{{ $g->id }}" style="color:var(--accent);text-decoration:none;font-size:13px">Lihat</a>
                        <a href="/legal-glossary/{{ $g->id }}/edit" style="color:var(--accent);text-decoration:none;font-size:13px;margin-left:8px">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--text-muted)">Belum ada istilah.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div style="margin-top:16px;color:var(--text-muted);font-size:13px">{{ $items->links()</div>
    </div>
</div>

<script>
document.getElementById('glossSearch').addEventListener('keyup', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('table tbody tr[data-search]').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});
document.getElementById('glossCat').addEventListener('change', function() {
    const c = this.value;
    document.querySelectorAll('table tbody tr[data-cat]').forEach(row => {
        if (!c || row.dataset.cat === c) row.style.display = ''; else row.style.display = 'none';
    });
});
</script>
</x-layouts.base>