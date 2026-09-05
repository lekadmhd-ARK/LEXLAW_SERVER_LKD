<x-layouts.base>
@section("content")
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Subscription Plans</h1>
        </div>
        <table class="w-full bg-white rounded-lg shadow overflow-hidden">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Monthly</th><th class="px-4 py-3 text-left">Yearly</th><th class="px-4 py-3 text-left">Max Users</th><th class="px-4 py-3 text-left">Max Regulations</th><th class="px-4 py-3 text-left">AI Enabled</th><th class="px-4 py-3 text-left">Active</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($plans as $plan)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-medium">{{ $plan->name }}</td><td class="px-4 py-3">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</td><td class="px-4 py-3">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}</td><td class="px-4 py-3">{{ $plan->max_users }}</td><td class="px-4 py-3">{{ $plan->max_regulations }}</td><td class="px-4 py-3">{{ $plan->ai_enabled ? 'Yes' : 'No' }}</td><td class="px-4 py-3">{{ $plan->is_active ? 'Yes' : 'No' }}</td></tr>
                @empty<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No plans defined.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.base>
