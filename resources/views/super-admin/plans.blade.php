<x-layouts.base>

@section('title', 'Super Admin Plans - LEXLAW v2')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">💰 Super Admin Pricing</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola paket langganan & kuota perusahaan</p>
     </div>
        <a href="/dashboard" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">← Back</a>
 </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 mb-6">
        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-3">Paket SaaS</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($plans as $plan)
            <div class="p-4 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-indigo-500 transition-colors">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-2">{{ Str::ucfirst($plan->name)</div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($plan->price_monthly) }}<span class="text-xs text-slate-500/80">/bulan</span></div>
                <div class="text-sm text-slate-500 mb-1">{{ Str::limit($plan->features, 100)</div>
                <button class="mt-2 w-full px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-hover hover:bg-indigo-700">Upgrade</button>
           </div>
           @endforeach
       </div>
   </div>

   <div class="mt-8">
       <a href="#" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Buat Paket Baru</a>
   </div>
</div>
</x-layouts.base>