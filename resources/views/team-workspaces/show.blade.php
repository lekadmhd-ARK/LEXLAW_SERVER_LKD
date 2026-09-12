<x-layouts.base>
@section('title', $w->name)
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">▣ Detail Workspace</div>
            <h1 class="page-title">{{ $w->name }}</h1>
            <p class="page-desc">{{ $w->description ?: 'Tidak ada deskripsi' }}</p>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('team-workspaces.edit', $w) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('team-workspaces.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    @if(session('success'))
    <div style="padding:12px 16px;border-radius:8px;background:#22c55e20;color:#22c55e;border:1px solid #22c55e40;margin-bottom:16px">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="padding:12px 16px;border-radius:8px;background:#ef444420;color:#ef4444;border:1px solid #ef444440;margin-bottom:16px">{{ session('error') }}</div>
    @endif

    <div class="grid-4" style="margin-bottom:20px">
        <div class="card stat">
            <div class="num">{{ $members->count() }}</div>
            <div class="label">Anggota</div>
        </div>
        <div class="card stat">
            <div class="num" style="font-size:18px">{{ $w->type_name }}</div>
            <div class="label">Tipe</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $tasks->where('status','done')->count() }}/{{ $tasks->count() }}</div>
            <div class="label">Tugas Selesai</div>
        </div>
        <div class="card stat">
            <div class="num" style="font-size:16px">{{ intdiv($w->total_minutes, 60) }}j {{ $w->total_minutes % 60 }}m</div>
            <div class="label">Total Waktu</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="display:flex;gap:4px;border-bottom:2px solid var(--line);margin-bottom:20px;flex-wrap:wrap">
        @php $currentTab = $tab; @endphp
        <a href="{{ route('team-workspaces.show', ['team_workspace' => $w, 'tab' => 'members']) }}" class="btn {{ $currentTab === 'members' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px 8px 0 0;border-bottom:none">Anggota</a>
        <a href="{{ route('team-workspaces.show', ['team_workspace' => $w, 'tab' => 'documents']) }}" class="btn {{ $currentTab === 'documents' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px 8px 0 0;border-bottom:none">Dokumen</a>
        <a href="{{ route('team-workspaces.show', ['team_workspace' => $w, 'tab' => 'notes']) }}" class="btn {{ $currentTab === 'notes' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px 8px 0 0;border-bottom:none">Catatan</a>
        <a href="{{ route('team-workspaces.show', ['team_workspace' => $w, 'tab' => 'tasks']) }}" class="btn {{ $currentTab === 'tasks' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px 8px 0 0;border-bottom:none">Tugas</a>
        <a href="{{ route('team-workspaces.show', ['team_workspace' => $w, 'tab' => 'time']) }}" class="btn {{ $currentTab === 'time' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius:8px 8px 0 0;border-bottom:none">Waktu</a>
    </div>

    <!-- ======================== TAB: ANGGOTA ======================== -->
    @if($currentTab === 'members')
    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Daftar Anggota ({{ $members->count() }})</h3>
            @if($members->count())
            <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $m)
                    <tr>
                        <td style="font-weight:500">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:28px;height:28px;border-radius:50%;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:var(--accent)">{{ strtoupper(substr($m->name, 0, 2)) }}</div>
                                {{ $m->name }}
                            </div>
                        </td>
                        <td style="color:var(--muted)">{{ $m->email }}</td>
                        <td>
                            <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ ($m->pivot->role ?? '') === 'owner' ? '#f59e0b20' : 'var(--accent-bg)' }};color:{{ ($m->pivot->role ?? '') === 'owner' ? '#f59e0b' : 'var(--accent)' }}">
                                {{ ucfirst($m->pivot->role ?? 'member') }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--muted)">{{ $m->pivot->joined_at ? \Carbon\Carbon::parse($m->pivot->joined_at)->format('d M Y') : '—' }}</td>
                        <td style="text-align:right">
                            @if(($m->pivot->role ?? '') !== 'owner' && $m->id !== auth()->id())
                            <form method="POST" action="{{ route('workspace-members.destroy', [$w, $m]) }}" style="display:inline" onsubmit="return confirm('Hapus anggota ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:12px">Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada anggota.</div>
            @endif
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Tambah Anggota</h3>
            <form method="POST" action="{{ route('workspace-members.store', $w) }}" style="display:flex;flex-direction:column;gap:12px">
                @csrf
                <div>
                    <label class="label">Email</label>
                    <input type="email" name="email" required placeholder="email@kantor-hukum.id">
                </div>
                <div>
                    <label class="label">Role</label>
                    <select name="role" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>
    @endif

    <!-- ======================== TAB: DOKUMEN ======================== -->
    @if($currentTab === 'documents')
    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Dokumen ({{ $documents->count() }})</h3>
            @if($documents->count())
            <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Kategori</th>
                        <th>Ukuran</th>
                        <th>Diunggah</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:16px">{{ $doc->file_icon }}</span>
                                <div>
                                    <div style="font-weight:500">{{ $doc->title }}</div>
                                    <div style="font-size:11px;color:var(--muted)">{{ $doc->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:var(--accent-bg);color:var(--accent)">{{ $doc->category_name }}</span></td>
                        <td style="font-size:12px;color:var(--muted)">{{ $doc->formatted_size }}</td>
                        <td style="font-size:12px;color:var(--muted)">
                            {{ $doc->user?->name ?? '—' }}
                            <div style="font-size:11px">{{ $doc->created_at ? $doc->created_at->format('d M Y') : '' }}</div>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('workspace-documents.download', [$w, $doc]) }}" style="color:var(--accent);font-size:12px">Unduh</a>
                            <form method="POST" action="{{ route('workspace-documents.destroy', [$w, $doc]) }}" style="display:inline;margin-left:8px" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:12px">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada dokumen.</div>
            @endif
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Unggah Dokumen</h3>
            <form method="POST" action="{{ route('workspace-documents.store', $w) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px">
                @csrf
                <div>
                    <label class="label">Judul</label>
                    <input type="text" name="title" required placeholder="Judul dokumen">
                </div>
                <div>
                    <label class="label">Kategori</label>
                    <select name="category">
                        @foreach(App\Models\WorkspaceDocument::CATEGORIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Deskripsi</label>
                    <input type="text" name="description" placeholder="Keterangan singkat">
                </div>
                <div>
                    <label class="label">File (max 25MB)</label>
                    <input type="file" name="file" required style="padding:8px">
                </div>
                <button type="submit" class="btn btn-primary">Unggah</button>
            </form>
        </div>
    </div>
    @endif

    <!-- ======================== TAB: CATATAN ======================== -->
    @if($currentTab === 'notes')
    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Catatan Kasus ({{ $notes->count() }})</h3>
            @if($notes->count())
            <div style="display:flex;flex-direction:column;gap:12px">
                @foreach($notes as $note)
                <div style="padding:16px;border:1px solid var(--line);border-radius:8px;background:var(--bg)">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                        <h4 style="font-size:14px;font-weight:600">{{ $note->title }}</h4>
                        <form method="POST" action="{{ route('workspace-notes.destroy', [$w, $note]) }}" style="display:inline" onsubmit="return confirm('Hapus catatan?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:11px">Hapus</button>
                        </form>
                    </div>
                    <p style="font-size:13px;color:var(--muted);white-space:pre-wrap;line-height:1.6">{{ $note->content }}</p>
                    <div style="margin-top:8px;font-size:11px;color:var(--muted)">
                        {{ $note->user?->name ?? '—' }} &middot; {{ $note->created_at ? $note->created_at->format('d M Y H:i') : '' }}
                    </div>
                    <!-- Edit form toggle -->
                    <details style="margin-top:8px">
                        <summary style="font-size:12px;color:var(--accent);cursor:pointer">Edit</summary>
                        <form method="POST" action="{{ route('workspace-notes.update', [$w, $note]) }}" style="display:flex;flex-direction:column;gap:8px;margin-top:8px">
                            @csrf @method('PATCH')
                            <input type="text" name="title" value="{{ $note->title }}" required>
                            <textarea name="content" rows="3" required>{{ $note->content }}</textarea>
                            <button type="submit" class="btn btn-primary" style="align-self:flex-start">Simpan</button>
                        </form>
                    </details>
                </div>
                @endforeach
            </div>
            @else
            <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada catatan.</div>
            @endif
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Catatan Baru</h3>
            <form method="POST" action="{{ route('workspace-notes.store', $w) }}" style="display:flex;flex-direction:column;gap:12px">
                @csrf
                <div>
                    <label class="label">Judul</label>
                    <input type="text" name="title" required placeholder="Catatan singkat">
                </div>
                <div>
                    <label class="label">Isi Catatan</label>
                    <textarea name="content" rows="5" required placeholder="Tulis catatan kasus..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Catatan</button>
            </form>
        </div>
    </div>
    @endif

    <!-- ======================== TAB: TUGAS ======================== -->
    @if($currentTab === 'tasks')
    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Tugas ({{ $tasks->count() }})</h3>
            @if($tasks->count())
            <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:30px"></th>
                        <th>Tugas</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Penanggung Jawab</th>
                        <th>Deadline</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr style="{{ $task->status === 'done' ? 'opacity:.6' : '' }}">
                        <td>
                            <form method="POST" action="{{ route('workspace-tasks.toggle', [$w, $task]) }}">
                                @csrf @method('PATCH')
                                <input type="checkbox" {{ $task->status === 'done' ? 'checked' : '' }} onchange="this.form.submit()" style="cursor:pointer;width:auto">
                            </form>
                        </td>
                        <td>
                            <div style="font-weight:500;text-decoration:{{ $task->status === 'done' ? 'line-through' : 'none' }}">{{ $task->title }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ $task->description }}</div>
                        </td>
                        <td><span style="padding:2px 8px;border-radius:99px;font-size:11px;color:{{ $task->priority_color }};background:{{ $task->priority_color }}20">{{ $task->priority_name }}</span></td>
                        <td><span style="padding:2px 8px;border-radius:99px;font-size:11px;color:{{ $task->status_color }};background:{{ $task->status_color }}20">{{ $task->status_name }}</span></td>
                        <td style="font-size:12px;color:var(--muted)">{{ $task->assignee?->name ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--muted)">{{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('workspace-tasks.destroy', [$w, $task]) }}" style="display:inline" onsubmit="return confirm('Hapus tugas?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:12px">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada tugas.</div>
            @endif
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Tugas Baru</h3>
            <form method="POST" action="{{ route('workspace-tasks.store', $w) }}" style="display:flex;flex-direction:column;gap:12px">
                @csrf
                <div>
                    <label class="label">Judul Tugas</label>
                    <input type="text" name="title" required placeholder="Judul tugas">
                </div>
                <div>
                    <label class="label">Deskripsi</label>
                    <input type="text" name="description" placeholder="Keterangan singkat">
                </div>
                <div>
                    <label class="label">Penanggung Jawab</label>
                    <select name="assigned_to">
                        <option value="">-- Pilih --</option>
                        @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <div>
                        <label class="label">Prioritas</label>
                        <select name="priority">
                            @foreach(App\Models\WorkspaceTask::PRIORITIES as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'normal' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Deadline</label>
                        <input type="date" name="due_date">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Tugas</button>
            </form>
        </div>
    </div>
    @endif

    <!-- ======================== TAB: WAKTU ======================== -->
    @if($currentTab === 'time')
    @php
        $totalMin = $timeEntries->sum('minutes');
        $totalHours = intdiv($totalMin, 60);
        $totalMins = $totalMin % 60;
        $billableMin = $timeEntries->where('billable', true)->sum('minutes');
    @endphp
    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
        <div>
            <div class="grid-4" style="margin-bottom:16px">
                <div class="card stat">
                    <div class="num" style="font-size:22px">{{ $totalHours }}j {{ $totalMins }}m</div>
                    <div class="label">Total Waktu</div>
                </div>
                <div class="card stat">
                    <div class="num" style="font-size:22px">{{ $timeEntries->count() }}</div>
                    <div class="label">Entry</div>
                </div>
                <div class="card stat">
                    <div class="num" style="font-size:22px">{{ intdiv($billableMin, 60) }}j {{ $billableMin % 60 }}m</div>
                    <div class="label">Billable</div>
                </div>
                <div class="card stat">
                    <div class="num" style="font-size:22px">{{ $timeEntries->where('billable', false)->count() }}</div>
                    <div class="label">Non-Billable</div>
                </div>
            </div>
            <div class="card">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Riwayat Waktu</h3>
                @if($timeEntries->count())
                <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>Tugas</th>
                            <th>Durasi</th>
                            <th>Billable</th>
                            <th>User</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeEntries as $entry)
                        <tr>
                            <td style="font-weight:500">{{ $entry->description ?: '—' }}</td>
                            <td style="font-size:12px;color:var(--muted)">{{ $entry->task?->title ?? '—' }}</td>
                            <td>{{ $entry->formatted_duration }}</td>
                            <td>
                                <span style="padding:2px 8px;border-radius:99px;font-size:11px;background:{{ $entry->billable ? '#22c55e20' : '#94a3b820' }};color:{{ $entry->billable ? '#22c55e' : '#94a3b8' }}">
                                    {{ $entry->billable ? 'Ya' : 'Tidak' }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:var(--muted)">{{ $entry->user?->name ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--muted)">{{ $entry->entry_date ? $entry->entry_date->format('d M Y') : '—' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('workspace-time.destroy', [$w, $entry]) }}" style="display:inline" onsubmit="return confirm('Hapus entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="color:var(--err);background:none;border:none;cursor:pointer;font-size:12px">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @else
                <div style="padding:24px;text-align:center;color:var(--muted)">Belum ada entry waktu.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px">Catat Waktu</h3>
            <form method="POST" action="{{ route('workspace-time.store', $w) }}" style="display:flex;flex-direction:column;gap:12px">
                @csrf
                <div>
                    <label class="label">Deskripsi</label>
                    <input type="text" name="description" placeholder="Kegiatan yang dilakukan">
                </div>
                <div>
                    <label class="label">Tugas Terkait</label>
                    <select name="task_id">
                        <option value="">-- Pilih (opsional) --</option>
                        @foreach($tasks->where('status', '!=', 'done') as $task)
                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <div>
                        <label class="label">Jam</label>
                        <input type="number" name="hours" min="0" max="23" value="0" required>
                    </div>
                    <div>
                        <label class="label">Menit</label>
                        <input type="number" name="minutes" min="0" max="59" value="0" required>
                    </div>
                </div>
                <div>
                    <label class="label">Tanggal</label>
                    <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" name="billable" value="1" checked style="width:auto">
                        Billable
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Catat Waktu</button>
            </form>
        </div>
    </div>
    @endif

</div>
</x-layouts.base>
