<div dir="rtl" class="flex bg-[#F4F6FA] overflow-hidden"
     style="height: calc(100vh - 53px);"
     wire:poll.3000ms="refreshAll"
     x-data="{
         showChat: {{ ($activeConversationId || $pendingClientPhone) ? 'true' : 'false' }},
         prevUnread: {{ $unreadTotal }},
         emojis: ['😊','😄','👍','🙏','❤️','✅','🔥','💬','📞','📩','⚡','🎉','😢','🤔','👋','💡','⚠️','📋','🕐','✔️'],
         initNotifications() {
             if ('Notification' in window && Notification.permission === 'default') {
                 Notification.requestPermission();
             }
         },
         playSound() {
             try {
                 const ctx = new (window.AudioContext || window.webkitAudioContext)();
                 const o = ctx.createOscillator(), g = ctx.createGain();
                 o.connect(g); g.connect(ctx.destination);
                 o.frequency.value = 880; o.type = 'sine';
                 g.gain.setValueAtTime(0.3, ctx.currentTime);
                 g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                 o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.4);
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
     x-init="initNotifications()"
     x-on:livewire:updated.window="if ($wire.activeConversationId) showChat = true; checkNew({{ $unreadTotal }})"
     >

    {{-- ═══════════════════════════════════════════════════════════════
         CONVERSATIONS PANEL (right side in RTL)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-l border-slate-200 flex flex-col shadow-sm flex-shrink-0 w-full md:w-[320px] lg:w-[340px]"
         :class="showChat ? 'hidden md:flex' : 'flex'">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-slate-100 bg-white">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-black text-slate-900">صندوق الرسائل</h2>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($unreadTotal > 0)
                        <span class="bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                            {{ $unreadTotal }}
                        </span>
                    @endif
                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full">
                        {{ count($conversations) }}
                    </span>
                    <span class="flex items-center gap-1 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                        Live
                    </span>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative mb-3">
                <input type="text" wire:model.live.debounce.300ms="searchQuery"
                    placeholder="بحث باسم أو رقم..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 pr-8">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute top-2.5 right-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-1">
                <button wire:click="$set('filterStatus', 'open')"
                    class="flex-1 text-[11px] font-bold px-2 py-1.5 rounded-lg transition-all {{ $filterStatus === 'open' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    مفتوح
                </button>
                <button wire:click="$set('filterStatus', 'pending')"
                    class="flex-1 text-[11px] font-bold px-2 py-1.5 rounded-lg transition-all {{ $filterStatus === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    معلّق
                </button>
                <button wire:click="$set('filterStatus', 'resolved')"
                    class="flex-1 text-[11px] font-bold px-2 py-1.5 rounded-lg transition-all {{ $filterStatus === 'resolved' ? 'bg-slate-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    مغلق
                </button>
            </div>
        </div>

        {{-- Conversation List --}}
        <div class="flex-1 overflow-y-auto p-2 space-y-1" style="scrollbar-width:thin;scrollbar-color:#e2e8f0 transparent;">
            @forelse($filteredConversations as $conv)
                @php
                    $priorityDot = match($conv['priority'] ?? null) {
                        'low'    => 'bg-blue-400',
                        'medium' => 'bg-amber-400',
                        'high'   => 'bg-orange-500',
                        'urgent' => 'bg-red-500',
                        default  => '',
                    };
                    $avatarGradients = ['from-indigo-500 to-purple-500','from-emerald-500 to-teal-500','from-rose-500 to-pink-500','from-amber-500 to-orange-500','from-cyan-500 to-blue-500'];
                    $avatarGrad = $avatarGradients[crc32($conv['client_name']) % 5];
                    $statusColor = match($conv['status'] ?? 'open') {
                        'open'     => 'bg-emerald-400',
                        'pending'  => 'bg-amber-400',
                        'resolved' => 'bg-slate-400',
                        default    => 'bg-slate-300',
                    };
                @endphp
                <div wire:click="selectConversation({{ $conv['id'] }})"
                     x-on:click="showChat = true"
                     class="flex items-start gap-3 p-3 rounded-xl cursor-pointer transition-all border-2 group
                        {{ $activeConversationId == $conv['id']
                            ? 'bg-indigo-50 border-indigo-200'
                            : 'border-transparent hover:bg-slate-50' }}">

                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0 mt-0.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $avatarGrad }} flex items-center justify-center text-white text-sm font-black shadow-sm">
                            {{ mb_substr($conv['client_name'], 0, 1) }}
                        </div>
                        <span class="absolute -bottom-0.5 -left-0.5 w-3 h-3 rounded-full border-2 border-white {{ $statusColor }}"></span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if($priorityDot)
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $priorityDot }}"></span>
                                @endif
                                <span class="text-[13px] font-bold text-slate-800 truncate">{{ $conv['client_name'] }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if(($conv['unread'] ?? 0) > 0)
                                    <span class="bg-indigo-600 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-sm">
                                        {{ $conv['unread'] > 9 ? '9+' : $conv['unread'] }}
                                    </span>
                                @endif
                                @if($conv['last_message_at'])
                                    <span class="text-[10px] text-slate-400 font-medium">
                                        {{ \Carbon\Carbon::parse($conv['last_message_at'])->diffForHumans(null, true, true) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($conv['client_phone'] ?? '')
                            <p class="text-[11px] text-slate-400 font-medium mb-1">{{ $conv['client_phone'] }}</p>
                        @endif

                        @if($conv['last_message'] ?? '')
                            <p class="text-[11px] text-slate-500 truncate leading-relaxed">{{ $conv['last_message'] }}</p>
                        @endif

                        {{-- Labels (first 2) --}}
                        @if(!empty($conv['labels']))
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach(array_slice($conv['labels'], 0, 2) as $label)
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-700">{{ $label }}</span>
                                @endforeach
                                @if(count($conv['labels']) > 2)
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500">+{{ count($conv['labels']) - 2 }}</span>
                                @endif
                            </div>
                        @endif

                        @if($conv['assigned_agent'] ?? '')
                            <p class="text-[10px] text-indigo-500 font-semibold mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $conv['assigned_agent'] }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="text-sm font-semibold">لا توجد محادثات</p>
                    <p class="text-xs mt-1">جرّب تغيير الفلتر</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CHAT AREA (left side in RTL)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0"
         :class="showChat ? 'flex' : 'hidden md:flex'">

        {{-- ── NO CONVERSATION SELECTED ── --}}
        @if(!$activeConversationId && !$pendingClientPhone)
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 bg-[#F4F6FA]">
                <div class="w-20 h-20 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-slate-600 mb-1">اختر محادثة</h3>
                <p class="text-sm text-slate-400">اختر محادثة من القائمة لعرض الرسائل</p>
            </div>

        {{-- ── PENDING NEW CONVERSATION ── --}}
        @elseif($pendingClientPhone && !$activeConversationId)
            <div class="flex-1 flex flex-col bg-[#F4F6FA]">
                {{-- Back button mobile --}}
                <div class="bg-white border-b border-slate-100 px-4 py-3 flex items-center gap-3 md:hidden">
                    <button x-on:click="showChat = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <span class="font-bold text-slate-700 text-sm">محادثة جديدة</span>
                </div>
                <div class="flex-1 flex items-center justify-center p-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-md w-full text-center">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-black text-slate-800 mb-1">بدء محادثة جديدة</h3>
                        <p class="text-sm text-slate-500 mb-4">
                            {{ $pendingClientName ?: 'عميل' }} — <span dir="ltr">{{ $pendingClientPhone }}</span>
                        </p>
                        <p class="text-xs text-slate-400">اكتب رسالتك أدناه وسيتم إنشاء المحادثة تلقائياً</p>
                    </div>
                </div>
                {{-- Composer for pending --}}
                <div class="bg-white border-t border-slate-100 p-4">
                    <form wire:submit.prevent="sendMessage" class="flex gap-2">
                        <input type="text" wire:model="newMessage"
                            placeholder="اكتب رسالتك الأولى..."
                            class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            إرسال
                        </button>
                    </form>
                </div>
            </div>

        {{-- ── ACTIVE CONVERSATION ── --}}
        @else
            @if(!$activeConvData)
                <div class="flex-1 flex items-center justify-center bg-[#F4F6FA]">
                    <div class="flex flex-col items-center gap-3 text-slate-400">
                        <svg class="w-8 h-8 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm font-semibold">جاري التحميل...</span>
                    </div>
                </div>
            @else
                @php
                    $convStatus = $activeConvData['status'] ?? 'open';
                    $statusBadgeClass = match($convStatus) {
                        'open'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
                        'resolved' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default    => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                    $statusLabel = match($convStatus) {
                        'open'     => 'مفتوح',
                        'pending'  => 'معلّق',
                        'resolved' => 'مغلق',
                        default    => $convStatus,
                    };
                    $priorityBadge = match($activeConvPriority) {
                        'low'    => ['label' => 'منخفض', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                        'medium' => ['label' => 'متوسط', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                        'high'   => ['label' => 'مرتفع', 'class' => 'bg-orange-100 text-orange-700 border-orange-200'],
                        'urgent' => ['label' => 'عاجل',  'class' => 'bg-red-100 text-red-700 border-red-200'],
                        default  => null,
                    };
                    $avatarGradients2 = ['from-indigo-500 to-purple-500','from-emerald-500 to-teal-500','from-rose-500 to-pink-500','from-amber-500 to-orange-500','from-cyan-500 to-blue-500'];
                    $headerGrad = $avatarGradients2[crc32($activeConvData['client_name'] ?? '') % 5];
                @endphp

                {{-- ── CHAT HEADER ── --}}
                <div class="bg-white border-b border-slate-100 shadow-sm flex-shrink-0">
                    <div class="flex items-center gap-3 px-4 py-3">
                        {{-- Back (mobile) --}}
                        <button x-on:click="showChat = false"
                            class="md:hidden p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        {{-- Avatar --}}
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $headerGrad }} flex items-center justify-center text-white text-sm font-black shadow-sm flex-shrink-0">
                            {{ mb_substr($activeConvData['client_name'] ?? '?', 0, 1) }}
                        </div>

                        {{-- Name & Meta --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-black text-slate-900 truncate">{{ $activeConvData['client_name'] }}</h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                @if($priorityBadge)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $priorityBadge['class'] }}">{{ $priorityBadge['label'] }}</span>
                                @endif
                                @if($isMuted)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border bg-slate-100 text-slate-500 border-slate-200 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                                        مكتوم
                                    </span>
                                @endif
                            </div>
                            @if($activeConvData['client_phone'])
                                <p class="text-xs text-slate-400 font-medium mt-0.5" dir="ltr">{{ $activeConvData['client_phone'] }}</p>
                            @endif
                        </div>

                        {{-- Labels chips in header --}}
                        @if(!empty($activeConvLabels))
                            <div class="hidden lg:flex flex-wrap gap-1">
                                @foreach(array_slice($activeConvLabels, 0, 3) as $lbl)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-indigo-100 text-indigo-700">{{ $lbl }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-1 flex-shrink-0">

                            {{-- Agent Dropdown --}}
                            @if(!empty($agents))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold
                                           {{ $assignedAgentName ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}
                                           transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="hidden sm:inline max-w-[80px] truncate">{{ $assignedAgentName ?: 'وكيل' }}</span>
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute left-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1">
                                        <button wire:click="assignConversation(0, '')" x-on:click="open=false"
                                            class="w-full text-right px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-lg">
                                            بلا تعيين
                                        </button>
                                        @foreach($agents as $agent)
                                            <button wire:click="assignConversation({{ $agent['id'] }}, '{{ addslashes($agent['name']) }}')"
                                                x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-indigo-50 rounded-lg flex items-center gap-2
                                                       {{ $assignedAgentId == $agent['id'] ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700' }}">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0
                                                    {{ $agent['availability_status'] === 'online' ? 'bg-emerald-400' : ($agent['availability_status'] === 'busy' ? 'bg-amber-400' : 'bg-slate-300') }}">
                                                </span>
                                                {{ $agent['name'] }}
                                                @if($assignedAgentId == $agent['id'])
                                                    <svg class="w-3 h-3 mr-auto text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Team Dropdown --}}
                            @if(!empty($teams))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold
                                           {{ $assignedTeamName ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}
                                           transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="hidden sm:inline max-w-[80px] truncate">{{ $assignedTeamName ?: 'فريق' }}</span>
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute left-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1">
                                        <button wire:click="assignTeam(0, '')" x-on:click="open=false"
                                            class="w-full text-right px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-lg">
                                            بلا فريق
                                        </button>
                                        @foreach($teams as $team)
                                            <button wire:click="assignTeam({{ $team['id'] }}, '{{ addslashes($team['name']) }}')"
                                                x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-purple-50 rounded-lg flex items-center gap-2
                                                       {{ $assignedTeamId == $team['id'] ? 'bg-purple-50 text-purple-700' : 'text-slate-700' }}">
                                                {{ $team['name'] }}
                                                @if($assignedTeamId == $team['id'])
                                                    <svg class="w-3 h-3 mr-auto text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Priority Picker --}}
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"
                                    title="الأولوية">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                                    <span class="hidden sm:inline">أولوية</span>
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute left-0 top-full mt-1 w-36 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                                    <div class="p-1">
                                        @foreach([''=>'بلا', 'low'=>'منخفض', 'medium'=>'متوسط', 'high'=>'مرتفع', 'urgent'=>'عاجل'] as $pval => $plabel)
                                            <button wire:click="setPriority('{{ $pval }}')" x-on:click="open=false"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-slate-50 rounded-lg flex items-center gap-2
                                                       {{ $activeConvPriority === $pval ? 'text-indigo-700 bg-indigo-50' : 'text-slate-700' }}">
                                                @if($pval)
                                                    <span class="w-2 h-2 rounded-full flex-shrink-0
                                                        {{ $pval==='low'?'bg-blue-400':($pval==='medium'?'bg-amber-400':($pval==='high'?'bg-orange-500':'bg-red-500')) }}">
                                                    </span>
                                                @endif
                                                {{ $plabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Labels Picker --}}
                            @if(!empty($allLabels))
                            <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                                <button x-on:click="open=!open"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"
                                    title="الوسوم">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span class="hidden sm:inline">وسوم</span>
                                </button>
                                <div x-show="open" x-transition
                                    class="absolute left-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden max-h-52 overflow-y-auto">
                                    <div class="p-1">
                                        @foreach($allLabels as $labelItem)
                                            <button wire:click="toggleLabel('{{ $labelItem['title'] }}')"
                                                class="w-full text-right px-3 py-2 text-xs font-semibold hover:bg-slate-50 rounded-lg flex items-center gap-2
                                                       {{ in_array($labelItem['title'], $activeConvLabels) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700' }}">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $labelItem['color'] ?? '#6366f1' }}"></span>
                                                {{ $labelItem['title'] }}
                                                @if(in_array($labelItem['title'], $activeConvLabels))
                                                    <svg class="w-3 h-3 mr-auto text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Mute --}}
                            <button wire:click="toggleMute" title="{{ $isMuted ? 'إلغاء الكتم' : 'كتم' }}"
                                class="p-1.5 rounded-lg transition-colors {{ $isMuted ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                @if($isMuted)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3.536-9.536A5 5 0 0112 6"/></svg>
                                @endif
                            </button>

                            {{-- Info Toggle --}}
                            <button wire:click="$toggle('showInfo')" title="معلومات"
                                class="p-1.5 rounded-lg transition-colors {{ $showInfo ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            {{-- Status Toggle --}}
                            <button wire:click="toggleStatus({{ $activeConversationId }})"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors shadow-sm
                                    {{ $convStatus === 'open'
                                        ? 'bg-slate-700 hover:bg-slate-800 text-white'
                                        : 'bg-emerald-500 hover:bg-emerald-600 text-white' }}">
                                @if($convStatus === 'open')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    إغلاق
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    فتح
                                @endif
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── MAIN BODY (messages + optional info panel) ── --}}
                <div class="flex-1 flex min-h-0">

                    {{-- Messages Area --}}
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2"
                             style="scrollbar-width:thin;scrollbar-color:#e2e8f0 transparent; background:#F4F6FA;"
                             x-data="{
                                 atBottom: true,
                                 scrollToBottom() {
                                     this.$nextTick(() => {
                                         this.$el.scrollTop = this.$el.scrollHeight;
                                     });
                                 },
                                 onScroll() {
                                     const el = this.$el;
                                     this.atBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) < 60;
                                 }
                             }"
                             x-init="scrollToBottom()"
                             x-on:messages-loaded.window="atBottom = true; scrollToBottom()"
                             x-on:messages-refreshed.window="if(atBottom) scrollToBottom()"
                             @scroll.passive="onScroll()">

                            @php
                                $prevDate = null;
                            @endphp

                            @forelse($messages as $msg)
                                @php
                                    $msgDate = $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->toDateString() : null;
                                @endphp

                                {{-- Date Separator --}}
                                @if($msgDate && $msgDate !== $prevDate)
                                    @php $prevDate = $msgDate; @endphp
                                    <div class="flex justify-center my-2">
                                        <span class="bg-white text-slate-400 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm border border-slate-100">
                                            {{ \Carbon\Carbon::parse($msg['sent_at'])->isSameDay(now()) ? 'اليوم' : \Carbon\Carbon::parse($msg['sent_at'])->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Activity Message --}}
                                @if($msg['direction'] === 'activity')
                                    <div class="flex justify-center my-1">
                                        <span class="bg-slate-200/70 text-slate-500 text-[10px] font-semibold px-3 py-1 rounded-full">
                                            {{ $msg['content'] }}
                                        </span>
                                    </div>

                                {{-- Private Note --}}
                                @elseif($msg['private'])
                                    <div class="flex justify-start">
                                        <div class="max-w-[72%]">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span class="text-[10px] font-bold text-amber-600">ملاحظة خاصة</span>
                                                @if($msg['sender_name'])
                                                    <span class="text-[10px] text-slate-400">— {{ $msg['sender_name'] }}</span>
                                                @endif
                                            </div>
                                            <div class="bg-amber-50 border border-amber-200 text-slate-800 rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed">
                                                {!! nl2br(e($msg['content'])) !!}
                                                @if(!empty($msg['attachments']))
                                                    @foreach($msg['attachments'] as $att)
                                                        @include('livewire.crm.partials.attachment', ['att' => $att])
                                                    @endforeach
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-slate-400 mt-1 px-1">
                                                {{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}
                                            </p>
                                        </div>
                                    </div>

                                {{-- Outgoing Message (agent) — justify-start = right in RTL --}}
                                @elseif($msg['direction'] === 'out')
                                    <div class="flex justify-start">
                                        <div class="max-w-[72%]">
                                            <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed shadow-sm">
                                                {!! nl2br(e($msg['content'])) !!}
                                                @if(!empty($msg['attachments']))
                                                    @foreach($msg['attachments'] as $att)
                                                        @include('livewire.crm.partials.attachment', ['att' => $att])
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1 px-1">
                                                <p class="text-[10px] text-slate-400">
                                                    {{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}
                                                </p>
                                                {{-- Delivery status --}}
                                                @if(($msg['status'] ?? 'sent') === 'read')
                                                    {{-- صحين زرق = مقروءة --}}
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 26 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 12l4 4L16 6"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l4 4L23 6"/>
                                                    </svg>
                                                @elseif(($msg['status'] ?? 'sent') === 'delivered')
                                                    {{-- صحين رمادي = وصلت --}}
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 26 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 12l4 4L16 6"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l4 4L23 6"/>
                                                    </svg>
                                                @else
                                                    {{-- صح واحد رمادي = أُرسلت --}}
                                                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                {{-- Incoming Message (customer) — justify-end = left in RTL --}}
                                @else
                                    <div class="flex justify-end">
                                        <div class="max-w-[72%]">
                                            @if($msg['sender_name'])
                                                <p class="text-[10px] font-bold text-slate-500 mb-1 px-1 text-left">{{ $msg['sender_name'] }}</p>
                                            @endif
                                            <div class="bg-white text-slate-800 rounded-2xl rounded-tl-sm px-4 py-3 text-sm leading-relaxed border border-slate-100 shadow-sm">
                                                {!! nl2br(e($msg['content'])) !!}
                                                @if(!empty($msg['attachments']))
                                                    @foreach($msg['attachments'] as $att)
                                                        @include('livewire.crm.partials.attachment', ['att' => $att])
                                                    @endforeach
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-slate-400 mt-1 px-1 text-left">
                                                {{ $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at'])->format('H:i') : '' }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                            @empty
                                <div class="flex flex-col items-center justify-center h-full py-20 text-slate-400">
                                    <svg class="w-10 h-10 mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <p class="text-sm font-semibold">لا توجد رسائل بعد</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- ── COMPOSER ── --}}
                        <div class="bg-white border-t border-slate-100 flex-shrink-0">

                            {{-- Tabs: Reply / Private Note --}}
                            <div class="flex border-b border-slate-100">
                                <button wire:click="$set('isPrivateNote', false)"
                                    class="px-4 py-2.5 text-xs font-bold transition-colors border-b-2
                                           {{ !$isPrivateNote ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                                    رد
                                </button>
                                <button wire:click="$set('isPrivateNote', true)"
                                    class="px-4 py-2.5 text-xs font-bold transition-colors border-b-2 flex items-center gap-1.5
                                           {{ $isPrivateNote ? 'border-amber-500 text-amber-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    ملاحظة خاصة
                                </button>
                            </div>

                            {{-- Canned Responses Dropdown --}}
                            @if($showCanned)
                                <div class="border-b border-slate-100 bg-slate-50 max-h-44 overflow-y-auto">
                                    <div class="p-2">
                                        <input type="text" wire:model.live.debounce.200ms="cannedSearch"
                                            placeholder="بحث في الردود الجاهزة..."
                                            class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 mb-2">
                                        @forelse($cannedResponses as $cr)
                                            <button wire:click="selectCannedResponse({{ $cr['id'] }})"
                                                class="w-full text-right px-3 py-2 text-xs hover:bg-white rounded-lg transition-colors block">
                                                <span class="font-bold text-indigo-700">{{ $cr['title'] }}</span>
                                                <span class="text-slate-500 mr-2 truncate">{{ Str::limit($cr['content'], 60) }}</span>
                                            </button>
                                        @empty
                                            <p class="text-xs text-center text-slate-400 py-2">لا توجد نتائج</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- Input Row --}}
                            <div class="p-3">
                                <form wire:submit.prevent="sendMessage">
                                    <div class="flex items-end gap-2">
                                        {{-- Emoji Button --}}
                                        <div class="relative flex-shrink-0" x-data="{ emojiOpen: false }" x-on:click.outside="emojiOpen = false">
                                            <button type="button" x-on:click="emojiOpen = !emojiOpen"
                                                class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                                :class="emojiOpen && 'bg-indigo-50 text-indigo-500'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                            {{-- Emoji Grid --}}
                                            <div x-show="emojiOpen" x-transition
                                                 class="absolute bottom-11 right-0 bg-white rounded-xl shadow-xl border border-slate-100 p-3 z-50 w-64">
                                                <div class="grid grid-cols-10 gap-0.5">
                                                    <template x-for="e in $root.emojis" :key="e">
                                                        <button type="button"
                                                            x-on:click="$wire.set('newMessage', $wire.newMessage + e); emojiOpen = false"
                                                            class="text-lg hover:bg-slate-100 rounded-lg p-0.5 transition-colors"
                                                            x-text="e"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Canned Responses Toggle --}}
                                        <button type="button" wire:click="$toggle('showCanned')"
                                            title="ردود جاهزة"
                                            class="flex-shrink-0 p-2 rounded-lg transition-colors {{ $showCanned ? 'text-indigo-600 bg-indigo-50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </button>

                                        {{-- Text Area --}}
                                        <textarea wire:model="newMessage"
                                            rows="1"
                                            placeholder="{{ $isPrivateNote ? 'اكتب ملاحظة خاصة...' : 'اكتب رسالتك...' }}"
                                            class="flex-1 bg-slate-50 border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none leading-relaxed placeholder:text-slate-400
                                                   {{ $isPrivateNote ? 'border-amber-200 focus:border-amber-400 bg-amber-50/30' : 'border-slate-200 focus:border-indigo-400' }}"
                                            style="min-height:42px;max-height:120px;"
                                            x-data
                                            x-on:input="$el.style.height='42px'; $el.style.height=Math.min($el.scrollHeight,120)+'px'"></textarea>

                                        {{-- Send Button --}}
                                        <button type="submit"
                                            class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-colors
                                                   {{ $isPrivateNote
                                                       ? 'bg-amber-500 hover:bg-amber-600 text-white'
                                                       : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            <span class="hidden sm:inline">إرسال</span>
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
                         class="w-72 bg-white border-r border-slate-100 shadow-sm flex flex-col overflow-y-auto flex-shrink-0"
                         style="scrollbar-width:thin;">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800">تفاصيل المحادثة</h4>
                            <button wire:click="$set('showInfo', false)" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-4 space-y-4">
                            {{-- Contact Info --}}
                            <div>
                                <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">معلومات العميل</h5>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الاسم</span>
                                        <span class="text-xs font-bold text-slate-700 truncate">{{ $activeConvData['client_name'] }}</span>
                                    </div>
                                    @if($activeConvData['client_phone'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الهاتف</span>
                                        <span class="text-xs font-bold text-slate-700" dir="ltr">{{ $activeConvData['client_phone'] }}</span>
                                    </div>
                                    @endif
                                    @if($activeConvData['client_email'] ?? '')
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الإيميل</span>
                                        <span class="text-xs font-bold text-slate-700 truncate">{{ $activeConvData['client_email'] }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Conversation Info --}}
                            <div>
                                <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">المحادثة</h5>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">المعرّف</span>
                                        <span class="text-xs font-bold text-slate-700">#{{ $activeConvData['id'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الحالة</span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full border {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    @if($activeConvData['last_message_at'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">آخر نشاط</span>
                                        <span class="text-xs text-slate-600">{{ \Carbon\Carbon::parse($activeConvData['last_message_at'])->diffForHumans() }}</span>
                                    </div>
                                    @endif
                                    @if($assignedAgentName)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الوكيل</span>
                                        <span class="text-xs font-bold text-indigo-600">{{ $assignedAgentName }}</span>
                                    </div>
                                    @endif
                                    @if($assignedTeamName)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 w-16 flex-shrink-0">الفريق</span>
                                        <span class="text-xs font-bold text-purple-600">{{ $assignedTeamName }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Labels --}}
                            <div>
                                <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">الوسوم</h5>
                                @if(!empty($activeConvLabels))
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach($activeConvLabels as $lbl)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700">
                                                {{ $lbl }}
                                                <button wire:click="toggleLabel('{{ $lbl }}')" class="hover:text-indigo-900">
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
                                                <button wire:click="toggleLabel('{{ $lItem['title'] }}')"
                                                    class="text-[10px] font-bold px-2 py-0.5 rounded-lg border border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
                                                    + {{ $lItem['title'] }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Priority Selector --}}
                            <div>
                                <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">الأولوية</h5>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach([''=>'بلا', 'low'=>'منخفض', 'medium'=>'متوسط', 'high'=>'مرتفع', 'urgent'=>'عاجل'] as $pv => $pl)
                                        <button wire:click="setPriority('{{ $pv }}')"
                                            class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition-colors
                                                   {{ $activeConvPriority === $pv
                                                       ? 'bg-indigo-600 text-white border-indigo-600'
                                                       : 'border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600' }}">
                                            {{ $pl }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- end main body --}}
            @endif
        @endif
    </div>{{-- end chat area --}}

</div>
