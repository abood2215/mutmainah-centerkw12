<div dir="rtl" class="p-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center shadow-md">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-black text-slate-900">ساعات العمل</h1>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">تحديد أوقات العمل والرد التلقائي خارجها</p>
        </div>
    </div>

    {{-- Success Messages --}}
    @if(session('hours_saved'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs font-bold text-emerald-700">{{ session('hours_saved') }}</span>
        </div>
    @endif
    @if(session('reply_saved'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs font-bold text-emerald-700">{{ session('reply_saved') }}</span>
        </div>
    @endif

    {{-- Business Hours Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
        <h2 class="text-sm font-black text-slate-800 mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            أيام وساعات العمل
        </h2>

        <div class="space-y-3">
            @foreach($hours as $day => $data)
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $data['is_active'] ? 'bg-indigo-50/50 border border-indigo-100' : 'bg-slate-50 border border-slate-100' }} transition-all">

                    {{-- Toggle --}}
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox"
                            wire:model="hours.{{ $day }}.is_active"
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer
                            peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white
                            after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300
                            after:border after:rounded-full after:h-4 after:w-4 after:transition-all
                            peer-checked:bg-indigo-600"></div>
                    </label>

                    {{-- Day Name --}}
                    <span class="text-sm font-black w-20 flex-shrink-0 {{ $data['is_active'] ? 'text-slate-800' : 'text-slate-400' }}">
                        {{ $data['day_name'] }}
                    </span>

                    {{-- Time Pickers --}}
                    @if($data['is_active'])
                        <div class="flex items-center gap-2 flex-1">
                            <input type="time"
                                wire:model="hours.{{ $day }}.start_time"
                                class="bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 transition-all">
                            <span class="text-xs text-slate-400 font-bold">—</span>
                            <input type="time"
                                wire:model="hours.{{ $day }}.end_time"
                                class="bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 transition-all">
                        </div>
                    @else
                        <span class="text-xs text-slate-400 font-semibold flex-1">إجازة</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-end">
            <button wire:click="saveHours"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-xl transition-all shadow-sm active:scale-95">
                حفظ ساعات العمل
            </button>
        </div>
    </div>

    {{-- Auto-reply Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                الرد التلقائي خارج ساعات العمل
            </h2>
            {{-- Active Toggle --}}
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="autoReplyIsActive" class="sr-only peer">
                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer
                    peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white
                    after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300
                    after:border after:rounded-full after:h-4 after:w-4 after:transition-all
                    peer-checked:bg-emerald-500"></div>
                <span class="mr-2 text-xs font-bold text-slate-600">{{ $autoReplyIsActive ? 'مفعّل' : 'معطّل' }}</span>
            </label>
        </div>

        <div class="bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5 mb-4 flex items-start gap-2">
            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-amber-700 font-semibold">
                يُرسل هذا الرد تلقائياً لأي رسالة واردة خارج ساعات العمل المحددة أعلاه.
            </p>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">نص الرسالة التلقائية</label>
            <textarea wire:model="autoReplyMessage" rows="5"
                placeholder="مثال: شكراً لتواصلك معنا! مواعيد عملنا من الأحد إلى الخميس 9ص - 5م. سنرد عليك في أقرب وقت ممكن."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 transition-all resize-none"></textarea>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="saveAutoReply"
                class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-black rounded-xl transition-all shadow-sm active:scale-95">
                حفظ الرد التلقائي
            </button>
        </div>
    </div>
</div>
