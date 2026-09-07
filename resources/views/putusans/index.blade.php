@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">⚖️ Database Putusan</h1>
        <a href="/putusans/create" class="btn btn-primary">+ Tambah Putusan</a>
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
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" class="form-control" placeholder="Nomor, pihak, ringkasan, isi..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis Pengadilan</label>
                    <select name="jenis_pengadilan" class="form-select">
                        <option value="">Semua</option>
                        <option value="MA" {{ request('jenis_pengadilan') === 'MA' ? 'selected' : '' }}>MA</option>
                        <option value="PT" {{ request('jenis_pengadilan') === 'PT' ? 'selected' : '' }}>PT</option>
                        <option value="PN" {{ request('jenis_pengadilan') === 'PN' ? 'selected' : '' }}>PN</option>
                        <option value="PTUN" {{ request('jenis_pengadilan') === 'PTUN' ? 'selected' : '' }}>PTUN</option>
                        <option value="MA_PIDANA" {{ request('jenis_pengadilan') === 'MA_PIDANA' ? 'selected' : '' }}>MA Pidana</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Golongan Perkara</label>
                    <select name="golongan_perkara" class="form-select">
                        <option value="">Semua</option>
                        <option value="Pidana" {{ request('golongan_perkara') === 'Pidana' ? 'selected' : '' }}>Pidana</option>
                        <option value="Perdata" {{ request('golongan_perkara') === 'Perdata' ? 'selected' : '' }}>Perdata</option>
                        <option value="TUN" {{ request('golongan_perkara') === 'TUN' ? 'selected' : '' }}>TUN</option>
                        <option value="Perbankan" {{ request('golongan_perkara') === 'Perbankan' ? 'selected' : '' }}>Perbankan</option>
                        <option value="PPHI" {{ request('golongan_perkara') === 'PPHI' ? 'selected' : '' }}>PPHI</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tingkat</label>
                    <select name="tingkat_pengadilan" class="form-select">
                        <option value="">Semua</option>
                        <option value="Pertama" {{ request('tingkat_pengadilan') === 'Pertama' ? 'selected' : '' }}>Pertama</option>
                        <option value="Banding" {{ request('tingkat_pengadilan') === 'Banding' ? 'selected' : '' }}>Banding</option>
                        <option value="Kasasi" {{ request('tingkat_pengadilan') === 'Kasasi' ? 'selected' : '' }}>Kasasi</option>
                        <option value="Peninjauan Kembali" {{ request('tingkat_pengadilan') === 'Peninjauan Kembali' ? 'selected' : '' }}>Peninjauan Kembali</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="/putusans" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Putusan</th>
                            <th>Pengadilan</th>
                            <th>Golongan</th>
                            <th>Tingkat</th>
                            <th>Tanggal</th>
                            <th>Pihak</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($putusans as $p)
                        <tr>
                            <td>
                                <strong>{{ $p->nomor_putusan }}</strong>
                                @if($p->panitera)
                                <br><small class="text-muted">{{ $p->panitera }}</small>
                                @endif
                            </td>
                            <td>{{ $p->nama_pengadilan }}</td>
                            <td><span class="badge bg-info">{{ $p->golongan_perkara }}</span></td>
                            <td><span class="badge bg-secondary">{{ $p->tingkat_pengadilan }}</span></td>
                            <td>{{ $p->tanggal_putusan?->format('d M Y') }}</td>
                            <td>{{ Str::limit($p->para_pihak ?? '-', 50) }}</td>
                            <td>
                                <span class="badge {{ $p->status_putusan === 'Berlaku' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $p->status_putusan }}
                                </span>
                            </td>
                            <td>
                                <a href="/putusans/{{ $p->id }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">Tidak ada putusan ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $putusans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection