<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-4 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-900">لوحة <span class="text-indigo-600">المتابعة</span></h1>
            <p class="text-xs lg:text-sm text-slate-500 font-semibold mt-0.5">{{ collect($clientsByStage)->flatten()->count() }} عميل إجمالاً</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search"
                    placeholder="ابحث بالاسم..."
                    class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-2.5 pr-10 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 shadow-sm placeholder:text-slate-400">
            </div>
            <a href="{{ route('crm.clients') }}"
               class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-black shadow-lg shadow-indigo-500/25 active:scale-95 transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">عميل جديد</span>
            </a>
        </div>
    </header>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-6 no-scrollbar">
        @foreach($stages as $id => $stage)
            <div class="flex-shrink-0 w-[260px] sm:w-[280px] lg:w-[300px] flex flex-col">

                <!-- Column Header -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage['color'] }}"></div>
                        <h2 class="text-sm font-black text-slate-700">{{ $stage['name'] }}</h2>
                    </div>
                    <span class="text-xs font-black text-slate-500 bg-slate-200 px-2.5 py-1 rounded-full">
                        {{ count($clientsByStage[$id]) }}
                    </span>
                </div>

                <!-- Cards Column -->
                <div class="flex-grow space-y-3 bg-slate-200/50 rounded-2xl p-3 min-h-[400px]">
                    @forelse($clientsByStage[$id] as $client)
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">

                            <div class="flex items-start justify-between mb-3">
                                <a href="{{ route('crm.client-show', $client->id) }}"
                                   class="text-sm font-black text-slate-900 hover:text-indigo-600 transition-colors leading-tight line-clamp-2">
                                    {{ $client->name }}
                                </a>
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-black text-white shadow-sm flex-shrink-0 mr-2"
                                     style="background-color: {{ $stage['color'] }}">
                                    {{ mb_substr($client->name, 0, 1) }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $client->source }}</span>
                                    <span class="text-sm font-black text-indigo-600">{{ number_format($client->deal_value) }} KD</span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-400" dir="ltr">{{ $client->phone ?: '—' }}</div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                                <span class="text-[10px] text-slate-400 font-semibold">{{ $client->updated_at->diffForHumans() }}</span>

                                @php
                                    $stageKeys  = array_keys($stages);
                                    $currentIdx = array_search($id, $stageKeys);
                                    $nextStage  = $currentIdx < count($stageKeys) - 1 ? $stageKeys[$currentIdx + 1] : null;
                                @endphp
                                @if($nextStage)
                                    <button wire:click="moveStage({{ $client->id }}, '{{ $nextStage }}')"
                                        class="text-[10px] font-black text-indigo-500 hover:text-white hover:bg-indigo-500 px-2.5 py-1 rounded-lg transition-all">
                                        {{ $stages[$nextStage]['name'] }} ←
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                            <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <span class="text-xs font-bold">لا يوجد عملاء</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
