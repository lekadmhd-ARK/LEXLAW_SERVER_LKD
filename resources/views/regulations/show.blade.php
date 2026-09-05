<x-layouts.base title="{{ $regulation->title }}">
    <div>
        <div class="page-head">
            <div>
                <a href="/regulations" class="btn btn-secondary" style="margin-bottom:12px;display:inline-flex">← Kembali ke Daftar</a>
                <div class="eyebrow">⚖️ Detail Regulasi</div>
                <h1 class="page-title">{{ $regulation->title }}</h1>
                <p class="page-desc">{{ $regulation->hierarchy_label }} • Sektor: {{ $regulation->sector_label }} • Tahun {{ $regulation->year }}</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="/regulations/{{ $regulation->id }}/edit" class="btn btn-secondary">✏️ Edit</a>
                <form action="/regulations/{{ $regulation->id }}" method="POST" onsubmit="return confirm('Hapus regulasi ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary" style="color:var(--error);border-color:var(--error)">🗑️ Hapus</button>
                </form>
            </div>
        </div>

        {{-- METADATA CARD --}}
        <div class="card" style="margin-bottom:16px">
            <div style="font-size:14px;font-weight:600;color:var(--accent);margin-bottom:16px;text-transform:uppercase">📋 Informasi & Metadata Resmi</div>
            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px">
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Nomor & Tahun</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $regulation->number ? 'No. '.$regulation->number : '—' }} / {{ $regulation->year ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Tingkat Hierarki</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $regulation->hierarchy_label }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Kategori Sektor</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $regulation->sector_label }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Tanggal Penetapan</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $regulation->penetapan_date ? $regulation->penetapan_date->format('d F Y') : '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Tanggal Pengundangan</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px">{{ $regulation->pengundangan_date ? $regulation->pengundangan_date->format('d F Y') : '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase">Status Keberlakuan</div>
                    <div style="margin-top:2px">
                        <span style="padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600;background:{{ $regulation->is_active ? '#22c55e20' : '#ef444420' }};color:{{ $regulation->is_active ? '#22c55e' : '#ef4444' }}">
                            {{ $regulation->is_active ? '✓ Masih Berlaku' : '✗ Dicabut / Tidak Berlaku' }}
                        </span>
                    </div>
                </div>
            </div>

            @if($regulation->source_url)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line);font-size:12px">
                <span style="color:var(--text-muted)">Sumber Resmi:</span>
                <a href="{{ $regulation->source_url }}" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:underline;margin-left:6px">
                    {{ $regulation->source_url }} ↗
                </a>
            </div>
            @endif
        </div>

        {{-- STATUS HUKUM & RELASI --}}
        @if($regulation->derogatLegi || $regulation->revokedBy->count() > 0)
        <div class="card" style="margin-bottom:16px;border-left:4px solid #f59e0b">
            <div style="font-size:14px;font-weight:600;color:#f59e0b;margin-bottom:12px;text-transform:uppercase">🔄 Status Hukum & Relasi Peraturan</div>
            
            @if($regulation->derogatLegi)
            <div style="margin-bottom:10px">
                <span style="font-size:12px;color:var(--text-muted)">Diubah / Dicabut oleh:</span>
                <a href="/regulations/{{ $regulation->derogatLegi->id }}" style="font-weight:600;color:var(--accent);margin-left:6px">
                    {{ $regulation->derogatLegi->title }} ({{ $regulation->derogatLegi->hierarchy_label }}) ↗
                </a>
            </div>
            @endif

            @if($regulation->revokedBy->count() > 0)
            <div>
                <span style="font-size:12px;color:var(--text-muted)">Mencabut / Mengubah:</span>
                <ul style="margin-top:6px;padding-left:20px;font-size:13px">
                    @foreach($regulation->revokedBy as $rev)
                    <li>
                        <a href="/regulations/{{ $rev->id }}" style="color:var(--accent)">
                            {{ $rev->title }} ({{ $rev->hierarchy_label }})
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        {{-- ABSTRAKSI --}}
        @if($regulation->short_description)
        <div class="card" style="margin-bottom:16px">
            <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:8px">Abstraksi / Ringkasan</div>
            <div style="font-size:13px;line-height:1.6;color:var(--text-muted)">
                {{ $regulation->short_description }}
            </div>
        </div>
        @endif

        {{-- ISI DOKUMEN --}}
        <div class="card" style="margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:12px">
                <div style="font-size:14px;font-weight:600;color:var(--text)">Naskah Peraturan</div>
                <div style="display:flex;gap:8px">
                    {{-- OPSI 1: DOWNLOAD PDF RESMI DARI SITUS PEMERINTAH --}}
                    @if($regulation->pdf_url)
                    <a href="{{ $regulation->pdf_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding:8px 16px;background:#16a34a">
                        📥 Download PDF Resmi ↗
                    </a>
                    @else
                    <span style="padding:8px 16px;color:var(--text-muted);font-size:13px">PDF resmi tidak tersedia</span>
                    @endif
                </div>
            </div>

            @if($regulation->content_text)
            <div style="font-family:Georgia,serif;font-size:14px;line-height:1.8;white-space:pre-wrap;color:var(--text)">{{ $regulation->content_text }}</div>
            @else
            <div style="padding:32px;text-align:center;color:var(--text-muted)">
                Naskah lengkap belum diinput. Anda dapat mengunduh dokumen resmi melalui tombol di atas atau mengedit via form.
            </div>
            @endif
        </div>
    </div>
</x-layouts.base>