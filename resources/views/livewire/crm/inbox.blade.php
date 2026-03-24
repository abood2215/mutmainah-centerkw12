<div dir="rtl" class="flex overflow-hidden" style="height:calc(100vh - 53px); background:#ECEFFE;"
     x-data="{
         showChat: {{ ($activeConversationId || $pendingClientPhone) ? 'true' : 'false' }},
         prevUnread: {{ $unreadTotal }},
         _pollConvs: null,
         _pollMsgs: null,
         initNotifications() {
             if ('Notification' in window && Notification.permission === 'default') {
                 Notification.requestPermission();
             }
         },
         initPolling() {
             this._pollConvs = setInterval(() => {
                 if (!document.hidden) $wire.loadConversations();
             }, 30000);
             this._pollMsgs = setInterval(() => {
                 if (!document.hidden && $wire.activeConversationId) $wire.loadMessages(true);
             }, 15000);
         },
         playSound() {
             try {
                 const ctx = new (window.AudioContext || window.webkitAudioContext)();
                 const o = ctx.createOscillator(), g = ctx.createGain();
                 o.connect(g); g.connect(ctx.destination);
                 o.frequency.value = 840; o.type = 'sine';
                 g.gain.setValueAtTime(0.25, ctx.currentTime);
                 g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                 o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.35);
             } catch(e) {}
         },
         checkNew(newCount) {
             if (newCount > this.prevUnread) {
                 this.playSound();
                 if (typeof Notification !== 'undefined' && Notification.permission === 'granted' && document.hidden) {
                     new Notification('رسالة جديدة', { body: 'وصلتك رسالة جديدة في صندوق الرسائل' });
                 }
             }
             this.prevUnread = newCount;
         }
     }"
     x-init="initNotifications(); initPolling()"
     x-on:livewire:updated.window="if ($wire.activeConversationId) showChat = true; checkNew({{ $unreadTotal }})">

    {{-- ═══════════════════════════════════════════════════════
         CONVERSATIONS PANEL
    ═══════════════════════════════════════════════════════ --}}
    <div class="bg-white border-l border-slate-200 flex flex-col flex-shrink-0 w-full md:w-[320px] lg:w-[360px] shadow-md"
         :class="showChat ? 'hidden md:flex' : 'flex'">

        {{-- Panel Header --}}
        <div class="px-4 pt-4 pb-3 border-b border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-sm">
                        <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 leading-none">💬 صندوق الرسائل</h2>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ count($conversations) }} محادثة</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($unreadTotal > 0)
                        <span class="bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">{{ $unreadTotal }}</span>
                    @endif
                    <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>Live
                    </span>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="searchQuery"
                    placeholder="بحث باسم أو رقم..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 pr-9 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute top-2.5 right-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Status Tabs --}}
            <div class="flex gap-0.5 p-1 bg-slate-100 rounded-xl">
                <button wire:click="$set('filterStatus', 'open')"
                    class="flex-1 text-[11px] font-bold py-1.5 rounded-lg transition-all duration-200
                           {{ $filterStatus === 'open' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    🟢 مفتوح
                </button>
                <button wire:click="$set('filterStatus', 'pending')"
                    class="flex-1 text-[11px] font-bold py-1.5 rounded-lg transition-all duration-200
                           {{ $filterStatus === 'pending' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    ⏳ مقترح
                </button>
                <button wire:click="$set('filterStatus', 'resolved')"
                    class="flex-1 text-[11px] font-bold py-1.5 rounded-lg transition-all duration-200
                           {{ $filterStatus === 'resolved' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    ✅ محلول
                </button>
            </div>
        </div>

        {{-- Conversation List --}}
        <div class="flex-1 overflow-y-auto p-2 space-y-0.5" style="scrollbar-width:thin;scrollbar-color:#e2e8f0 transparent;">

            {{-- Loading skeleton when filter changes or conversations reload --}}
            <div wire:loading wire:target="updatedFilterStatus,loadConversations,refreshAll" class="space-y-1 px-1 pt-1">
                @for($i = 0; $i < 5; $i++)
                    <div class="flex items-start gap-3 p-3 rounded-xl animate-pulse bg-slate-50">
                        <div class="w-11 h-11 rounded-xl bg-slate-200 flex-shrink-0"></div>
                        <div class="flex-1 space-y-2 pt-1">
                            <div class="h-3 bg-slate-200 rounded w-3/4"></div>
                            <div class="h-2.5 bg-slate-100 rounded w-1/2"></div>
                        </div>
                    </div>
                @endfor
            </div>

            @forelse($filteredConversations as $conv)
                @php
                    $isActive = $activeConversationId == $conv['id'];
                    $avatarColors = ['from-indigo-500 to-violet-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-500','from-cyan-500 to-blue-600','from-fuchsia-500 to-purple-600'];
                    $avatarGrad = $avatarColors[abs(crc32($conv['client_name'])) % 6];
                    $priorityDot = match($conv['priority'] ?? null) { 'low'=>'bg-blue-400','medium'=>'bg-amber-400','high'=>'bg-orange-500','urgent'=>'bg-red-500',default=>'' };
                    $statusDot = match($conv['status']??'open') { 'open'=>'bg-emerald-500','pending'=>'bg-amber-500','resolved'=>'bg-rose-400',default=>'bg-slate-400' };
                @endphp
                <div wire:click="selectConversation({{ $conv['id'] }})"
                     x-on:click="showChat = true"
                     wire:loading.class="opacity-60 pointer-events-none" wire:target="selectConversation"
                     class="flex items-start gap-3 p-3 rounded-xl cursor-pointer transition-all duration-150 group
                        {{ $isActive ? 'bg-indigo-50 border border-indigo-200/70 shadow-sm' : 'border border-transparent hover:bg-slate-50 hover:border-slate-100' }}
                        {{ $loadingConvId == $conv['id'] ? 'opacity-70' : '' }}">

                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0 mt-0.5">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $avatarGrad }} flex items-center justify-center text-white text-[15px] font-black shadow-sm">
                            {{ mb_strtoupper(mb_substr($conv['client_name'], 0, 1)) }}
                        </div>
                        <span class="absolute -bottom-0.5 -left-0.5 w-3 h-3 rounded-full border-2 border-white {{ $statusDot }}"></span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1 mb-0.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if($priorityDot)
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $priorityDot }}"></span>
                                @endif
                                <span class="text-[13px] font-bold truncate {{ $isActive ? 'text-indigo-900' : 'text-slate-800' }}">
                                    {{ $conv['client_name'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if(($conv['unread'] ?? 0) > 0)
                                    <span class="bg-indigo-600 text-white text-[9px] font-black min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1 shadow-sm">
                                        {{ $conv['unread'] > 9 ? '9+' : $conv['unread'] }}
                                    </span>
                                @endif
                                @if($conv['last_message_at'])
                                    <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($conv['last_message_at'])->diffForHumans(null, true, true) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($conv['client_phone'] ?? '')
                            <p class="text-[11px] text-slate-400 font-medium leading-none mb-0.5" dir="ltr">{{ $conv['client_phone'] }}</p>
                        @endif

                        @if($conv['last_message'] ?? '')
                            <p class="text-[11px] text-slate-500 truncate leading-snug">{{ $conv['last_message'] }}</p>
                        @endif

                        @if($conv['assigned_agent'] ?? '')
                            <div class="flex items-center gap-1 mt-1">
                                <div class="w-3.5 h-3.5 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-2 h-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                </div>
                                <p class="text-[10px] text-indigo-500 font-semibold truncate">{{ $conv['assigned_agent'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <div wire:loading wire:target="updatedFilterStatus, filterStatus" class="flex flex-col items-center gap-3 py-4">
                        <svg class="w-6 h-6 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-xs font-semibold text-slate-400">جاري التحميل...</span>
                    </div>
                    <div wire:loading.remove wire:target="updatedFilterStatus, filterStatus" class="flex flex-col items-center">
                        <span class="text-4xl mb-3">📭</span>
                        <p class="text-sm font-bold text-slate-500">لا توجد محادثات</p>
                        <p class="text-xs mt-1 text-slate-400">جرّب تغيير الفلتر</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         CHAT AREA
    ═══════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0" :class="showChat ? 'flex' : 'hidden md:flex'">

        {{-- NO CONVERSATION SELECTED --}}
        @if(!$activeConversationId && !$pendingClientPhone)
            <div class="flex-1 flex flex-col items-center justify-center" style="background:#ECEFFE;">
                <div class="text-center">
                    <div class="w-24 h-24 rounded-3xl bg-white shadow-sm border border-indigo-100 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-700 mb-2">اختر محادثة</h3>
                    <p class="text-sm text-slate-400 font-medium">اختر محادثة من القائمة على اليمين</p>
                </div>
            </div>

        {{-- PENDING NEW CONVERSATION --}}
        @elseif($pendingClientPhone && !$activeConversationId)
            <div class="flex-1 flex flex-col" style="background:#ECEFFE;">
                <div class="bg-white border-b border-slate-100 px-4 py-3 flex items-center gap-3 md:hidden shadow-sm">
                    <button x-on:click="showChat = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <span class="font-bold text-slate-700 text-sm">محادثة جديدة</span>
                </div>
                <div class="flex-1 flex items-center justify-center p-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 max-w-md w-full text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center mx-auto mb-4 shadow-md">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3 class="text-base font-black text-slate-800 mb-1">بدء محادثة جديدة</h3>
                        <p class="text-sm text-slate-500 mb-1">{{ $pendingClientName ?: 'عميل' }}</p>
                        <p class="text-sm font-bold text-indigo-600 mb-4" dir="ltr">{{ $pendingClientPhone }}</p>
                        <p class="text-xs text-slate-400">اكتب رسالتك أدناه وسيتم إنشاء المحادثة تلقائياً</p>
                    </div>
                </div>
                <div class="bg-white border-t border-slate-100 p-4 shadow-[0_-2px_12px_rgba(0,0,0,0.04)]">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <input type="text" wire:model="newMessage" placeholder="اكتب رسالتك الأولى..."
                            class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400">
                        <button type="submit" class="bg-gradient-to-l from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm">
                            إرسال
                        </button>
                    </form>
                </div>
            </div>

        {{-- ACTIVE CONVERSATION --}}
        @else
            @if(!$activeConvData)
                <div class="flex-1 flex items-center justify-center" style="background:#ECEFFE;">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-7 h-7 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-slate-500">جاري التحميل...</span>
                    </div>
                </div>
            @else
                @php
                    $convStatus = $activeConvData['status'] ?? 'open';
                    $statusBadgeClass = match($convStatus) {
                        'open'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
                        'resolved' => 'bg-rose-100 text-rose-600 border-rose-200',
                        default    => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                    $statusLabel = match($convStatus) {
                        'open'     => '🟢 مفتوح',
                        'pending'  => '⏳ مقترح',
                        'resolved' => '✅ محلول',
                        default    => $convStatus,
                    };
                    $priorityBadge = match($activeConvPriority) {
                        'low'    => ['label'=>'🔵 منخفض','class'=>'bg-blue-100 text-blue-700 border-blue-200'],
                        'medium' => ['label'=>'🟡 متوسط','class'=>'bg-amber-100 text-amber-700 border-amber-200'],
                        'high'   => ['label'=>'🟠 مرتفع','class'=>'bg-orange-100 text-orange-700 border-orange-200'],
                        'urgent' => ['label'=>'🔴 عاجل','class'=>'bg-red-100 text-red-700 border-red-200'],
                        default  => null,
                    };
                    $hColors = ['from-indigo-500 to-violet-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-500','from-cyan-500 to-blue-600','from-fuchsia-500 to-purple-600'];
                    $headerGrad = $hColors[abs(crc32($activeConvData['client_name'] ?? '')) % 6];
                @endphp

                {{-- ── CHAT HEADER ── --}}
                <div class="bg-white border-b border-slate-200 flex-shrink-0 shadow-sm">
                    <div class="flex items-center gap-2.5 px-4 py-3">

                        {{-- Back (mobile) --}}
                        <button x-on:click="showChat = false" class="md:hidden p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $headerGrad }} flex items-center justify-center text-white font-black shadow-sm flex-shrink-0 text-base">
                            {{ mb_strtoupper(mb_substr($activeConvData['client_name'] ?? '?', 0, 1)) }}
                        </div>

                        {{-- Name & Meta --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h3 class="text-sm font-black text-slate-900 truncate">{{ $activeConvData['client_name'] }}</h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                @if($priorityBadge)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $priorityBadge['class'] }}">{{ $priorityBadge['label'] }}</span>
                                @endif
                                @if($isMuted)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>مكتوم
                                    </span>
                                @endif
                            </div>
                            @if($activeConvData['client_phone'])
                                <p class="text-[11px] text-slate-400 font-medium" dir="ltr">{{ $activeConvData['client_phone'] }}</p>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-1 flex-shrink-0">

                            {{-- Agent --}}
                            @if(!empty($agents))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open" title="تعيين وكيل"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all
                                           {{ $assignedAgentName ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="hidden sm:inline max-w-[68px] truncate">{{ $assignedAgentName ?: 'وكيل' }}</span>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute left-0 top-full mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1.5">
                                        <button wire:click="assignConversation(0, '')" x-on:click="open=false"
                                            class="w-full text-right px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-lg">بلا تعيين</button>
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        @foreach($agents as $agent)
                                            <button wire:click="assignConversation({{ $agent['id'] }}, '{{ addslashes($agent['name']) }}')" x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-indigo-50 rounded-lg flex items-center gap-2
                                                       {{ $assignedAgentId == $agent['id'] ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700' }}">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $agent['availability_status']==='online'?'bg-emerald-400':($agent['availability_status']==='busy'?'bg-amber-400':'bg-slate-300') }}"></span>
                                                <span class="flex-1 truncate">{{ $agent['name'] }}</span>
                                                @if($assignedAgentId == $agent['id'])
                                                    <svg class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Team --}}
                            @if(!empty($teams))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open" title="تعيين فريق"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition-all
                                           {{ $assignedTeamName ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="hidden sm:inline max-w-[68px] truncate">{{ $assignedTeamName ?: 'فريق' }}</span>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute left-0 top-full mt-1.5 w-44 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1.5">
                                        <button wire:click="assignTeam(0, '')" x-on:click="open=false"
                                            class="w-full text-right px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-lg">بلا فريق</button>
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        @foreach($teams as $team)
                                            <button wire:click="assignTeam({{ $team['id'] }}, '{{ addslashes($team['name']) }}')" x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-purple-50 rounded-lg flex items-center gap-2
                                                       {{ $assignedTeamId==$team['id'] ? 'bg-purple-50 text-purple-700' : 'text-slate-700' }}">
                                                <span class="flex-1">{{ $team['name'] }}</span>
                                                @if($assignedTeamId==$team['id'])
                                                    <svg class="w-3.5 h-3.5 text-purple-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Priority --}}
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open" title="الأولوية"
                                    class="p-1.5 rounded-lg transition-colors {{ $activeConvPriority ? 'bg-slate-100 text-slate-700' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute left-0 top-full mt-1.5 w-36 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1.5">
                                        @foreach([''=>'⬜ بلا أولوية','low'=>'🔵 منخفض','medium'=>'🟡 متوسط','high'=>'🟠 مرتفع','urgent'=>'🔴 عاجل'] as $pval=>$plabel)
                                            <button wire:click="setPriority('{{ $pval }}')" x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-slate-50 rounded-lg flex items-center gap-2
                                                       {{ $activeConvPriority===$pval ? 'text-indigo-700 bg-indigo-50' : 'text-slate-700' }}">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $pval===''?'bg-slate-300':($pval==='low'?'bg-blue-400':($pval==='medium'?'bg-amber-400':($pval==='high'?'bg-orange-500':'bg-red-500'))) }}"></span>
                                                {{ $plabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Labels --}}
                            @if(!empty($allLabels))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open" title="وسوم"
                                    class="p-1.5 rounded-lg transition-colors {{ !empty($activeConvLabels) ? 'bg-indigo-50 text-indigo-500' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute left-0 top-full mt-1.5 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden max-h-56 overflow-y-auto">
                                    <div class="p-1.5">
                                        @foreach($allLabels as $labelItem)
                                            <button wire:click="toggleLabel('{{ $labelItem['title'] }}')"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-slate-50 rounded-lg flex items-center gap-2
                                                       {{ in_array($labelItem['title'],$activeConvLabels) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700' }}">
                                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color:{{ $labelItem['color']??'#6366f1' }}"></span>
                                                <span class="flex-1">{{ $labelItem['title'] }}</span>
                                                @if(in_array($labelItem['title'],$activeConvLabels))
                                                    <svg class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Mute --}}
                            <button wire:click="toggleMute" title="{{ $isMuted ? 'إلغاء الكتم' : 'كتم' }}"
                                class="p-1.5 rounded-lg transition-colors {{ $isMuted ? 'bg-slate-200 text-slate-700' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                                @if($isMuted)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3.536-9.536A5 5 0 0112 6"/></svg>
                                @endif
                            </button>

                            {{-- Info --}}
                            <button wire:click="$toggle('showInfo')" title="تفاصيل"
                                class="p-1.5 rounded-lg transition-colors {{ $showInfo ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>

                            {{-- Close / Open --}}
                            <button wire:click="toggleStatus({{ $activeConversationId }})"
                                wire:loading.attr="disabled" wire:target="toggleStatus"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-sm transition-all disabled:opacity-60
                                    {{ $convStatus==='open'
                                        ? 'bg-slate-800 hover:bg-slate-900 text-white'
                                        : 'bg-gradient-to-l from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white' }}">
                                <span wire:loading.remove wire:target="toggleStatus" class="flex items-center gap-1.5">
                                    @if($convStatus==='open')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>إغلاق
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>فتح
                                    @endif
                                </span>
                                <svg wire:loading wire:target="toggleStatus" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── BODY: Messages + Info Panel ── --}}
                <div class="flex-1 flex min-h-0">

                    {{-- Messages Column --}}
                    <div class="flex-1 flex flex-col min-h-0">

                        {{-- Messages Scroll --}}
                        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-1"
                             style="scrollbar-width:thin;scrollbar-color:#c7d2fe transparent; background:#ECEFFE;"
                             x-data="{
                                 atBottom: true,
                                 scrollToBottom() { this.$nextTick(() => { this.$el.scrollTop = this.$el.scrollHeight; }); },
                                 onScroll() { const el = this.$el; this.atBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) < 80; }
                             }"
                             x-init="scrollToBottom()"
                             x-on:messages-loaded.window="atBottom = true; scrollToBottom()"
                             x-on:messages-refreshed.window="if(atBottom) scrollToBottom()"
                             @scroll.passive="onScroll()">

                            @php $prevDate = null; @endphp

                            @forelse($messages as $msg)
                                @php $msgDate = $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->toDateString() : null; @endphp

                                {{-- Date separator --}}
                                @if($msgDate && $msgDate !== $prevDate)
                                    @php $prevDate = $msgDate; @endphp
                                    <div class="flex justify-center my-3">
                                        <span class="bg-white/80 backdrop-blur-sm text-slate-500 text-[10px] font-bold px-4 py-1.5 rounded-full shadow-sm border border-white/60">
                                            {{ \Carbon\Carbon::parse($msg['sent_at'])->isSameDay(now()) ? 'اليوم' : (\Carbon\Carbon::parse($msg['sent_at'])->isYesterday() ? 'أمس' : \Carbon\Carbon::parse($msg['sent_at'])->translatedFormat('d M Y')) }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Activity --}}
                                @if($msg['direction'] === 'activity')
                                    <div class="flex justify-center my-1">
                                        <span class="bg-black/10 text-slate-600 text-[10px] font-semibold px-3 py-1 rounded-full">{{ $msg['content'] }}</span>
                                    </div>

                                {{-- Private Note --}}
                                @elseif($msg['private'])
                                    <div class="flex justify-start my-1">
                                        <div class="max-w-[76%]">
                                            <div class="flex items-center gap-1.5 mb-1.5">
                                                <div class="w-4 h-4 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-2.5 h-2.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                                </div>
                                                <span class="text-[10px] font-bold text-amber-600">ملاحظة خاصة</span>
                                                @if($msg['sender_name'])<span class="text-[10px] text-slate-400">· {{ $msg['sender_name'] }}</span>@endif
                                            </div>
                                            <div class="bg-amber-50 border border-amber-200/70 text-slate-800 rounded-2xl rounded-tr-md px-4 py-3 text-[13px] leading-relaxed shadow-sm">
                                                {!! nl2br(e($msg['content'])) !!}
                                            </div>
                                            <p class="text-[10px] text-slate-400 mt-1 px-1">{{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}</p>
                                        </div>
                                    </div>

                                {{-- Outgoing (agent) --}}
                                @elseif($msg['direction'] === 'out')
                                    <div class="flex justify-start my-0.5">
                                        <div class="max-w-[76%]">
                                            <div class="bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-2xl rounded-tr-md px-4 py-3 text-[13px] leading-relaxed shadow-md">
                                                {!! nl2br(e($msg['content'])) !!}
                                                @if(!empty($msg['attachments']))
                                                    @foreach($msg['attachments'] as $att)
                                                        @include('livewire.crm.partials.attachment', ['att'=>$att])
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1 px-1">
                                                <p class="text-[10px] text-slate-400">{{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}</p>
                                                @php $ms = $msg['status'] ?? 'sent'; @endphp
                                                @if($ms==='read')
                                                    <svg style="width:18px;height:11px;" viewBox="0 0 28 16" fill="none" stroke="#818cf8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,9 5,13 13,5"/><polyline points="8,9 12,13 27,3"/></svg>
                                                @elseif($ms==='delivered')
                                                    <svg style="width:18px;height:11px;" viewBox="0 0 28 16" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,9 5,13 13,5"/><polyline points="8,9 12,13 27,3"/></svg>
                                                @else
                                                    <svg style="width:13px;height:11px;" viewBox="0 0 18 16" fill="none" stroke="#cbd5e1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,9 5,13 17,3"/></svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                {{-- Incoming (client) --}}
                                @else
                                    <div class="flex justify-end my-0.5">
                                        <div class="max-w-[76%]">
                                            @if($msg['sender_name'])
                                                <p class="text-[10px] font-bold text-slate-500 mb-1 px-2 text-left">{{ $msg['sender_name'] }}</p>
                                            @endif
                                            <div class="bg-white text-slate-800 rounded-2xl rounded-tl-md px-4 py-3 text-[13px] leading-relaxed border border-slate-100/80 shadow-sm">
                                                {!! nl2br(e($msg['content'])) !!}
                                                @if(!empty($msg['attachments']))
                                                    @foreach($msg['attachments'] as $att)
                                                        @include('livewire.crm.partials.attachment', ['att'=>$att])
                                                    @endforeach
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-slate-400 mt-1 px-2 text-left">{{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}</p>
                                        </div>
                                    </div>
                                @endif

                            @empty
                                <div class="flex flex-col items-center justify-center h-full py-24">
                                    <div wire:loading wire:target="selectConversation, loadMessages" class="flex flex-col items-center gap-3">
                                        <svg class="w-8 h-8 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span class="text-sm font-semibold text-slate-500">جاري تحميل الرسائل...</span>
                                    </div>
                                    <div wire:loading.remove wire:target="selectConversation, loadMessages" class="flex flex-col items-center">
                                        <span class="text-4xl mb-3">💬</span>
                                        <p class="text-sm font-bold text-slate-500">لا توجد رسائل بعد</p>
                                        <p class="text-xs text-slate-400 mt-1">ابدأ المحادثة من أدناه</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        {{-- ── COMPOSER ── --}}
                        <div class="bg-white border-t border-slate-200 flex-shrink-0 shadow-[0_-2px_16px_rgba(99,102,241,0.06)]">

                            {{-- Tabs: Reply / Note --}}
                            <div class="flex items-center border-b border-slate-100 px-2">
                                <button wire:click="$set('isPrivateNote', false)"
                                    class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5
                                           {{ !$isPrivateNote ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    رد
                                </button>
                                <button wire:click="$set('isPrivateNote', true)"
                                    class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5
                                           {{ $isPrivateNote ? 'border-amber-500 text-amber-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    ملاحظة خاصة
                                </button>
                                <div class="mr-auto flex items-center gap-1 px-2">
                                    <span class="text-[10px] text-slate-300 font-medium hidden sm:block">Ctrl+Enter للإرسال</span>
                                </div>
                            </div>

                            <div class="p-3">
                                <form wire:submit.prevent="sendMessage">

                                    {{-- Canned Responses Panel (floats above input) --}}
                                    @if($showCanned)
                                    <div class="mb-2 bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 border-b border-slate-100">
                                            <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <input type="text" wire:model.live.debounce.200ms="cannedSearch"
                                                placeholder="ابحث في الردود الجاهزة..."
                                                autofocus
                                                class="flex-1 bg-transparent text-xs font-medium text-slate-700 focus:outline-none placeholder:text-slate-400">
                                            <button type="button" wire:click="$toggle('showCanned')" class="text-slate-400 hover:text-slate-600 transition-colors p-0.5 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="max-h-44 overflow-y-auto" style="scrollbar-width:thin;">
                                            @forelse($cannedResponses as $cr)
                                                <button type="button" wire:click="selectCannedResponse({{ $cr['id'] }})"
                                                    class="w-full text-right px-4 py-2.5 text-xs hover:bg-indigo-50 transition-colors border-b border-slate-50 last:border-0 flex items-start gap-3 group">
                                                    <div class="flex-shrink-0 w-6 h-6 rounded-lg bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center mt-0.5 transition-colors">
                                                        <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0 text-right">
                                                        <p class="font-black text-indigo-700 text-[12px]">{{ $cr['title'] }}</p>
                                                        <p class="text-slate-500 truncate text-[11px] mt-0.5">{{ Str::limit($cr['content'], 75) }}</p>
                                                    </div>
                                                </button>
                                            @empty
                                                <div class="py-8 text-center">
                                                    <svg class="w-8 h-8 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    <p class="text-xs font-semibold text-slate-400">لا توجد ردود جاهزة</p>
                                                    <p class="text-[10px] text-slate-300 mt-0.5">أضف ردوداً من صفحة الإعدادات</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                    @endif

                                    <div class="flex items-end gap-2">

                                        {{-- ✅ EMOJI PICKER — Fixed: emojis defined locally in x-data, no $root needed --}}
                                        <div class="relative flex-shrink-0"
                                             x-data="{
                                                 open: false,
                                                 emojis: ['😊','😄','😂','🥰','😍','😎','🤩','😁','👍','🙏','❤️','💕','✅','🔥','💬','📞','📩','⚡','🎉','😢','🤔','👋','💡','⚠️','📋','🕐','✔️','💪','🌟','🎊','💯','🙌','👏','🤝','💼','🏠','📊','🔔','📌','✨','💫','🎁','😊','🚀']
                                             }"
                                             x-on:click.outside="open = false">

                                            <button type="button" x-on:click="open = !open" title="إيموجي"
                                                class="p-2 rounded-xl transition-all"
                                                :class="open ? 'bg-indigo-100 text-indigo-600' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>

                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute bottom-12 right-0 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50"
                                                 style="width:280px; padding:12px;">
                                                <p class="text-[10px] font-bold text-slate-400 mb-2">اختر إيموجي</p>
                                                <div class="grid grid-cols-8 gap-0.5">
                                                    <template x-for="e in emojis" :key="e">
                                                        <button type="button"
                                                            x-on:click="$wire.set('newMessage', ($wire.newMessage || '') + e); open = false"
                                                            class="text-xl hover:bg-slate-100 active:bg-slate-200 rounded-lg transition-colors flex items-center justify-center"
                                                            style="aspect-ratio:1; padding:4px; line-height:1;"
                                                            x-text="e">
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Canned Responses Button --}}
                                        <button type="button" wire:click="$toggle('showCanned')" title="ردود جاهزة — أو اكتب / في الرسالة"
                                            class="flex-shrink-0 p-2 rounded-xl transition-all {{ $showCanned ? 'text-indigo-600 bg-indigo-100' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </button>

                                        {{-- Textarea --}}
                                        <textarea wire:model="newMessage"
                                            rows="1"
                                            placeholder="{{ $isPrivateNote ? 'اكتب ملاحظة خاصة...' : 'اكتب رسالتك... أو اكتب / للردود الجاهزة' }}"
                                            class="flex-1 rounded-xl px-4 py-2.5 text-[13px] text-slate-800 focus:outline-none focus:ring-2 resize-none leading-relaxed placeholder:text-slate-400 transition-all
                                                   {{ $isPrivateNote
                                                       ? 'bg-amber-50 border border-amber-200 focus:ring-amber-400/30 focus:border-amber-400'
                                                       : 'bg-slate-50 border border-slate-200 focus:ring-indigo-500/30 focus:border-indigo-400' }}"
                                            style="min-height:42px; max-height:130px;"
                                            x-on:input="
                                                $el.style.height='42px';
                                                $el.style.height=Math.min($el.scrollHeight,130)+'px';
                                                if($event.target.value === '/') {
                                                    $wire.set('newMessage','');
                                                    $wire.set('showCanned', true);
                                                }
                                            "
                                            x-on:keydown.ctrl.enter.prevent="$wire.sendMessage()">
                                        </textarea>

                                        {{-- Send Button --}}
                                        <button type="submit"
                                            wire:loading.attr="disabled" wire:target="sendMessage"
                                            class="flex-shrink-0 p-2.5 rounded-xl font-bold shadow-sm transition-all disabled:opacity-60
                                                   {{ $isPrivateNote
                                                       ? 'bg-amber-500 hover:bg-amber-600 text-white'
                                                       : 'bg-gradient-to-l from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white' }}">
                                            <svg wire:loading.remove wire:target="sendMessage" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            <svg wire:loading wire:target="sendMessage" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ── INFO PANEL ── --}}
                    <div x-show="$wire.showInfo"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-x-4"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 translate-x-4"
                         class="w-72 bg-white border-r border-slate-200 flex flex-col overflow-y-auto flex-shrink-0 shadow-[-4px_0_16px_rgba(0,0,0,0.04)]"
                         style="scrollbar-width:thin;">

                        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800">تفاصيل المحادثة</h4>
                            <button wire:click="$set('showInfo', false)" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-4 space-y-5">

                            {{-- Client --}}
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">العميل</p>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $headerGrad }} flex items-center justify-center text-white font-black shadow-sm text-base">
                                        {{ mb_strtoupper(mb_substr($activeConvData['client_name'], 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $activeConvData['client_name'] }}</p>
                                        @if($activeConvData['client_phone'])
                                            <p class="text-[11px] text-slate-400 font-medium" dir="ltr">{{ $activeConvData['client_phone'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($activeConvData['client_email'] ?? '')
                                    <div class="flex items-center gap-2 bg-slate-50 rounded-lg px-3 py-2">
                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="text-[11px] font-medium text-slate-600 truncate">{{ $activeConvData['client_email'] }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Conversation --}}
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">المحادثة</p>
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] text-slate-500">المعرّف</span>
                                        <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">#{{ $activeConvData['id'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] text-slate-500">الحالة</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    @if($activeConvData['last_message_at'])
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] text-slate-500">آخر نشاط</span>
                                        <span class="text-[11px] text-slate-600">{{ \Carbon\Carbon::parse($activeConvData['last_message_at'])->diffForHumans() }}</span>
                                    </div>
                                    @endif
                                    @if($assignedAgentName)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] text-slate-500">الوكيل</span>
                                        <span class="text-[11px] font-bold text-indigo-600">{{ $assignedAgentName }}</span>
                                    </div>
                                    @endif
                                    @if($assignedTeamName)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] text-slate-500">الفريق</span>
                                        <span class="text-[11px] font-bold text-purple-600">{{ $assignedTeamName }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Labels --}}
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">الوسوم</p>
                                @if(!empty($activeConvLabels))
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach($activeConvLabels as $lbl)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700">
                                                {{ $lbl }}
                                                <button type="button" wire:click="toggleLabel('{{ $lbl }}')" class="hover:text-indigo-900 transition-colors">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($allLabels))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($allLabels as $lItem)
                                            @if(!in_array($lItem['title'], $activeConvLabels))
                                                <button type="button" wire:click="toggleLabel('{{ $lItem['title'] }}')"
                                                    class="text-[10px] font-bold px-2 py-0.5 rounded-lg border border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
                                                    + {{ $lItem['title'] }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if(empty($activeConvLabels) && empty($allLabels))
                                    <p class="text-[11px] text-slate-400">لا توجد وسوم متاحة</p>
                                @endif
                            </div>

                            {{-- Priority --}}
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">الأولوية</p>
                                <div class="grid grid-cols-2 gap-1.5">
                                    @foreach([''=>'⬜ بلا','low'=>'🔵 منخفض','medium'=>'🟡 متوسط','high'=>'🟠 مرتفع','urgent'=>'🔴 عاجل'] as $pv=>$pl)
                                        <button type="button" wire:click="setPriority('{{ $pv }}')"
                                            class="text-[11px] font-bold py-1.5 rounded-lg border transition-all text-center
                                                   {{ $activeConvPriority===$pv
                                                       ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                                       : 'border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600' }}">
                                            {{ $pl }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                </div>{{-- end body --}}
            @endif
        @endif
    </div>{{-- end chat area --}}

</div>
