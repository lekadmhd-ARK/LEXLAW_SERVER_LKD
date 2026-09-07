<x-layouts.base title="Edit Istilah">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">📚 Glosari Hukum</div>
                <h1 class="page-title">Edit Istilah</h1>
                <p class="page-desc">{{ $legalGlossary->term }}</p>
            </div>
            <a href="/legal-glossary/{{ $legalGlossary->id }}" class="btn btn-primary">← Kembali</a>
        </div></div>

        <div style="max-width:720px">
            <form method="POST" action="/legal-glossary/{{ $legalGlossary->id }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf
                @method('PUT')
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Istilah (term) *</label>
                    <input type="text" name="term" required value="{{ $legalGlossary->term }}" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Singkatan / Alias</label>
                    <input type="text" name="singkatan" value="{{ $legalGlossary->singkatan }}" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Kategori Hukum</label>
                    <select name="kategori_hukum" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px">
                        <option value="">— Pilih Kategori —</option>
                        @foreach(['Pidana','Perdata','Bisnis','Tata Negara','Ketenagakerjaan','Properti','Perbankan','Pajak','HAM','Lingkungan','HAKI','Konstitusi'] as $c)
                        <option value="{{ $c }}" {{ $legalGlossary->kategori_hukum === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Definisi Singkat</label>
                    <input type="text" name="definisi_singkat" value="{{ $legalGlossary->definisi_singkat }}" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Penjelasan Lengkap *</label>
                    <textarea name="penjelasan_lengkap" required rows="5" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px;line-height:1.6">{{ $legalGlossary->penjelasan_lengkap }}</textarea>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Contoh Penerapan / Kasus</label>
                    <textarea name="contoh_implementasi" rows="3" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px;line-height:1.6">{{ $legalGlossary->contoh_implementasi }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="padding:12px 20px;font-size:15px">Perbarui Istilah</button>
            </form>
        </div>
    </div>

    <style>
    .cr-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding:24px;background:linear-gradient(135deg,var(--accent) 0%,#8b5cf6 100%);border-radius:var(--radius);color:#fff;border:1px solid color-mix(in srgb,var(--accent) 40%,transparent);box-shadow:0 8px 30px -10px var(--accent)}
    .cr-hero .page-head{flex:1;display:block;margin:0;padding:0;border:0;background:transparent}
    .cr-hero .eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.2);margin-bottom:8px;color:#fff}
    .cr-hero .page-title{margin:0 0 4px;font-size:28px;font-weight:700;letter-spacing:-.5px;color:#fff}
    .cr-hero .page-desc{margin:0;font-size:14px;opacity:.9;line-height:1.5;color:#fff}
    .cr-hero .btn-primary{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;margin-top:12px}
    </style>
</x-layouts.base>
