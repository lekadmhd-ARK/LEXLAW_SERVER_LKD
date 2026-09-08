<x-layouts.base title="{{ $putusan->nomor_putusan }}">
    <div>
        <div class="page-head">
            <div>
                <a href="/putusans" class="btn btn-secondary btn-sm mb-2" style="padding:6px 12px">← Kembali</a>
                <div class="eyebrow">⚖️ Detail Putusan</div>
                <h1 class="page-title">{{ $putusan->nomor_putusan }}</h1>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="card">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="putusan-meta">
                <div>
                    <div class="label">Pengadilan</div>
                    <div style="font-weight:600">{{ $putusan->nama_pengadilan }}</div>
                    <div class="text-muted" style="font-size:13px">{{ $putusan->panitera ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Jenis</div>
                    <span class="badge bg-info">{{ $putusan->jenis_pengadilan }}</span>
                </div>
                <div>
                    <div class="label">Golongan</div>
                    <span class="badge bg-primary">{{ $putusan->golongan_perkara }}</span>
                </div>
                <div>
                    <div class="label">Tingkat</div>
                    <span class="badge bg-secondary">{{ $putusan->tingkat_pengadilan }}</span>
                </div>
                <div>
                    <div class="label">Tanggal Putusan</div>
                    {{ $putusan->tanggal_putusan?->format('d F Y') }}
                    @if($putusan->tanggal_register)
                    <br><small class="text-muted">Register: {{ $putusan->tanggal_register->format('d F Y') }}</small>
                    @endif
                </div>
                <div>
                    <div class="label">Status</div>
                    <span class="badge {{ $putusan->status_putusan === 'Berlaku' ? 'bg-success' : 'bg-danger' }}">
                        {{ $putusan->status_putusan }}
                    </span>
                    @if($putusan->sumber_url)
                    <div style="margin-top:8px">
                        <a href="{{ $putusan->sumber_url }}" target="_blank" class="btn btn-outline-primary btn-sm" style="padding:6px 12px">Sumber Resmi</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pihak-pihak --}}
        @if($putusan->para_pihak)
        <div class="card">
            <div class="eyebrow" style="margin-bottom:8px">Para Pihak</div>
            <div style="font-size:14px;line-height:1.7">{{ nl2br(e($putusan->para_pihak)) }}</div>
        </div>
        @endif

        {{-- Ringkasan --}}
        @if($putusan->ringkasan_putusan)
        <div class="card">
            <div class="eyebrow" style="margin-bottom:8px">Ringkasan Putusan (Headnote)</div>
            <div style="font-size:14px;line-height:1.7">{{ nl2br(e($putusan->ringkasan_putusan)) }}</div>
        </div>
        @endif

        {{-- Isi Putusan --}}
        @if($putusan->isi_putusan)
        <div class="card">
            <div class="eyebrow" style="margin-bottom:8px">Isi Putusan Lengkap</div>
            <div style="white-space: pre-wrap; font-family: 'Georgia', serif; line-height: 1.8; font-size:14px">
                {{ $putusan->isi_putusan }}
            </div>
        </div>
        @endif

        {{-- Pasal Dikutip --}}
        @if(!empty($putusan->pasal_dikutip))
        <div class="card">
            <div class="eyebrow" style="margin-bottom:8px">Pasal/Pasal yang Dikutip</div>
            <ul style="padding-left:20px;font-size:14px;line-height:1.7">
                @foreach($putusan->pasal_dikutip as $pasal)
                <li>{{ $pasal }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Putusan Terkait --}}
        @if($related->isNotEmpty())
        <div class="card">
            <div class="eyebrow" style="margin-bottom:12px">Putusan Terkait</div>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
                @foreach($related as $r)
                <div style="border:1px solid var(--line);border-radius:10px;padding:12px 14px">
                    <a href="/putusans/{{ $r->id }}"><strong>{{ $r->nomor_putusan }}</strong></a>
                    <div class="text-muted" style="font-size:12px">{{ $r->nama_pengadilan }} | {{ $r->golongan_perkara }} | {{ $r->tanggal_putusan?->format('d M Y') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts.base>