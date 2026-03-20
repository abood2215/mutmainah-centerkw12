<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-4 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="/crm/clients" class="w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 flex items-center justify-center shadow-sm transition-colors group flex-shrink-0">
                <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xl font-black shadow-lg flex-shrink-0">
                {{ mb_substr($client->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl lg:text-2xl font-black text-slate-900">{{ $client->name }}</h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    @php
                        $stageBg = ['new'=>'bg-slate-100 text-slate-600','contacted'=>'bg-blue-100 text-blue-700','interested'=>'bg-yellow-100 text-yellow-700','booked'=>'bg-purple-100 text-purple-700','active'=>'bg-green-100 text-green-700','followup'=>'bg-orange-100 text-orange-700','completed'=>'bg-emerald-100 text-emerald-700'];
                        $cls = $stageBg[$client->stage] ?? 'bg-slate-100 text-slate-600';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 {{ $cls }} px-2.5 py-1 rounded-lg text-xs font-bold">{{ $client->stage }}</span>
                    <span class="text-xs text-slate-400 font-semibold">{{ $client->source }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2 w-full sm:w-auto">
            @if($client->phone)
            <a href="{{ route('crm.inbox') }}?phone={{ urlencode($client->phone) }}"
               class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-black shadow-lg shadow-emerald-500/25 active:scale-95 transition-all">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                </svg>
                واتساب
            </a>
            @else
            <button disabled class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-slate-200 text-slate-400 px-4 py-2.5 rounded-xl text-sm font-black cursor-not-allowed">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                </svg>
                لا يوجد رقم
            </button>
            @endif
            <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-white border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-600 px-4 py-2.5 rounded-xl text-sm font-black shadow-sm active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تحرير
            </button>
        </div>
    </header>

    <!-- Tabs -->
    <div class="overflow-x-auto no-scrollbar mb-5">
        <nav class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1 w-max min-w-full sm:w-fit shadow-sm">
            @foreach(['overview' => 'نظرة عامة', 'notes' => 'الملاحظات', 'tasks' => 'المهام', 'activities' => 'النشاطات', 'conversations' => 'المحادثات'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                    class="px-3 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all whitespace-nowrap
                        {{ $activeTab == $key ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Content -->
    <div class="animate-slide-up">

        @if($activeTab == 'overview')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h2 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                            <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                            بيانات العميل
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">الهاتف</div>
                                <div class="text-sm font-bold text-slate-800" dir="ltr">{{ $client->phone ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">الإيميل</div>
                                <div class="text-sm font-bold text-slate-800 truncate">{{ $client->email ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">المصدر</div>
                                <div class="text-sm font-bold text-indigo-600 uppercase">{{ $client->source }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">الأولوية</div>
                                <div class="text-sm font-bold text-slate-800">{{ $client->priority }}</div>
                            </div>
                        </div>
                    </div>

                    @php $clientNotesText = $client->getAttributes()['notes'] ?? null; @endphp
                    @if($clientNotesText)
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                            <h3 class="text-xs font-black text-amber-700 uppercase tracking-widest mb-2">ملاحظات عامة</h3>
                            <p class="text-sm text-amber-900 font-semibold leading-relaxed">{{ $clientNotesText }}</p>
                        </div>
                    @endif
                </div>

                <!-- Deal Value -->
                <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl p-6 shadow-lg text-white">
                    <div class="text-xs font-black uppercase tracking-widest opacity-70 mb-3">القيمة التقديرية</div>
                    <div class="text-4xl font-black tracking-tight">{{ number_format($client->deal_value) }}</div>
                    <div class="text-lg font-bold opacity-70 mt-1">دينار كويتي</div>
                    <div class="mt-6 pt-6 border-t border-white/20">
                        <div class="text-xs font-black opacity-60 uppercase tracking-widest mb-1">تاريخ الإضافة</div>
                        <div class="text-sm font-bold">{{ $client->created_at->format('Y/m/d') }}</div>
                    </div>
                </div>
            </div>

        @elseif($activeTab == 'notes')
            <div class="max-w-3xl space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <textarea wire:model.defer="newNote" rows="3"
                        placeholder="اكتب ملاحظة جديدة..."
                        class="w-full bg-transparent border-none text-slate-800 font-semibold text-sm outline-none resize-none placeholder:text-slate-400"></textarea>
                    <div class="flex justify-end mt-3 pt-3 border-t border-slate-100">
                        <button wire:click="addNote"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-black shadow-sm active:scale-95 transition-all">
                            حفظ الملاحظة
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($client->notes()->latest()->get() as $note)
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-indigo-200 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-xs font-black text-indigo-600">م</div>
                                    <span class="text-sm font-bold text-slate-700">الموظف المسؤول</span>
                                </div>
                                <span class="text-xs text-slate-400 font-semibold">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700 font-semibold leading-relaxed">{{ $note->content }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-bold">لا توجد ملاحظات بعد</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @elseif($activeTab == 'tasks')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($client->tasks as $task)
                    @php
                        $taskStatus = ['pending'=>['bg-slate-100','text-slate-600'],'inprogress'=>['bg-yellow-100','text-yellow-700'],'done'=>['bg-emerald-100','text-emerald-700']];
                        [$tbg,$tcl] = $taskStatus[$task->status] ?? ['bg-slate-100','text-slate-600'];
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <span class="{{ $tbg }} {{ $tcl }} text-xs font-bold px-2.5 py-1 rounded-lg">{{ $task->status }}</span>
                            <span class="text-xs text-slate-400 font-semibold">{{ $task->due_date ? $task->due_date->format('M d') : '—' }}</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 mb-2">{{ $task->title }}</h3>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-slate-400 font-bold">{{ $task->priority }}</span>
                            <div class="h-1.5 w-16 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $task->status == 'done' ? '100' : ($task->status == 'inprogress' ? '50' : '10') }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-slate-400">
                        <p class="text-sm font-bold">لا توجد مهام</p>
                    </div>
                @endforelse
            </div>

        @elseif($activeTab == 'activities')
            <div class="max-w-2xl space-y-3">
                @forelse($client->activityLogs()->latest()->get() as $log)
                    @php
                        $actionColors = ['client_created'=>'bg-emerald-100 text-emerald-700','stage_changed'=>'bg-blue-100 text-blue-700','note_added'=>'bg-violet-100 text-violet-700'];
                        $ac = $actionColors[$log->action] ?? 'bg-slate-100 text-slate-600';
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $ac }} flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1 flex-wrap gap-1">
                                <span class="text-xs font-black {{ $ac }} px-2 py-0.5 rounded-md">{{ $log->action }}</span>
                                <span class="text-[10px] text-slate-400 font-bold">{{ $log->created_at->format('Y/m/d H:i') }}</span>
                            </div>
                            @if($log->metadata)
                                <p class="text-xs text-slate-600 font-semibold">
                                    @if(isset($log->metadata['from']))
                                        من: {{ $log->metadata['from'] }} → إلى: {{ $log->metadata['to'] }}
                                    @else
                                        {{ json_encode($log->metadata) }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <p class="text-sm font-bold">لا توجد نشاطات</p>
                    </div>
                @endforelse
            </div>

        @elseif($activeTab == 'conversations')
            <div class="max-w-2xl">
                <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm text-center">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-slate-800 mb-2">WhatsApp Integration</h3>
                    <p class="text-sm text-slate-500 font-semibold">المحادثات متاحة عبر صندوق الوارد</p>
                    <a href="/crm/inbox" class="inline-flex items-center gap-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-black transition-all">
                        اذهب للصندوق
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
