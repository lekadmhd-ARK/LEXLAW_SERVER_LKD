<x-layouts.base>
@section("content")
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Team Workspaces</h1>
        </div>
        @if (session('success'))<div class="mb-4 p-4 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>@endif
        <table class="w-full bg-white rounded-lg shadow overflow-hidden">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Active</th><th class="px-4 py-3">Actions</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-medium">{{ $item->name }}</td><td class="px-4 py-3">{{ $item->is_active ? 'Yes' : 'No' }}</td><td class="px-4 py-3"><a href="/team-workspaces/{{ $item->id }}/edit" class="text-indigo-600 hover:underline">Edit</a></td></tr>
                @empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No workspaces yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-6 bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">Create Workspace</h3>
            <form method="POST" action="/team-workspaces" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium">Name</label><input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-medium">Description</label><textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea></div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create</button>
            </form>
        </div>
    </div>
</x-layouts.base>
