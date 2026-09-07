<x-layouts.base title="Regulasi">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">⚖️ Manajemen Regulasi</div>
                <h1 class="page-title">Peraturan Perundang-undangan</h1>
                <p class="page-desc">Pustaka lengkap UU, PP, Perpres, PerMen, Perda — hierarki, filter, pencarian lanjutan</p>
            </div>
            <a href="/regulations/create" class="btn btn-primary">+ Tambah Regulasi</a>
        </div>
       </div>

        {{-- Sumber Data & Hak Cipta --}}
                {{-- Search & Fetch Card (BPK Scraper) --}}
        <div class="card" style="padding:16px;margin-bottom:16px;border-left:4px solid var(--accent)">
            <form method="POST" action="/regulations/search-fetch" style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end">
                @csrf
                <div>
                    <label class="label" style="font-size:12px;font-weight:600">Pencarian & Fetch Regulasi Resmi (JDIH BPK)</label>
                    <input type="text" name="q" required value="{{ request('q') }}" placeholder="Ketik kata kunci/nomor/judul regulasi (contoh: UU 12 2011, PP 26 2011, Cipta Kerja)..." style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="white-space:nowrap;padding:10px 16px">
                        📥 Ambil Data Resmi (Fetch)
                    </button>
                </div>
            </form>
            <div style="font-size:11px;color:var(--muted);margin-top:6px">
                Data diambil langsung dari portal resmi JDIH BPK beserta link dokumen PDF asli dan dimasukkan otomatis ke database.
            </div>
        </div>

        {{-- Filter Data Lokal Card --}}
        <div class="card" style="padding:16px;margin-bottom:16px">
            <form method="GET" action="/regulations" id="filterForm" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
                <div>
                    <label class="label" style="font-size:12px">Filter Hierarki</label>
                    <select name="hierarchy" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua Hierarki</option>
                        <option value="1" {{ request('hierarchy')=='1'?'selected':'' }}>UU (Undang-Undang)</option>
                        <option value="2" {{ request('hierarchy')=='2'?'selected':'' }}>PP (Peraturan Pemerintah)</option>
                        <option value="3" {{ request('hierarchy')=='3'?'selected':'' }}>Perpres (Peraturan Presiden)</option>
                        <option value="4" {{ request('hierarchy')=='4'?'selected':'' }}>PerMen (Peraturan Menteri)</option>
                        <option value="5" {{ request('hierarchy')=='5'?'selected':'' }}>Perda (Peraturan Daerah)</option>
                    </select>
                </div>
                <div>
                    <label class="label" style="font-size:12px">Filter Sektor</label>
                    <select name="sector" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua Sektor</option>
                        <option value="ketenagakerjaan" {{ request('sector')=='ketenagakerjaan'?'selected':'' }}>Ketenagakerjaan</option>
                        <option value="perpajakan" {{ request('sector')=='perpajakan'?'selected':'' }}>Perpajakan</option>
                        <option value="perusahaan" {{ request('sector')=='perusahaan'?'selected':'' }}>Perusahaan</option>
                        <option value="agraria" {{ request('sector')=='agraria'?'selected':'' }}>Agraria</option>
                        <option value="teknologi" {{ request('sector')=='teknologi'?'selected':'' }}>Teknologi Informasi</option>
                        <option value="lainnya" {{ request('sector')=='lainnya'?'selected':'' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="label" style="font-size:12px">Filter Status</label>
                    <select name="active" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('active')=='1'?'selected':'' }}>Aktif / Berlaku</option>
                        <option value="0" {{ request('active')=='0'?'selected':'' }}>Dicabut / Tidak Berlaku</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-secondary" style="padding:10px 14px">🔍 Filter Lokal</button>
                    <a href="/regulations" class="btn btn-secondary" style="padding:10px 14px">Reset</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="table-scroll">
            <table id="regulationsTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Dokumen</th>
                        <th>Hierarki</th>
                        <th>Sektor</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($regulations as $i => $r)
                    <tr>
                        <td>{{ $regulations->firstItem() + $i }}</td>
                        <td>
                            <div style="font-weight:600;color:var(--text)">{{ $r->title }}</div>
                            <div style="font-size:12px;color:var(--muted)">{{ $r->number ? 'No. '.$r->number : '' }} {{ $r->year ? 'Tahun '.$r->year : '' }}</div>
                        </td>
                        <td>
                            <span style="padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;background:{{ $r->hierarchy_level=='1'?'#3b82f620':($r->hierarchy_level=='2'?'#8b5cf620':($r->hierarchy_level=='3'?'#ec489920':($r->hierarchy_level=='4'?'#f59e0b20':'#22c55e20'))) }};color:{{ $r->hierarchy_level=='1'?'#3b82f6':($r->hierarchy_level=='2'?'#8b5cf6':($r->hierarchy_level=='3'?'#ec4899':($r->hierarchy_level=='4'?'#f59e0b':'#22c55e'))) }}">
                                {{ $r->hierarchy_label }}
                            </span>
                        </td>
                        <td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">{{ $r->sector_label }}</span></td>
                        <td>{{ $r->year ?? '—' }}</td>
                        <td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ $r->is_active ? '#22c55e20' : '#ef444420' }};color:{{ $r->is_active ? '#22c55e' : '#ef4444' }}">{{ $r->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td style="text-align:right">
                            <a href="/regulations/{{ $r->id }}" class="btn btn-secondary" style="padding:4px 10px;font-size:12px">Detail</a>
                        </td>
                    </tr>
                @empty
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Info Pagination --}}
        <div style="margin-top:16px;color:var(--muted);font-size:13px;text-align:center">
            Total: <strong>{{ $stats['total'] }}</strong> regulasi | Menampilkan {{ $regulations->count() }} dari {{ $regulations->total() }} ({{ $regulations->firstItem() ?? 0 }}–{{ $regulations->lastItem() ?? 0 }})
        </div>

        {{-- Disclaimer --}}
        <div style="margin-top:24px;padding:16px;border:1px solid var(--line);border-radius:8px;font-size:11px;color:var(--muted);line-height:1.6">
            <strong>Disclaimer</strong> — Data regulasi bersumber dari dokumen resmi pemerintah Indonesia (public domain). Informasi ini bersifat referensi dan tidak menggantikan dokumen resmi. Aplikasi ini adalah kurator independen dan tidak berafiliasi dengan instansi pemerintah mana pun.
        </div>
    </div>

    @push('scripts')
    <style>
    .cr-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding:24px;background:linear-gradient(135deg,var(--accent) 0%,#8b5cf6 100%);border-radius:var(--radius);color:#fff;border:1px solid color-mix(in srgb,var(--accent) 40%,transparent);box-shadow:0 8px 30px -10px var(--accent)}
    .cr-hero .page-head{flex:1;display:block;margin:0;padding:0;border:0;background:transparent}
    .cr-hero .eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.2);margin-bottom:8px;color:#fff}
    .cr-hero .page-title{margin:0 0 4px;font-size:28px;font-weight:700;letter-spacing:-.5px;color:#fff}
    .cr-hero .page-desc{margin:0;font-size:14px;opacity:.9;line-height:1.5;color:#fff}
    .cr-hero .btn-secondary{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;margin-top:12px}
    .cr-hero .btn-secondary:hover{background:rgba(255,255,255,.25)}
    .cr-hero .btn-primary{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;margin-top:12px}
    .cr-hero .btn-primary:hover{background:rgba(255,255,255,.3)}
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(function(){
        $('#regulationsTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[1, 'asc']],
            scrollX: true,
            autoWidth: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_–_END_ dari _TOTAL_",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(disaring dari _MAX_ total)",
                paginate: { previous: "←", next: "→", first: "«", last: "»" },
                zeroRecords: "Tidak ada hasil yang cocok"
            },
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            initComplete: function() {
                var dtSearch = $('.dataTables_filter input');
                dtSearch.attr('id', 'dtGlobalSearch');
                dtSearch.attr('placeholder', 'Ketik untuk mencari di tabel...');
                $('#globalSearch').on('keyup', function(){
                    dtSearch.val($(this).val()).trigger('keyup.DT');
                });
            }
        });
    });
    </script>
    @endpush
</x-layouts.base>
