<div dir="rtl" class="flex bg-[#F0F2FF] overflow-hidden" style="height: calc(100vh - 53px);"
     wire:poll.3000ms="refreshAll"
     x-data="{ showChat: {{ ($activeConversationId || $pendingClientPhone) ? 'true' : 'false' }} }"
     x-on:livewire:updated.window="if ($wire.activeConversationId) showChat = true">

    <!-- Conversations List -->
    <div class="bg-white border-l border-slate-200 flex flex-col shadow-sm flex-shrink-0
                w-full md:w-[300px] lg:w-[320px]"
         :class="showChat ? 'hidden md:flex' : 'flex'">

        <!-- Header -->
        <div class="px-4 py-4 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">صندوق الرسائل</h2>
                <span class="bg-indigo-600 text-white text-xs font-black px-2.5 py-1 rounded-full shadow-sm">
                    {{ count($conversations) }}
                </span>
            </div>

            <!-- Search -->
            <div class="mt-3 relative">
                <input type="text" wire:model.live="searchQuery"
                    placeholder="بحث باسم أو رقم..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 pr-8">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute top-2.5 right-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-1.5 mt-3">
                <button wire:click="$set('filterStatus', '')"
                    class="text-[10px] font-black px-2.5 py-1.5 rounded-lg transition-all {{ $filterStatus === '' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    الكل
                </button>
                <button wire:click="$set('filterStatus', 'open')"
                    class="text-[10px] font-black px-2.5 py-1.5 rounded-lg transition-all {{ $filterStatus === 'open' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    مفتوح
                </button>
                <button wire:click="$set('filterStatus', 'resolved')"
                    class="text-[10px] font-black px-2.5 py-1.5 rounded-lg transition-all {{ $filterStatus === 'resolved' ? 'bg-slate-500 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    مغلق
                </button>
                <span class="mr-auto text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-100 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    Live
                </span>
            </div>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-3 space-y-1">
            @forelse($filteredConversations as $conv)
                <div wire:click="selectConversation({{ $conv['id'] }})"
                     x-on:click="showChat = true"
                     class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border-2
                        {{ $activeConversationId == $conv['id']
                            ? 'bg-indigo-50 border-indigo-200'
                            : 'border-transparent hover:bg-slate-50' }}">

                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-black shadow-sm">
                            {{ mb_substr($conv['client_name'], 0, 1) }}
                        </div>
                        @if($conv['unread'] > 0)
                            <span class="absolute -top-1 -left-1 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">
                                {{ $conv['unread'] }}
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 truncate {{ $conv['unread'] > 0 ? 'text-indigo-700' : '' }}">
                                {{ $conv['client_name'] }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-semibold flex-shrink-0 mr-1">
                                {{ $conv['last_message_at'] ? \Carbon\Carbon::parse($conv['last_message_at'])->diffForHumans(null, true) : '—' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.925 0A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                            </svg>
                            <span class="text-[10px] text-slate-400 font-semibold truncate" dir="ltr">
                                {{ $conv['client_phone'] ?: 'WhatsApp' }}
                            </span>
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $conv['status'] === 'open' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="text-xs font-bold">لا توجد محادثات</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col overflow-hidden"
         :class="showChat ? 'flex' : 'hidden md:flex'">

        @if($activeConversationId && $activeConvData)
            <!-- Chat Header -->
            <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <button x-on:click="showChat = false" class="md:hidden w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-black shadow-sm">
                        {{ mb_substr($activeConvData['client_name'] ?? '', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900">{{ $activeConvData['client_name'] ?? '' }}</h2>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ ($activeConvData['status'] ?? '') === 'open' ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                            <span class="text-xs text-slate-500 font-semibold" dir="ltr">{{ $activeConvData['client_phone'] ?? '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @php
                        $rawPhone = preg_replace('/[^0-9]/', '', $activeConvData['client_phone'] ?? '');
                        $crmClient = null;
                        if ($rawPhone) {
                            $crmClient = \App\Models\CrmClient::all()
                                ->first(fn($c) => preg_replace('/[^0-9]/', '', $c->phone ?? '') === $rawPhone);
                        }
                    @endphp
                    @if($crmClient)
                        <a href="{{ route('crm.client-show', $crmClient->id) }}"
                           class="text-xs font-black px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-all hidden sm:flex items-center gap-1">
                            ملف العميل ←
                        </a>
                    @endif

                    <button wire:click="toggleStatus({{ $activeConversationId }})"
                        class="text-xs font-black px-3 py-1.5 rounded-xl transition-all border
                            {{ ($activeConvData['status'] ?? '') === 'open'
                                ? 'bg-white border-slate-200 text-slate-600 hover:bg-red-50 hover:border-red-200 hover:text-red-600'
                                : 'bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600' }}">
                        {{ ($activeConvData['status'] ?? '') === 'open' ? 'إغلاق' : 'فتح' }}
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 no-scrollbar bg-[#ECE5DD]"
                 x-data="{ lastCount: {{ count($messages) }} }"
                 x-init="$el.scrollTop = $el.scrollHeight"
                 x-on:livewire:updated.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">

                @php $prevDate = null; @endphp

                @forelse($messages as $msg)
                    @php
                        $msgDate = $msg['sent_at'] ? \Carbon\Carbon::parse($msg['sent_at']) : null;
                        $showDate = $msgDate && $msgDate->toDateString() !== $prevDate;
                        $prevDate = $msgDate?->toDateString();
                    @endphp

                    {{-- Date Separator --}}
                    @if($showDate)
                        <div class="flex justify-center my-3">
                            <span class="bg-white/80 text-slate-500 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">
                                {{ $msgDate->isToday() ? 'اليوم' : ($msgDate->isYesterday() ? 'أمس' : $msgDate->format('d/m/Y')) }}
                            </span>
                        </div>
                    @endif

                    {{-- Activity / System Message --}}
                    @if($msg['direction'] === 'activity')
                        <div class="flex justify-center my-2">
                            <span class="bg-white/70 text-slate-500 text-[10px] font-semibold px-3 py-1.5 rounded-full shadow-sm max-w-xs text-center">
                                {{ $msg['content'] }}
                            </span>
                        </div>

                    {{-- Private Note --}}
                    @elseif($msg['private'] ?? false)
                        <div class="flex mb-2 justify-start">
                            <div class="relative max-w-[75%] sm:max-w-[65%]">
                                <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed shadow-sm bg-amber-100 border border-amber-200 text-slate-800 rounded-tl-sm">
                                    <div class="flex items-center gap-1 mb-1">
                                        <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-[10px] font-black text-amber-700">ملاحظة خاصة</span>
                                    </div>
                                    @if($msg['content'])
                                        <p class="whitespace-pre-wrap break-words">{{ $msg['content'] }}</p>
                                    @endif
                                    @include('livewire.crm.inbox-attachments', ['attachments' => $msg['attachments'] ?? []])
                                    <div class="flex items-center gap-1 mt-0.5 justify-start">
                                        <span class="text-[10px] text-amber-600">{{ $msgDate ? $msgDate->format('H:i') : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- Regular Message --}}
                    @else
                        <div class="flex mb-1 {{ $msg['direction'] === 'out' ? 'justify-start' : 'justify-end' }}">
                            <div class="relative max-w-[75%] sm:max-w-[65%]">
                                @php $status = $msg['status'] ?? 'sent'; @endphp
                                <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed shadow-sm
                                    {{ $msg['direction'] === 'out'
                                        ? ($status === 'failed'
                                            ? 'bg-red-100 border border-red-300 text-slate-800 rounded-tl-sm'
                                            : 'bg-[#D9FDD3] text-slate-800 rounded-tl-sm')
                                        : 'bg-white text-slate-800 rounded-tr-sm' }}">

                                    {{-- Sender name for incoming --}}
                                    @if($msg['direction'] === 'in' && ($msg['sender_name'] ?? ''))
                                        <p class="text-[10px] font-black text-emerald-600 mb-1">{{ $msg['sender_name'] }}</p>
                                    @endif

                                    {{-- Message content --}}
                                    @if($msg['content'])
                                        <p class="whitespace-pre-wrap break-words">{{ $msg['content'] }}</p>
                                    @endif

                                    {{-- Attachments --}}
                                    @php $attachments = $msg['attachments'] ?? []; @endphp
                                    @foreach($attachments as $att)
                                        @php $fileType = $att['file_type'] ?? ''; @endphp
                                        @if($fileType === 'image')
                                            <a href="{{ $att['data_url'] ?? $att['file_url'] ?? '#' }}" target="_blank" class="block mt-1">
                                                <img src="{{ $att['thumb_url'] ?? $att['data_url'] ?? $att['file_url'] ?? '' }}"
                                                     class="rounded-xl max-w-full max-h-48 object-cover" alt="صورة">
                                            </a>
                                        @elseif($fileType === 'audio')
                                            <audio controls class="mt-1 w-full max-w-xs" style="height:36px;">
                                                <source src="{{ $att['data_url'] ?? $att['file_url'] ?? '' }}">
                                            </audio>
                                        @elseif($fileType === 'contact')
                                            <div class="mt-1 bg-white/60 rounded-xl p-2 border border-slate-200 flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-slate-300 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-slate-600" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-slate-700">{{ $att['file_name'] ?? 'جهة اتصال' }}</p>
                                                    @if($att['phone_number'] ?? '')
                                                        <p class="text-[10px] text-slate-500" dir="ltr">{{ $att['phone_number'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif(in_array($fileType, ['file', 'document']))
                                            <a href="{{ $att['data_url'] ?? $att['file_url'] ?? '#' }}" target="_blank"
                                               class="mt-1 flex items-center gap-2 bg-white/60 rounded-xl p-2 border border-slate-200 hover:bg-white/80 transition-all">
                                                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-xs font-semibold text-slate-700 truncate">{{ $att['file_name'] ?? 'ملف' }}</span>
                                            </a>
                                        @elseif($fileType === 'video')
                                            <video controls class="mt-1 rounded-xl max-w-full max-h-48">
                                                <source src="{{ $att['data_url'] ?? $att['file_url'] ?? '' }}">
                                            </video>
                                        @endif
                                    @endforeach

                                    {{-- Timestamp + Status --}}
                                    <div class="flex items-center gap-1 mt-0.5
                                        {{ $msg['direction'] === 'out' ? 'justify-start' : 'justify-end' }}">
                                        <span class="text-[10px] text-slate-400">
                                            {{ $msgDate ? $msgDate->format('H:i') : '' }}
                                        </span>
                                        @if($msg['direction'] === 'out')
                                            @if($status === 'failed')
                                                {{-- Failed --}}
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($status === 'read')
                                                {{-- Read - double blue --}}
                                                <svg class="w-4 h-3.5 text-teal-500" viewBox="0 0 20 11" fill="currentColor">
                                                    <path d="M7.071 8.653a.75.75 0 0 1-.025-1.06L12.46 2.25a.75.75 0 1 1 1.085 1.036L7.096 8.678a.75.75 0 0 1-1.025-.025zM1.071 8.653a.75.75 0 0 1-.025-1.06L6.46 2.25a.75.75 0 1 1 1.085 1.036L2.096 8.678a.75.75 0 0 1-1.025-.025z"/>
                                                </svg>
                                            @elseif($status === 'delivered')
                                                {{-- Delivered - double grey --}}
                                                <svg class="w-4 h-3.5 text-slate-400" viewBox="0 0 20 11" fill="currentColor">
                                                    <path d="M7.071 8.653a.75.75 0 0 1-.025-1.06L12.46 2.25a.75.75 0 1 1 1.085 1.036L7.096 8.678a.75.75 0 0 1-1.025-.025zM1.071 8.653a.75.75 0 0 1-.025-1.06L6.46 2.25a.75.75 0 1 1 1.085 1.036L2.096 8.678a.75.75 0 0 1-1.025-.025z"/>
                                                </svg>
                                            @else
                                                {{-- Sent - single grey --}}
                                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 16 11" fill="currentColor">
                                                    <path d="M11.071.653a.75.75 0 0 1 .025 1.06l-6.5 7a.75.75 0 0 1-1.085 0l-3-3.228a.75.75 0 1 1 1.085-1.036L4.02 7.147l5.966-6.47a.75.75 0 0 1 1.086-.024z"/>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-3 h-3 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.925 0A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <p class="text-sm font-bold">لا توجد رسائل</p>
                    </div>
                @endforelse
            </div>

            <!-- Input Area -->
            <div class="bg-[#F0F2F5] border-t border-slate-200"
                 x-data="{
                    showEmoji: false,
                    emojis: ['😊','😂','❤️','👍','🙏','😍','🥰','😘','😭','😅','😁','🤔','😢','😎','🙄','🤣','😋','😉','🥳','😀','👏','🎉','🔥','💪','✅','👌','💯','🌟','⭐','💫','🚀','💙','💚','💛','🧡','💜','🖤','🤍','😴','🤗'],
                    addEmoji(e) { $wire.set('newMessage', $wire.newMessage + e); this.showEmoji = false; }
                 }">

                {{-- Reply / Private Note Tabs --}}
                <div class="flex border-b border-slate-200 bg-white px-3 pt-2">
                    <button wire:click="$set('isPrivateNote', false)"
                        class="text-xs font-black px-3 py-1.5 border-b-2 transition-all mr-1
                            {{ !$isPrivateNote ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                        رد
                    </button>
                    <button wire:click="$set('isPrivateNote', true)"
                        class="text-xs font-black px-3 py-1.5 border-b-2 transition-all flex items-center gap-1
                            {{ $isPrivateNote ? 'border-amber-500 text-amber-700' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        ملاحظة خاصة
                    </button>
                </div>

                {{-- Private Note Warning --}}
                @if($isPrivateNote)
                    <div class="bg-amber-50 border-b border-amber-200 px-3 py-1.5 flex items-center gap-2">
                        <svg class="w-3 h-3 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-[10px] font-semibold text-amber-700">هذه الرسالة لن تُرسل للعميل — ستظهر للفريق فقط</span>
                    </div>
                @endif

                {{-- Emoji Picker --}}
                <div class="relative" x-show="showEmoji" x-on:click.outside="showEmoji = false"
                     style="display:none">
                    <div class="absolute bottom-2 right-3 bg-white border border-slate-200 rounded-2xl shadow-xl p-3 z-50 w-72"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">
                        <div class="grid grid-cols-8 gap-1">
                            <template x-for="e in emojis" :key="e">
                                <button type="button"
                                    x-on:click="addEmoji(e)"
                                    class="w-8 h-8 text-xl flex items-center justify-center rounded-lg hover:bg-slate-100 transition-all"
                                    x-text="e"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="sendMessage" class="flex items-end gap-2 px-3 py-2">
                    {{-- Emoji Button --}}
                    <button type="button" x-on:click="showEmoji = !showEmoji"
                        class="w-9 h-9 flex-shrink-0 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 transition-all text-xl">
                        😊
                    </button>

                    {{-- Text Input --}}
                    <div class="flex-1 rounded-2xl border px-4 py-2.5 flex items-center shadow-sm
                        {{ $isPrivateNote ? 'bg-amber-50 border-amber-200' : 'bg-white border-slate-200' }}">
                        <input type="text" wire:model="newMessage"
                            placeholder="{{ $isPrivateNote ? 'اكتب ملاحظة خاصة...' : 'اكتب رسالة...' }}"
                            x-on:keydown.enter.prevent="$wire.sendMessage()"
                            class="flex-1 bg-transparent text-slate-800 text-sm font-medium focus:outline-none placeholder:text-slate-400">
                    </div>

                    {{-- Send Button --}}
                    <button type="submit"
                        class="w-10 h-10 text-white rounded-full flex items-center justify-center shadow-md active:scale-95 transition-all flex-shrink-0
                            {{ $isPrivateNote ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                        <svg class="w-4 h-4 rotate-180" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </form>
            </div>

        @elseif($pendingClientPhone)
            <!-- New Conversation UI -->
            <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <button x-on:click="showChat = false" class="md:hidden w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-black shadow-sm">
                        {{ $pendingClientName ? mb_substr($pendingClientName, 0, 1) : '?' }}
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900">{{ $pendingClientName ?: 'عميل جديد' }}</h2>
                        <span class="text-xs text-slate-500 font-semibold" dir="ltr">{{ $pendingClientPhone }}</span>
                    </div>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700">
                    محادثة جديدة
                </span>
            </div>

            <div class="flex-1 flex flex-col items-center justify-center bg-[#ECE5DD] gap-3 p-6">
                <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                    <svg class="w-8 h-8 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                    </svg>
                </div>
                <p class="text-sm font-black text-slate-600">لا توجد محادثة سابقة</p>
                <p class="text-xs text-slate-400 font-semibold text-center">اكتب رسالتك الأولى أدناه لبدء محادثة مع<br><span class="text-slate-700 font-black">{{ $pendingClientName ?: $pendingClientPhone }}</span></p>
            </div>

            <div class="bg-[#F0F2F5] border-t border-slate-200 px-3 py-2">
                <form wire:submit.prevent="sendMessage" class="flex items-end gap-2">
                    <div class="flex-1 bg-white rounded-2xl border border-slate-200 px-4 py-2.5 flex items-center shadow-sm">
                        <input type="text" wire:model="newMessage"
                            placeholder="اكتب رسالتك الأولى..."
                            x-on:keydown.enter.prevent="$wire.sendMessage()"
                            class="flex-1 bg-transparent text-slate-800 text-sm font-medium focus:outline-none placeholder:text-slate-400">
                    </div>
                    <button type="submit"
                        class="w-10 h-10 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-md active:scale-95 transition-all flex-shrink-0">
                        <svg class="w-4 h-4 rotate-180" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </form>
            </div>

        @elseif($activeConversationId)
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-6" wire:loading>
                <div class="w-10 h-10 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin mb-3"></div>
                <p class="text-sm font-bold text-slate-500">جاري تحميل المحادثة...</p>
            </div>

        @else
            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-6">
                <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200 flex items-center justify-center mb-4 shadow-sm">
                    <svg class="w-8 h-8 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-slate-600 mb-1">اختر محادثة</h3>
                <p class="text-sm text-slate-400 font-semibold text-center">المحادثات مباشرة من Chatwoot</p>
                <div class="mt-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-xs font-bold text-emerald-700 text-center">
                    متصل بـ: {{ config('chatwoot.url') }}<br>
                    Account: {{ config('chatwoot.account_id') }} | Inbox: {{ config('chatwoot.inbox_id') }}
                </div>
            </div>
        @endif
    </div>
</div>
