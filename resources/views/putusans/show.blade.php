@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/putusans" class="btn btn-outline-secondary btn-sm mb-2">← Kembali</a>
            <h1 class="h3 mb-0">{{ $putusan->nomor_putusan }}</h1>
        </div>
    </div>

    {{-- Meta Info --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Pengadilan:</strong><br>
                    {{ $putusan->nama_pengadilan }}<br>
                    <small class="text-muted">{{ $putusan->panitera ?? '-' }}</small>
                </div>
                <div class="col-md-2">
                    <strong>Jenis:</strong><br>
                    <span class="badge bg-info">{{ $putusan->jenis_pengadilan }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Golongan:</strong><br>
                    <span class="badge bg-primary">{{ $putusan->golongan_perkara }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Tingkat:</strong><br>
                    <span class="badge bg-secondary">{{ $putusan->tingkat_pengadilan }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Tanggal:</strong><br>
                    {{ $putusan->tanggal_putusan?->format('d F Y') }}
                    @if($putusan->tanggal_register)
                    <br><small class="text-muted">Register: {{ $putusan->tanggal_register->format('d F Y') }}</small>
                    @endif
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Status:</strong>
                    <span class="badge {{ $putusan->status_putusan === 'Berlaku' ? 'bg-success' : 'bg-danger' }} ms-2">
                        {{ $putusan->status_putusan }}
                    </span>
                </div>
                <div class="col-md-6 text-md-end">
                    @if($putusan->sumber_url)
                    <a href="{{ $putusan->sumber_url }}" target="_blank" class="btn btn-outline-primary btn-sm">Sumber Resmi</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Pihak-pihak --}}
    @if($putusan->para_pihak)
    <div class="card mb-4">
        <div class="card-header"><strong>Para Pihak</strong></div>
        <div class="card-body">{{ nl2br(e($putusan->para_pihak)) }}</div>
    </div>
    @endif

    {{-- Ringkasan --}}
    @if($putusan->ringkasan_putusan)
    <div class="card mb-4">
        <div class="card-header"><strong>Ringkasan Putusan (Headnote)</strong></div>
        <div class="card-body">{{ nl2br(e($putusan->ringkasan_putusan)) }}</div>
    </div>
    @endif

    {{-- Isi Putusan --}}
    @if($putusan->isi_putusan)
    <div class="card mb-4">
        <div class="card-header"><strong>Isi Putusan Lengkap</strong></div>
        <div class="card-body" style="white-space: pre-wrap; font-family: 'Georgia', serif; line-height: 1.8;">
            {{ $putusan->isi_putusan }}
        </div>
    </div>
    @endif

    {{-- Pasal Dikutip --}}
    @if(!empty($putusan->pasal_dikutip))
    <div class="card mb-4">
        <div class="card-header"><strong>Pasal/Pasal yang Dikutip</strong></div>
        <div class="card-body">
            <ul>
                @foreach($putusan->pasal_dikutip as $pasal)
                <li>{{ $pasal }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Putusan Terkait --}}
    @if($related->isNotEmpty())
    <div class="card">
        <div class="card-header"><strong>Putusan Terkait</strong></div>
        <div class="card-body">
            <div class="row">
                @foreach($related as $r)
                <div class="col-md-6 mb-2">
                    <a href="/putusans/{{ $r->id }}" class="text-decoration-none">
                        <div class="p-2 border rounded">
                            <strong>{{ $r->nomor_putusan }}</strong><br>
                            <small class="text-muted">{{ $r->nama_pengadilan }} | {{ $r->golongan_perkara }} | {{ $r->tanggal_putusan?->format('d M Y') }}</small>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection