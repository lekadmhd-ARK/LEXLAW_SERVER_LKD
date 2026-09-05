<x-layouts.base title="Dashboard">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">📊 Dashboard</div>
                <h1 class="page-title">Selamat Datang, {{ $user->name }}</h1>
                <p class="page-desc">{{ $user->company->name ?? 'Tenant' }} • {{ $user->role ?? 'User' }}</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="/regulations" class="btn btn-primary">🔍 Cari Regulasi</a>
                <a href="/ai/lex-qna" class="btn btn-secondary">✦ Tanya AI</a>
                @if(auth()->user()->hasRole('super-admin'))
                <a href="{{ route('super-admin.plans.edit') }}" class="btn btn-secondary" style="border-color:#f59e0b;color:#f59e0b">💰 Edit Harga</a>
                @endif
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid-4" style="margin-bottom:24px">
            <div class="card stat">
                <div class="num">{{ $totalRegs }}</div>
                <div class="label">Total Regulasi Terindeks</div>
                <div class="hint">UU: {{ $uuCount }} | PP: {{ $ppCount }} | Perpres: {{ $perpresCount }} | PerMen: {{ $permenCount }} | Perda: {{ $perdaCount }}</div>
            </div>
            <div class="card stat">
                <div class="num">{{ $glossaryCount }}</div>
                <div class="label">Istilah Glosarium Hukum</div>
                <div class="hint">Kamus hukum Indonesia</div>
            </div>
            <div class="card stat">
                <div class="num">—</div>
                <div class="label">Aktivitas AI Bulan Ini</div>
                <div class="hint">Draft: — | Q&A: — | Validity: —</div>
            </div>
            <div class="card stat" style="border-left:4px solid var(--accent)">
                <div class="num" style="color:var(--{{ $aiStatusColor }});text-transform:capitalize">{{ $aiStatus }}</div>
                <div class="label">Status Layanan AI</div>
                <div class="hint">Model: {{ env('AI_MODEL', 'gemini-3.5-flash') }}</div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="card" style="margin-bottom:24px">
            <div class="eyebrow">⚡ Aksi Cepat</div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:16px">
                <a href="/regulations/create" class="card" style="padding:20px;text-decoration:none;text-align:center;border:2px dashed var(--line);transition:border-color .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'">
                    <div style="font-size:28px;margin-bottom:8px">📄</div>
                    <div style="font-weight:600;color:var(--text)">Tambah Regulasi</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Auto-fetch dari JDIH/BPK</div>
                </a>
                <a href="/ai/lex-qna" class="card" style="padding:20px;text-decoration:none;text-align:center;border:2px dashed var(--line);transition:border-color .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'">
                    <div style="font-size:28px;margin-bottom:8px">✦</div>
                    <div style="font-weight:600;color:var(--text)">Lex Q&A</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Tanya hukum ke AI</div>
                </a>
                <a href="/ai/draft" class="card" style="padding:20px;text-decoration:none;text-align:center;border:2px dashed var(--line);transition:border-color .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'">
                    <div style="font-size:28px;margin-bottom:8px">✎</div>
                    <div style="font-weight:600;color:var(--text)">Draft Dokumen</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">3 varian: Formal/Modern/Komprehensif</div>
                </a>
                <a href="/ai/validity" class="card" style="padding:20px;text-decoration:none;text-align:center;border:2px dashed var(--line);transition:border-color .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'">
                    <div style="font-size:28px;margin-bottom:8px">✓</div>
                    <div style="font-weight:600;color:var(--text)">Validity Checker</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Cek sitasi pasal resmi</div>
                </a>
            </div>
        </div>

        {{-- REGULASI TERBARU --}}
        <div class="card" style="margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <div class="eyebrow">📋 Regulasi Terbaru Ditambahkan</div>
                <a href="/regulations" class="btn btn-secondary" style="font-size:12px;padding:6px 12px">Lihat Semua →</a>
            </div>
            <table class="table" style="width:100%">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Hierarki</th>
                        <th>Sektor</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($latestRegs as $r)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--text)">{{ $r->title }}</div>
                            <div style="font-size:12px;color:var(--text-muted)">{{ $r->number ? 'No. '.$r->number : '' }} {{ $r->year ? 'Tahun '.$r->year : '' }}</div>
                        </td>
                        <td>
                            <span style="padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;background:{{ $r->hierarchy_level=='1'?'#3b82f620':($r->hierarchy_level=='2'?'#8b5cf620':($r->hierarchy_level=='3'?'#ec489920':($r->hierarchy_level=='4'?'#f59e0b20':'#22c55e20'))) }};color:{{ $r->hierarchy_level=='1'?'#3b82f6':($r->hierarchy_level=='2'?'#8b5cf6':($r->hierarchy_level=='3'?'#ec4899':($r->hierarchy_level=='4'?'#f59e0b':'#22c55e'))) }}">
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
                    <tr><td colspan="6" style="padding:32px;text-align:center;color:var(--text-muted)">Belum ada regulasi. <a href="/regulations/create" style="color:var(--accent)">Tambah sekarang</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- INFO TAMBAHAN --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="card">
                <div class="eyebrow">💡 Tips Penggunaan</div>
                <ul style="margin-top:12px;font-size:13px;color:var(--text-muted);line-height:1.8;padding-left:20px">
                    <li>Gunakan <strong>Auto-Fetch</a> di halaman Tambah Regulasi untuk mengisi data otomatis dari URL JDIH/BPK/peraturan.go.id</li>
                    <li><strong>Lex Q&A</a> mempertahankan riwayat chat per sesi — gunakan tombol "Bersihkan Chat" saat perlu</li>
                    <li><strong>Draft Generator</a> menghasilkan 3 varian gaya sekaligus (Formal, Modern, Komprehensif)</li>
                    <li><strong>Validity Checker</a> mencari ke situs resmi (BPK, Kemenkumham) — bukan hanya DB lokal</li>
                    <li>Semua regulasi tersimpan dengan <strong>source_url & pdf_url resmi</strong> untuk referensi</li>
                </ul>
            </div>
            <div class="card">
                <div class="eyebrow">⚙️ Sistem & Dukungan</div>
                <ul style="margin-top:12px;font-size:13px;color:var(--text-muted);line-height:1.8;padding-left:20px">
                    <li><strong>Versi</strong> LAWLEX v2.0</li>
                    <li><strong>Cache</strong> Redis + Horizon</li>
                    <li><strong>Storage</strong> R2 (S3 Compatible</li>
                    <li><strong>Keamanan Data yang ter-Enkripsi</strong</li>
                    <li><strong>Terkoneksi dengan AI yang selalu Update</strong</li>
               </ul>
           </div>
        </div>
    </div>
</x-layouts.base>