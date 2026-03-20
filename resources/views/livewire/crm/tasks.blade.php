<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-4 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-900">إدارة <span class="text-indigo-600">المهام</span></h1>
            <p class="text-xs lg:text-sm text-slate-500 font-semibold mt-0.5">Loving Homes Task Center</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                <span class="text-xs font-bold text-slate-500">الحالة</span>
                <select wire:model.live="filterStatus" class="text-xs font-bold text-slate-700 bg-transparent outline-none border-none">
                    <option value="">الكل</option>
                    <option value="pending">معلقة</option>
                    <option value="inprogress">قيد التنفيذ</option>
                    <option value="done">مكتملة</option>
                </select>
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                <span class="text-xs font-bold text-slate-500">الأولوية</span>
                <select wire:model.live="filterPriority" class="text-xs font-bold text-slate-700 bg-transparent outline-none border-none">
                    <option value="">الكل</option>
                    <option value="low">منخفضة</option>
                    <option value="medium">متوسطة</option>
                    <option value="high">عالية</option>
                </select>
            </div>
        </div>
    </header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 lg:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">معلقة</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl lg:text-4xl font-black text-slate-800">{{ $stats['pending'] }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 lg:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-amber-600 uppercase tracking-wider">قيد التنفيذ</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl lg:text-4xl font-black text-amber-600">{{ $stats['inprogress'] }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 lg:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider">مكتملة</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl lg:text-4xl font-black text-emerald-600">{{ $stats['done'] }}</div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-4 lg:p-5 shadow-lg shadow-red-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-white/80 uppercase tracking-wider">متأخرة</span>
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl lg:text-4xl font-black text-white">{{ $stats['overdue'] }}</div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="space-y-3">
        @forelse($tasks as $task)
            @php
                $isOverdue = $task->status != 'done' && $task->due_date && $task->due_date < now();
                $priorityColors = ['high'=>'bg-red-100 text-red-700','medium'=>'bg-amber-100 text-amber-700','low'=>'bg-slate-100 text-slate-600'];
                $statusConfig   = ['pending'=>['bg-slate-100','text-slate-600'],'inprogress'=>['bg-amber-100','text-amber-700'],'done'=>['bg-emerald-100','text-emerald-700']];
                [$sbg,$scl] = $statusConfig[$task->status] ?? ['bg-slate-100','text-slate-600'];
                $pc = $priorityColors[$task->priority] ?? 'bg-slate-100 text-slate-600';
            @endphp
            <div class="bg-white rounded-2xl border {{ $isOverdue ? 'border-red-200 shadow-red-100' : 'border-slate-200' }} shadow-sm hover:shadow-md transition-all">
                <div class="flex items-start sm:items-center gap-4 p-4 lg:p-5">
                    <!-- Status Icon -->
                    <div class="w-10 h-10 rounded-xl {{ $sbg }} flex items-center justify-center flex-shrink-0">
                        @if($task->status == 'done')
                            <svg class="w-5 h-5 {{ $scl }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif($task->status == 'inprogress')
                            <svg class="w-5 h-5 {{ $scl }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 {{ $scl }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="text-sm font-black text-slate-900 {{ $isOverdue ? 'text-red-800' : '' }}">{{ $task->title }}</h3>
                            @if($isOverdue)
                                <span class="bg-red-100 text-red-600 text-[10px] font-black px-2 py-0.5 rounded-md">متأخرة!</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('crm.client-show', $task->client->id) }}"
                               class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                {{ $task->client->name }}
                            </a>
                            <span class="{{ $pc }} text-[10px] font-bold px-2 py-0.5 rounded-md">{{ $task->priority }}</span>
                            <!-- Due date shown on mobile -->
                            @if($task->due_date)
                                <span class="text-[10px] font-bold {{ $isOverdue ? 'text-red-600' : 'text-slate-400' }} sm:hidden">
                                    {{ $task->due_date->format('Y/m/d') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Due Date (desktop) -->
                    <div class="text-right hidden sm:block flex-shrink-0">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-1">الموعد</div>
                        <div class="text-sm font-black {{ $isOverdue ? 'text-red-600' : 'text-slate-700' }}">
                            {{ $task->due_date ? $task->due_date->format('Y/m/d') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 py-20 text-center shadow-sm">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <p class="text-sm font-bold text-slate-400">لا توجد مهام</p>
            </div>
        @endforelse
    </div>
</div>
