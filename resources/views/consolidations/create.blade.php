<x-layouts.base title="Konsolidasi Baru">
    <div>
        <div class="cr-hero"><div class="page-head">
            <div>
                <div class="eyebrow">🧩 Konsolidasi Regulasi</div>
                <h1 class="page-title">Konsolidasi Baru</h1>
                <p class="page-desc">Pilih minimal 2 regulasi (induk + perubahan) untuk digabungkan.</p>
            </div>
            <a href="/consolidations" class="btn btn-primary">← Kembali</a>
        </div></div>

        @if(session('error'))
        <div style="padding:12px 16px;border-radius:8px;background:var(--err);color:#fff;margin-bottom:16px">{{ session('error') }}</div>
        @endif

        <div style="max-width:760px">
            <form method="POST" action="/consolidations" style="display:flex;flex-direction:column;gap:16px">
                @csrf
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Judul Konsolidasi *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="mis: UU Ketenagakerjaan Terkonsolidasi" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text);margin-top:6px">
                </div>

                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--muted)">Pilih Regulasi (min 2) *</label>
                    <div style="margin-top:8px;display:flex;flex-direction:column;gap:8px;max-height:360px;overflow:auto;padding:12px;border:1px solid var(--line);border-radius:var(--radius)">
                        @forelse($regulations as $reg)
                        <label style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--line);border-radius:8px;cursor:pointer;background:var(--bg2)">
                            <input type="checkbox" name="regulation_ids[]" value="{{ $reg->id }}" style="width:16px;height:16px;accent-color:var(--accent)">
                            <div>
                                <div style="font-size:14px;font-weight:600">{{ $reg->title }}</div>
                                <div style="font-size:12px;color:var(--muted)">No. {{ $reg->number }} Tahun {{ $reg->year }} · {{ $reg->hierarchy_label ?? '-' }}</div>
                            </div>
                        </label>
                        @empty
                        <div style="color:var(--muted);font-size:13px;padding:12px">Tidak ada regulasi tersimpan.</div>
                        @endforelse
                    </div>
                    @error('regulation_ids')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary" style="padding:12px 20px;font-size:15px">Generate Konsolidasi</button>
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
