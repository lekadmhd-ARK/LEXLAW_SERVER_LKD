<x-layouts.base title="Edit Regulasi">
    <div>
        <div class="page-head">
            <div>
                <div class="eyebrow">⚖️ Manajemen Regulasi</div>
                <h1 class="page-title">Edit Regulasi</h1>
            </div>
            <a href="/regulations/{{ $regulation->id }}" class="btn btn-secondary">← Kembali</a>
        </div>

        @if(session('success'))
        <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">
            <ul style="margin:0;padding-left:20px">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- AUTO-FETCH DARI URL JDIH --}}
        <div class="card" style="padding:20px;margin-bottom:16px;border-left:4px solid var(--accent)">
            <div style="font-size:14px;font-weight:600;color:var(--accent);margin-bottom:8px">🤖 Update Data via URL JDIH / Situs Resmi</div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">AI akan mengekstrak ulang metadata dari URL dan menimpa form di bawah.</div>
            <div style="display:flex;gap:8px">
                <input type="url" id="jdih-url" value="{{ $regulation->source_url }}" placeholder="https://peraturan.bpk.go.id/Details/..." style="flex:1;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px">
                <button type="button" onclick="fetchJdih()" id="fetch-btn" class="btn btn-primary">🔍 Fetch Data Baru</button>
            </div>
            <div id="fetch-status" style="margin-top:10px;font-size:12px;color:var(--text-muted);display:none"></div>
        </div>

        <form method="POST" action="/regulations/{{ $regulation->id }}" class="card" style="padding:24px">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Judul Peraturan *</label>
                <input type="text" name="title" id="f-title" value="{{ old('title', $regulation->title) }}" required class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Nomor</label>
                    <input type="text" name="number" id="f-number" value="{{ old('number', $regulation->number) }}" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Tahun</label>
                    <input type="number" name="year" id="f-year" value="{{ old('year', $regulation->year) }}" min="1900" max="2099" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Hierarki *</label>
                    <select name="hierarchy_level" id="f-hierarchy" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                        <option value="1" {{ old('hierarchy_level', $regulation->hierarchy_level)=='1'?'selected':'' }}>Undang-Undang (UU)</option>
                        <option value="2" {{ old('hierarchy_level', $regulation->hierarchy_level)=='2'?'selected':'' }}>Peraturan Pemerintah (PP)</option>
                        <option value="3" {{ old('hierarchy_level', $regulation->hierarchy_level)=='3'?'selected':'' }}>Peraturan Presiden (Perpres)</option>
                        <option value="4" {{ old('hierarchy_level', $regulation->hierarchy_level)=='4'?'selected':'' }}>Peraturan Menteri (PerMen)</option>
                        <option value="5" {{ old('hierarchy_level', $regulation->hierarchy_level)=='5'?'selected':'' }}>Peraturan Daerah (Perda)</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Kategori Sektor</label>
                    <select name="category_sector" id="f-sector" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                        <option value="">— Pilih —</option>
                        <option value="ketenagakerjaan" {{ old('category_sector', $regulation->category_sector)=='ketenagakerjaan'?'selected':'' }}>Ketenagakerjaan</option>
                        <option value="perpajakan" {{ old('category_sector', $regulation->category_sector)=='perpajakan'?'selected':'' }}>Perpajakan</option>
                        <option value="perusahaan" {{ old('category_sector', $regulation->category_sector)=='perusahaan'?'selected':'' }}>Perusahaan</option>
                        <option value="agraria" {{ old('category_sector', $regulation->category_sector)=='agraria'?'selected':'' }}>Agraria</option>
                        <option value="teknologi" {{ old('category_sector', $regulation->category_sector)=='teknologi'?'selected':'' }}>Teknologi Informasi</option>
                        <option value="lainnya" {{ old('category_sector', $regulation->category_sector)=='lainnya'?'selected':'' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Status Hukum *</label>
                    <select name="status" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                        <option value="active" {{ old('status', $regulation->status)=='active'?'selected':'' }}>Aktif / Berlaku</option>
                        <option value="draft" {{ old('status', $regulation->status)=='draft'?'selected':'' }}>Draft</option>
                        <option value="archived" {{ old('status', $regulation->status)=='archived'?'selected':'' }}>Diarsipkan</option>
                        <option value="revoked" {{ old('status', $regulation->status)=='revoked'?'selected':'' }}>Dicabut</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Tanggal Penetapan</label>
                    <input type="date" name="penetapan_date" id="f-penetapan" value="{{ old('penetapan_date', $regulation->penetapan_date?->format('Y-m-d')) }}" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Tanggal Pengundangan</label>
                    <input type="date" name="pengundangan_date" id="f-pengundangan" value="{{ old('pengundangan_date', $regulation->pengundangan_date?->format('Y-m-d')) }}" class="form-input" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
                </div>
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Abstraksi / Ringkasan</label>
                <textarea name="short_description" id="f-short" rows="3" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">{{ old('short_description', $regulation->short_description) }}</textarea>
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">URL Sumber (Lembaran Negara / JDIH / BPK)</label>
                <input type="url" name="source_url" id="f-source" value="{{ old('source_url', $regulation->source_url) }}" class="form-input" placeholder="https://..." style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">URL PDF Resmi (opsional)</label>
                <input type="url" name="pdf_url" id="f-pdf" value="{{ old('pdf_url', $regulation->pdf_url) }}" class="form-input" placeholder="https://..." style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:14px">
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Isi Peraturan (Lengkap)</label>
                <textarea name="content_text" id="f-content" rows="12" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;font-family:Georgia,serif">{{ old('content_text', $regulation->content_text) }}</textarea>
            </div>

            <div style="margin-bottom:16px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" id="f-active" value="1" {{ old('is_active', $regulation->is_active) ? 'checked' : '' }}>
                    <span style="font-size:13px;color:var(--text)">Peraturan masih berlaku</span>
                </label>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end">
                <a href="/regulations/{{ $regulation->id }}" class="btn btn-secondary" style="padding:10px 20px">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px">💾 Update</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    async function fetchJdih() {
        const url = document.getElementById('jdih-url').value.trim();
        if (!url) { alert('Masukkan URL terlebih dahulu'); return; }

        const btn = document.getElementById('fetch-btn');
        const status = document.getElementById('fetch-status');
        btn.disabled = true;
        btn.textContent = '⏳ Mengambil data baru...';
        status.style.display = 'block';
        status.style.color = 'var(--accent)';
        status.textContent = 'AI sedang mengekstrak metadata dari URL...';

        try {
            const response = await fetch('{{ route("regulations.fetch-jdih") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ url: url }),
            });

            const result = await response.json();

            if (result.success) {
                const d = result.data;
                if (d.title) document.getElementById('f-title').value = d.title;
                if (d.number) document.getElementById('f-number').value = d.number;
                if (d.year) document.getElementById('f-year').value = d.year;
                if (d.hierarchy_level) document.getElementById('f-hierarchy').value = d.hierarchy_level;
                if (d.category_sector) document.getElementById('f-sector').value = d.category_sector;
                if (d.penetapan_date) document.getElementById('f-penetapan').value = d.penetapan_date;
                if (d.pengundangan_date) document.getElementById('f-pengundangan').value = d.pengundangan_date;
                if (d.short_description) document.getElementById('f-short').value = d.short_description;
                if (d.content_text) document.getElementById('f-content').value = d.content_text;
                if (d.source_url) document.getElementById('f-source').value = d.source_url;
                if (d.pdf_url) document.getElementById('f-pdf').value = d.pdf_url;
                if (d.is_active !== undefined) document.getElementById('f-active').checked = d.is_active;

                status.style.color = '#22c55e';
                status.textContent = '✓ Data berhasil diperbarui di form. Klik "Update" untuk menyimpan.';
            } else {
                status.style.color = '#ef4444';
                status.textContent = '✗ ' + (result.message || 'Gagal mengambil data');
            }
        } catch (err) {
            status.style.color = '#ef4444';
            status.textContent = '✗ Error: ' + err.message;
        } finally {
            btn.disabled = false;
            btn.textContent = '🔍 Fetch Data Baru';
        }
    }
   </script>
    @endpush
</x-layouts.base>