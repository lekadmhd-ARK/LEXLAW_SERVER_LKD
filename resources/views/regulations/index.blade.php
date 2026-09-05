<x-layouts.base title="Regulasi">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">⚖️ Manajemen Regulasi</div>
                <h1 class="page-title">Peraturan Perundang-undangan</h1>
                <p class="page-desc">Pustaka lengkap UU, PP, Perpres, PerMen, Perda — hierarki, filter, pencarian lanjutan</p>
            </div>
            <a href="/regulations/create" class="btn btn-primary">+ Tambah Regulasi</a>
        </div>

        {{-- Sumber Data & Hak Cipta --}}
        <div class="card" style="padding:16px;margin-bottom:16px">
            <div style="font-size:12px;font-weight:600;color:var(--accent);text-transform:uppercase;margin-bottom:8px">📖 Sumber Data & Hak Cipta</div>
            <div style="font-size:12px;line-height:1.6;color:var(--muted)">
                <p style="margin-bottom:6px"><strong>Sumber Data Utama</strong> — Data regulasi bersumber langsung dari dokumen resmi milik pemerintah Indonesia (Lembaran Negara, Direktori Hukum Kementerian, JDIH Nasional). Dokumen publik resmi ini tidak dilindungi oleh hak cipta.</p>
                <p style="margin-bottom:6px"><strong>Hak Cipta Peraturan</strong> — Peraturan perundang-undangan Indonesia bersifat public domain — sah secara hukum untuk disalin, disebarluaskan, dan dimasukkan ke dalam database aplikasi.</p>
                <p style="margin-bottom:6px"><strong>Cara Pengumpulan Data</strong> — Manual (download PDF resmi) atau otomatis (crawler ke portal JDIH/situs kementerian).</p>
                <p><strong>Tanggung Jawab Pengembang</strong> — Aplikasi ini adalah penyedia mesin pengindeks dan kurator, bukan pembuat aturan.</p>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid-4" style="margin-bottom:16px">
            <div class="card stat"><div class="num">{{ $stats['total'] }}</div><div class="label">Total</div></div>
            <div class="card stat"><div class="num">{{ $stats['uu'] }}</div><div class="label">UU</div></div>
            <div class="card stat"><div class="num">{{ $stats['pp'] }}</div><div class="label">PP</div></div>
            <div class="card stat"><div class="num">{{ $stats['perpres'] }}</div><div class="label">Perpres</div></div>
        </div>

        {{-- Filter --}}
        <div class="card" style="padding:16px;margin-bottom:16px">
            <form method="GET" action="/regulations" id="filterForm" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
                <div>
                    <label class="label" style="font-size:12px">Pencarian</label>
                    <input type="text" name="q" id="globalSearch" value="{{ request('q') }}" placeholder="Cari judul/nomor/deskripsi..." style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                </div>
                <div>
                    <label class="label" style="font-size:12px">Hierarki</label>
                    <select name="hierarchy" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua</option>
                        <option value="1" {{ request('hierarchy')=='1'?'selected':'' }}>UU</option>
                        <option value="2" {{ request('hierarchy')=='2'?'selected':'' }}>PP</option>
                        <option value="3" {{ request('hierarchy')=='3'?'selected':'' }}>Perpres</option>
                        <option value="4" {{ request('hierarchy')=='4'?'selected':'' }}>PerMen</option>
                        <option value="5" {{ request('hierarchy')=='5'?'selected':'' }}>Perda</option>
                    </select>
                </div>
                <div>
                    <label class="label" style="font-size:12px">Sektor</label>
                    <select name="sector" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua</option>
                        <option value="ketenagakerjaan" {{ request('sector')=='ketenagakerjaan'?'selected':'' }}>Ketenagakerjaan</option>
                        <option value="perpajakan" {{ request('sector')=='perpajakan'?'selected':'' }}>Perpajakan</option>
                        <option value="perusahaan" {{ request('sector')=='perusahaan'?'selected':'' }}>Perusahaan</option>
                        <option value="agraria" {{ request('sector')=='agraria'?'selected':'' }}>Agraria</option>
                        <option value="teknologi" {{ request('sector')=='teknologi'?'selected':'' }}>Teknologi Informasi</option>
                        <option value="lainnya" {{ request('sector')=='lainnya'?'selected':'' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="label" style="font-size:12px">Status</label>
                    <select name="active" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text)">
                        <option value="">Semua</option>
                        <option value="1" {{ request('active')=='1'?'selected':'' }}>Aktif</option>
                        <option value="0" {{ request('active')=='0'?'selected':'' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">🔍 Filter</button>
                    <a href="/regulations" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        {{-- DataTables --}}
        <div class="card">
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
                            <a href="/regulations/{{ $r->id }}/edit" class="btn btn-secondary" style="padding:4px 10px;font-size:12px;margin-left:4px">Edit</a>
                        </td>
                    </tr>
                @empty
                @endforelse
                </tbody>
            </table>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(function(){
        $('#regulationsTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[1, 'asc']],
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
