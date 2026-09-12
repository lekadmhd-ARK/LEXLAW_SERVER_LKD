<x-layouts.base>
@section('title', 'Edit Workspace')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">▣ Edit Workspace</div>
            <h1 class="page-title">{{ $w->name }}</h1>
            <p class="page-desc">Ubah detail workspace dan kelola anggota tim</p>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('team-workspaces.show', $w) }}" class="btn btn-secondary">Lihat</a>
            <a href="{{ route('team-workspaces.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    @if(session('success'))
    <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
        <!-- Detail workspace -->
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Detail Workspace</h3>
            <form method="POST" action="{{ route('team-workspaces.update', $w) }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf
                @method('PUT')

                <div>
                    <label class="label" for="name">Nama Workspace <span style="color:var(--err)">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $w->name) }}" required maxlength="255">
                    @error('name')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="label" for="type">Tipe Workspace</label>
                    <select id="type" name="type">
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $w->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000">{{ old('description', $w->description) }}</textarea>
                    @error('description')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" name="is_active" value="1" {{ $w->is_active ? 'checked' : '' }} style="width:auto">
                        Workspace aktif
                    </label>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <!-- Sidebar: info + hapus -->
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <h4 style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:.3px">Info</h4>
                <div style="font-size:13px;color:var(--muted);display:flex;flex-direction:column;gap:8px">
                    <div>Dibuat: <strong style="color:var(--text)">{{ $w->created_at ? $w->created_at->format('d M Y H:i') : '—' }}</strong></div>
                    @if($w->creator)
                    <div>Pembuat: <strong style="color:var(--text)">{{ $w->creator->name }}</strong></div>
                    @endif>
                    <div>Anggota: <strong style="color:var(--text)">{{ $w->member_count ?? $members->count() }}</strong></div>
                </div>
            </div>

            @if($members->count())
            <div class="card">
                <h4 style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:.3px">Anggota ({{ $members->count() }})</h4>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @foreach($members as $m)
                    <div style="display:flex;align-items:center;gap:8px;font-size:13px">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:var(--accent)">{{ strtoupper(substr($m->name, 0, 2)) }}</div>
                        <div style="flex:1;min-width:0">
                            <div style="color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $m->name }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ $m->pivot->role }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card" style="border-color:var(--err)">
                <h4 style="font-size:13px;font-weight:600;color:var(--err);margin-bottom:12px">Hapus Workspace</h4>
                <p style="font-size:12px;color:var(--muted);margin-bottom:12px">Workspace yang dihapus tidak dapat dikembalikan.</p>
                <form method="POST" action="{{ route('team-workspaces.destroy', $w) }}" onsubmit="return confirm('Hapus workspace ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="background:var(--err);color:#fff;width:100%">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
</x-layouts.base>
