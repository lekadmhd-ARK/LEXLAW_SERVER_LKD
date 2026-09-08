<x-layouts.base title="{{ $putusan->nomor_putusan }}">
    <div>
        <div class="page-head">
            <div>
                <a href="/putusans" class="btn btn-secondary btn-sm mb-2" style="padding:6px 12px">← Kembali</a>
                <div class="eyebrow">⚖️ Detail Putusan</div>
                <h1 class="page-title">{{ $putusan->nomor_putusan }}</h1>
            </div>
        </div>

        {{-- AI Analysis --}}
        <div class="card" id="pa-analisis-card" style="border-left:3px solid var(--accent)">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div class="eyebrow">✦ Analisa AI Yurisprudensi</div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="btn btn-primary" id="pa-btn">
                        <span class="pa-spinner" style="display:none"></span>
                        <span class="pa-btn-text">✨ Analisa dengan AI</span>
                    </button>
                </div>
            </div>
            <p class="text-muted" style="font-size:12px;margin-top:6px;line-height:1.6">AI akan merangkum putusan ini: ringkasan eksekutif, prinsip hukum (ratione decidendi), kaidah yurisprudensi, pasal kunci, dan implikasinya.</p>
            <div id="pa-result" style="display:none">
                <div class="pa-meta" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin:14px 0 10px">
                    <span class="pa-timestamp text-muted" style="font-size:12px"></span>
                    <div style="display:flex;gap:8px">
                        <button type="button" class="btn btn-secondary btn-sm" id="pa-copy" style="padding:5px 12px">📋 Salin</button>
                    </div>
                </div>
                <div id="pa-body" style="font-size:14px;line-height:1.75;white-space:pre-wrap"></div>
                <div class="pa-disclaimer text-muted" style="font-size:11px;margin-top:14px;padding:10px 12px;background:var(--accent-bg);border-radius:8px">
                    ⚠️ Analisis ini dihasilkan AI untuk referensi awal dan bukan nasihat hukum resmi. Verifikasi dengan sumber resmi dan konsultasikan dengan praktisi hukum.
                </div>
            </div>
            <div id="pa-loading" style="display:none;padding:16px;text-align:center;color:var(--muted)">
                <span>⏳ AI sedang menganalisis putusan ini…</span>
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

    <style>
    .pa-spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:paSpin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px}
    @keyframes paSpin{to{transform:rotate(360deg)}}
    </style>

    <script>
    (() => {
        const btn = document.getElementById('pa-btn');
        const btnText = btn.querySelector('.pa-btn-text');
        const spinner = btn.querySelector('.pa-spinner');
        const result = document.getElementById('pa-result');
        const loading = document.getElementById('pa-loading');
        const body = document.getElementById('pa-body');
        const ts = document.querySelector('.pa-timestamp');
        const copyBtn = document.getElementById('pa-copy');

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btnText.textContent = 'Menganalisis…';
            spinner.style.display = 'inline-block';
            result.style.display = 'none';
            loading.style.display = 'block';
            try {
                const res = await fetch('{{ route("putusans.analyze", $putusan->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Server error');
                body.textContent = data.answer || '';
                ts.textContent = 'Dihasilkan: ' + (data.generated_at || new Date().toLocaleString());
                loading.style.display = 'none';
                result.style.display = 'block';
                result.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) {
                loading.style.display = 'none';
                result.style.display = 'block';
                body.textContent = '⚠️ ' + (err.message || 'Terjadi kesalahan saat analisis.');
            } finally {
                btn.disabled = false;
                btnText.textContent = '✨ Analisa dengan AI';
                spinner.style.display = 'none';
            }
        });

        copyBtn.addEventListener('click', async () => {
            await navigator.clipboard.writeText(body.textContent);
            copyBtn.textContent = '✅ Tersalin';
            setTimeout(() => { copyBtn.textContent = '📋 Salin'; }, 1500);
        });
    })();
    </script>
</x-layouts.base>