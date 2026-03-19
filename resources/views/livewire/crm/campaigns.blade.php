<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-6 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900">الحملات <span class="text-indigo-600">الذكية</span></h1>
            <p class="text-sm text-slate-500 font-semibold mt-1">إرسال جماعي عبر WhatsApp — Loving Homes Marketing</p>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Form -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-gradient-to-l from-indigo-50 to-white">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                        إعداد حملة جديدة
                    </h2>
                </div>

                <form wire:submit.prevent="createCampaign" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">عنوان الحملة</label>
                        <input type="text" wire:model.defer="title"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400"
                            placeholder="مثال: عروض رمضان 2026">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">نوع الحملة</label>
                        <select wire:model.defer="type"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                            <option value="promotional">ترويجية</option>
                            <option value="reminder">تذكير</option>
                            <option value="followup">متابعة</option>
                            <option value="occasion">مناسبة</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">فلتر الاستهداف</label>
                        <select wire:model.live="targetFilter"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                            <option value="all">كل العملاء</option>
                            <option value="stage">مرحلة محددة</option>
                            <option value="source">مصدر محدد</option>
                            <option value="inactive">غير نشطين</option>
                            <option value="consultant">موظف معين</option>
                            <option value="specific">أرقام هواتف محددة</option>
                        </select>
                        @if($targetFilter != 'all')
                            <input type="text" wire:model.live="targetValue"
                                placeholder="قيمة الفلتر..."
                                class="w-full mt-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400">
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">نص الرسالة</label>
                        <textarea wire:model.defer="message" rows="4"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 resize-none placeholder:text-slate-400"
                            placeholder="اكتب نص الرسالة..."></textarea>
                    </div>

                    <!-- Audience Count -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-black text-indigo-700 uppercase tracking-widest">الجمهور المستهدف</div>
                            <div class="text-xs text-indigo-500 font-semibold mt-0.5">سيتم الإرسال لـ</div>
                        </div>
                        <div class="text-3xl font-black text-indigo-700">{{ $this->audienceCount }}<span class="text-sm font-bold"> عميل</span></div>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-black shadow-lg shadow-indigo-500/25 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        جدولة وإطلاق الحملة
                    </button>
                </form>
            </div>
        </div>

        <!-- Campaigns History -->
        <div class="lg:col-span-8">
            <h2 class="text-base font-black text-slate-700 mb-4">سجل الحملات</h2>

            @if(count($campaigns) == 0)
                <div class="bg-white rounded-2xl border border-slate-200 py-20 text-center shadow-sm">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <p class="text-sm font-bold text-slate-400">لا توجد حملات بعد</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($campaigns as $campaign)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg uppercase">{{ $campaign->type }}</span>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full {{ $campaign->status == 'sent' ? 'bg-emerald-500' : 'bg-amber-500' }} animate-pulse"></div>
                                    <span class="text-xs font-bold {{ $campaign->status == 'sent' ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $campaign->status == 'sent' ? 'مرسلة' : ($campaign->status == 'scheduled' ? 'مجدولة' : $campaign->status) }}
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-base font-black text-slate-900 mb-2">{{ $campaign->title }}</h3>
                            <p class="text-xs text-slate-500 font-semibold leading-relaxed mb-4 line-clamp-2">{{ $campaign->message }}</p>

                            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
                                <div class="bg-slate-50 rounded-xl p-3">
                                    <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">المستلمون</div>
                                    <div class="text-2xl font-black text-slate-800">{{ $campaign->recipients_count }}</div>
                                </div>
                                <div class="bg-indigo-50 rounded-xl p-3">
                                    <div class="text-[10px] text-indigo-500 font-black uppercase tracking-widest mb-1">الردود</div>
                                    <div class="text-2xl font-black text-indigo-700">{{ $campaign->replies_count }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
