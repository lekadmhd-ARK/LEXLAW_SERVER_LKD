<x-layouts.base>
@section("content")
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Audit Logs</h1>
        <table class="w-full bg-white rounded-lg shadow overflow-hidden">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Action</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">IP</th><th class="px-4 py-3 text-left">Time</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-mono text-sm">{{ $item->action }}</td><td class="px-4 py-3">{{ $item->user_name }}</td><td class="px-4 py-3 text-sm">{{ $item->subject_type }} #{{ $item->subject_id }}</td><td class="px-4 py-3 text-sm">{{ $item->ip_address }}</td><td class="px-4 py-3 text-sm">{{ $item->created_at }}</td></tr>
                @empty<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No audit logs.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
</x-layouts.base>
