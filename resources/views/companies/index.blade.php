<x-layouts.base>
@section("content")
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Companies</h1>
        <table class="w-full bg-white rounded-lg shadow overflow-hidden">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Plan</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-medium">{{ $item->name }}</td><td class="px-4 py-3">{{ $item->plan?->name }}</td><td class="px-4 py-3 capitalize">{{ $item->subscription_status }}</td></tr>
                @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No companies.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.base>
