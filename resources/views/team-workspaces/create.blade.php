<x-layouts.base>
@section('title', 'Workspace Baru')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">▣ Workspace Baru</div>
            <h1 class="page-title">Buat Team Workspace</h1>
            <p class="page-desc">Buat ruang kerja baru untuk kolaborasi tim</p>
        </div>
        <a href="{{ route('team-workspaces.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card" style="max-width:640px">
        <form method="POST" action="{{ route('team-workspaces.store') }}" style="display:flex;flex-direction:column;gap:16px">
            @csrf

            <div>
                <label class="label" for="name">Nama Workspace <span style="color:var(--err)">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="mis. Perkara PT ABC vs XYZ">
                @error('name')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="label" for="type">Tipe Workspace</label>
                <select id="type" name="type">
                    <option value="">-- Pilih Tipe --</option>
                    @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="label" for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" maxlength="1000" placeholder="Deskripsi singkat workspace, tujuan, atau catatan">{{ old('description') }}</textarea>
                @error('description')<div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:8px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Buat Workspace</button>
                <a href="{{ route('team-workspaces.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</x-layouts.base>
