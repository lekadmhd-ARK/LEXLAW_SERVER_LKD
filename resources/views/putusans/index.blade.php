<x-layouts.base title="Putusan">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">⚖️ Database Putusan</div>
                <h1 class="page-title">Putusan Mahkamah Agung & Peradilan</h1>
                <p class="page-desc">Pustaka putusan hukum Indonesia — yurisprudensi, analisis pasal, dan status perkara</p>
            </div>
            <a href="/putusans/create" class="btn btn-primary">+ Tambah Putusan</a>
        </div>
       </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <small>Total Putusan</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['ma']) }}</h3>
                    <small>Mahkamah Agung</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['pt']) }}</h3>
                    <small>Pengadilan Tinggi</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['pn']) }}</h3>
                    <small>Pengadilan Negeri</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['ptun']) }}</h3>
                    <small>PTUN</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card" style="padding:16px;margin-bottom:16px">
        <form method="GET" action="/putusans" id="filterForm" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
            <div>
                <label class="label" style="font-size:12px">Cari Putusan</label>
                <input type="text" name="q" class="input" placeholder="Nomor putusan, pihak, ringkasan..." value="{{ request('q') }}" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg)">
            </div>
            <div>
                <label class="label" style="font-size:12px">Jenis Pengadilan</label>
                <select name="jenis_pengadilan" class="input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg)">
                    <option value="">Semua</option>
                    <option value="MA" {{ request('jenis_pengadilan') === 'MA' ? 'selected' : '' }}>MA</option>
                    <option value="PT" {{ request('jenis_pengadilan') === 'PT' ? 'selected' : '' }}>PT</option>
                    <option value="PN" {{ request('jenis_pengadilan') === 'PN' ? 'selected' : '' }}>PN</option>
                    <option value="PTUN" {{ request('jenis_pengadilan') === 'PTUN' ? 'selected' : '' }}>PTUN</option>
                </select>
            </div>
            <div>
                <label class="label" style="font-size:12px">Golongan</label>
                <select name="golongan_perkara" class="input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg)">
                    <option value="">Semua</option>
                    <option value="Pidana" {{ request('golongan_perkara') === 'Pidana' ? 'selected' : '' }}>Pidana</option>
                    <option value="Perdata" {{ request('golongan_perkara') === 'Perdata' ? 'selected' : '' }}>Perdata</option>
                    <option value="TUN" {{ request('golongan_perkara') === 'TUN' ? 'selected' : '' }}>TUN</option>
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary" style="padding:10px 16px">Filter</button>
                <a href="/putusans" class="btn btn-secondary" style="padding:10px 16px">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-scroll">
            <table class="table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Nomor Putusan</th>
                        <th>Pengadilan</th>
                        <th>Golongan</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Pihak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($putusans as $p)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $p->nomor_putusan }}</div>
                            <div class="text-muted" style="font-size:12px">{{ $p->panitera }}</div>
                        </td>
                        <td>{{ $p->nama_pengadilan }}</td>
                        <td><span class="badge bg-info">{{ $p->golongan_perkara }}</span></td>
                        <td><span class="badge bg-secondary">{{ $p->tingkat_pengadilan }}</span></td>
                        <td>{{ $p->tanggal_putusan?->format('d M Y') }}</td>
                        <td>{{ Str::limit($p->para_pihak ?? '-', 40) }}</td>
                        <td>
                            <a href="/putusans/{{ $p->id }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">Tidak ada putusan ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $putusans->links() }}</div>
    </div>
</div>
</x-layouts.base>