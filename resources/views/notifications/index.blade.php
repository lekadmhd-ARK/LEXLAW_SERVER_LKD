<x-layouts.base>
@section('title', 'Notifikasi')
<div>
    <div class="page-head">
        <div>
            <div class="eyebrow">🔔 Notifikasi</div>
            <h1 class="page-title">Pemberitahuan</h1>
        </div>
        @if(auth()->user()->unreadNotifications->count())
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button type="submit" class="btn btn-secondary">Tandai Semua Dibaca</button>
        </form>
        @endif
    </div>

    <div class="card" style="max-width:720px">
        @forelse($notifications as $n)
        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--line)">
            <div style="font-size:20px;flex-shrink:0">
                {{ match($n->data['type'] ?? '') {
                    'member_added' => '👤',
                    'task_assigned' => '✅',
                    'document_uploaded' => '📤',
                    'deadline_reminder' => '⏰',
                    default => '🔔',
                } }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:var(--text);margin-bottom:4px">{!! $n->data['message'] ?? '' !!}</div>
                <div style="font-size:11px;color:var(--muted)">{{ $n->created_at->diffForHumans() }}</div>
            </div>
            @if(is_null($n->read_at))
            <a href="{{ route('notifications.read', $n->id) }}" class="btn btn-secondary" style="padding:4px 10px;font-size:11px">Baca</a>
            @endif
        </div>
        @empty
        <div style="padding:32px;text-align:center;color:var(--muted)">Tidak ada notifikasi.</div>
        @endforelse

        <div style="margin-top:16px">{{ $notifications->links() }}</div>
    </div>
</div>
</x-layouts.base>
