<x-layouts.base title="AI Draft Generator">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">📝 AI Legal Draft</div>
                <h1 class="page-title">Draft Document Generator</h1>
                <p class="page-desc">Generate dokumen hukum otomatis dengan format surat profesional Indonesia</p>
            </div>
            <a href="/dashboard" class="btn btn-secondary">← Kembali</a>
        </div>

        {{-- Form Input --}}
        <div class="card" style="padding:24px">
            <form action="/ai/draft" method="POST" id="draftForm">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                    <div>
                        <label class="label" style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text)">Jenis Dokumen</label>
                        <select name="document_type" id="document_type" required style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;outline:none">
                            <optgroup label="Perjanjian & Kontrak">
                                <option value="nda">Perjanjian Kerahasiaan (NDA)</option>
                                <option value="kerjasama">Perjanjian Kerjasama</option>
                                <option value="jual_beli">Perjanjian Jual Beli</option>
                                <option value="sewa_menyewa">Perjanjian Sewa Menyewa</option>
                                <option value="ppjb">Perjanjian Pengikatan Jual Beli (PPJB)</option>
                                <option value="kontrak_kerja">Perjanjian Kerja / Kontrak Kerja</option>
                                <option value="pemborongan">Perjanjian Pemborongan / Jasa</option>
                                <option value="kredit">Perjanjian Kredit / Pinjam-Meminjam</option>
                                <option value="waralaba">Perjanjian Waralaba (Franchise)</option>
                                <option value="distribusi">Perjanjian Distribusi / Keagenan</option>
                                <option value="lisensi">Perjanjian Lisensi</option>
                                <option value="mou">Nota Kesepahaman (MoU)</option>
                            </optgroup>
                            <optgroup label="Korporasi & Perusahaan">
                                <option value="akta_pendirian">Akta Pendirian PT</option>
                                <option value="akta_perubahan">Akta Perubahan PT</option>
                                <option value="sk_direksi">SK Direksi / Komisaris</option>
                                <option value="berita_acara_rups">Berita Acara RUPS</option>
                                <option value="surat_kuasa">Surat Kuasa</option>
                                <option value="surat_keputusan">Surat Keputusan (SK)</option>
                                <option value="surat_edaran">Surat Edaran</option>
                            </optgroup>
                            <optgroup label="Ketenagakerjaan / HR">
                                <option value="pkwt">PKWT</option>
                                <option value="surat_peringatan">Surat Peringatan (SP)</option>
                                <option value="pengangkatan">Surat Pengangkatan</option>
                                <option value="phk">Surat PHK</option>
                                <option value="pkb">PKB</option>
                                <option value="mutasi">Surat Mutasi</option>
                                <option value="pengunduran_diri">Surat Pengunduran Diri</option>
                            </optgroup>
                            <optgroup label="Surat Resmi">
                                <option value="permohonan">Surat Permohonan</option>
                                <option value="pemberitahuan">Surat Pemberitahuan</option>
                                <option value="undangan">Surat Undangan</option>
                                <option value="keterangan">Surat Keterangan</option>
                                <option value="rekomendasi">Surat Rekomendasi</option>
                                <option value="pernyataan">Surat Pernyataan</option>
                                <option value="surat_tugas">Surat Tugas</option>
                            </optgroup>
                            <optgroup label="Keluarga & Properti">
                                <option value="wasiat">Surat Wasiat</option>
                                <option value="pra_nikah">Perjanjian Pra-Nikah</option>
                                <option value="hibah">Perjanjian Hibah</option>
                                <option value="kuasa_jual">Surat Kuasa Jual</option>
                                <option value="pengakuan_utang">Surat Pengakuan Utang</option>
                            </optgroup>
                            <optgroup label="Litigasi & Pidana">
                                <option value="kuasa_khusus">Surat Kuasa Khusus</option>
                                <option value="gugatan">Surat Gugatan</option>
                                <option value="somasi">Somasi</option>
                                <option value="pledoi">Surat Pembelaan (Pledoi)</option>
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label class="label" style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text)">Tanggal Efektif</label>
                        <input type="date" name="effective_date" required style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;outline:none" value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="label" style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text)">Jumlah Varian</label>
                        <select name="variant_count" required style="width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;outline:none">
                            <option value="1">1 Draft</option>
                            <option value="3">3 Draft (Pilih Terbaik)</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:16px">
                    <label class="label" style="display:block;margin-bottom:6px;font-size:13px;font-weight:500;color:var(--text)">Detail & Instruksi</label>
                    <textarea name="instructions" id="instructions" rows="6" required placeholder="Jelaskan detail dokumen yang ingin dibuat..." style="width:100%;padding:14px 16px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;outline:none;resize:vertical">{{ old('instructions', $instructions ?? '') }}</textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px">
                    <button type="reset" class="btn btn-secondary" style="padding:12px 20px">Reset</button>
                    <button type="submit" id="generateBtn" class="btn btn-primary" style="padding:12px 24px;font-weight:600">✦ Generate Dokumen</button>
                </div>
            </form>
        </div>

        {{-- Variant Tabs --}}
        @if(!empty($drafts) && count($drafts) > 1)
        <div style="margin-top:24px">
            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
                @foreach($drafts as $i => $d)
                <button type="button" class="btn draft-tab {{ $i === 0 ? 'btn-primary' : 'btn-secondary' }}" data-index="{{ $i }}" style="padding:10px 20px;font-size:13px">
                    {{ $i === 0 ? '✦ Formal' : ($i === 1 ? '✦ Modern' : '✦ Komprehensif') }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Draft Content --}}
        @if(!empty($drafts) && count($drafts) > 0)
        <div class="card" style="margin-top:16px;padding:0;overflow:hidden" id="draftCard">
            <div style="padding:16px 24px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:11px;font-weight:600;color:var(--accent);text-transform:uppercase">📄 Dokumen Generated</div>
                    <div style="font-size:14px;color:var(--text);margin-top:2px">{{ $document_type ?? '' }}</div>
                </div>
                <div style="display:flex;gap:8px">
                    <a href="/ai/draft/download?content={{ urlencode($drafts[0] ?? '') }}&type={{ $document_type ?? 'draft' }}" class="btn btn-secondary" style="padding:8px 16px;font-size:13px" id="downloadBtn">📄 Download DOCX</a>
                    <button type="button" onclick="window.print()" class="btn btn-secondary" style="padding:8px 16px;font-size:13px">🖨️ Print</button>
                </div>
            </div>
            <div style="padding:32px 48px;background:#fff;color:#111;min-height:500px;font-family:'Times New Roman',Times,serif;font-size:14px;line-height:1.8" id="draftContent">
                @foreach($drafts as $i => $d)
                <div class="draft-panel" data-index="{{ $i }}" style="{{ $i === 0 ? '' : 'display:none' }}">
                    {!! $d !!}
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($error))
        <div class="card" style="margin-top:24px;border-color:#ef444440;background:#ef444410">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:36px;height:36px;border-radius:8px;background:#ef444420;display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:16px">⚠️</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#ef4444">Gagal Generate</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px">{{ $error }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
        #generateBtn:disabled { opacity: .7; cursor: not-allowed; }
        @media print {
            .sidebar, .page-head, .card:first-child, .btn { display: none !important; }
            .main-content { padding: 0 !important; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('generateBtn');
            var form = document.getElementById('draftForm');
            if (form && btn) {
                form.addEventListener('submit', function(e) {
                    if (btn.disabled) { e.preventDefault(); return; }
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span> Memproses...';
                });
            }

            // Tab switching
            document.querySelectorAll('.draft-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var idx = this.getAttribute('data-index');
                    document.querySelectorAll('.draft-tab').forEach(function(t) {
                        t.classList.remove('btn-primary');
                        t.classList.add('btn-secondary');
                    });
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-primary');

                    document.querySelectorAll('.draft-panel').forEach(function(p) {
                        p.style.display = (p.getAttribute('data-index') === idx) ? 'block' : 'none';
                    });

                    // Update download link
                    var dl = document.getElementById('downloadBtn');
                    if (dl) {
                        var content = document.querySelector('.draft-panel[data-index="'+idx+'"]').innerHTML;
                        dl.href = '/ai/draft/download?content=' + encodeURIComponent(content) + '&type={{ $document_type ?? 'draft' }}';
                    }
                });
            });
        });
    </script>
</x-layouts.base>
