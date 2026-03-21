<div dir="rtl" class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black text-slate-900">الردود الجاهزة</h1>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">قوالب الرسائل المتكررة للرد السريع</p>
        </div>
        <span class="bg-indigo-600 text-white text-xs font-black px-3 py-1.5 rounded-full shadow-sm">
            {{ count($responses) }} رد
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Left Column: Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-black text-slate-800 mb-4 flex items-center gap-2">
                    @if($editingId)
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        تعديل الرد
                    @else
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        إضافة رد جديد
                    @endif
                </h2>

                <form wire:submit.prevent="saveResponse" class="space-y-4">

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">العنوان / الاسم المختصر</label>
                        <input type="text" wire:model="title"
                            placeholder="مثال: ترحيب - تأكيد موعد - متابعة..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 transition-all">
                        @error('title')
                            <p class="text-[10px] text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Content --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">نص الرسالة</label>
                        <textarea wire:model="content" rows="6"
                            placeholder="اكتب نص الرسالة الجاهزة هنا..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 transition-all resize-none"></textarea>
                        @error('content')
                            <p class="text-[10px] text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-black text-white transition-all
                                {{ $editingId ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                            {{ $editingId ? 'حفظ التعديلات' : 'إضافة الرد' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit"
                                class="px-4 py-2.5 rounded-xl text-sm font-black text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all">
                                إلغاء
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: List --}}
        <div class="lg:col-span-3 flex flex-col gap-4">

            {{-- Search --}}
            <div class="relative">
                <input type="text" wire:model.live="search"
                    placeholder="بحث في الردود..."
                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400 pr-9 shadow-sm">
                <svg class="w-4 h-4 text-slate-400 absolute top-3 right-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Response Cards --}}
            @forelse($filteredResponses as $response)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 group hover:border-indigo-200 transition-all
                    {{ $editingId === $response['id'] ? 'border-amber-300 bg-amber-50/30' : '' }}">

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-black text-slate-900 truncate mb-1">
                                {{ $response['title'] }}
                            </h3>
                            <p class="text-xs text-slate-500 font-semibold leading-relaxed line-clamp-2">
                                {{ $response['content'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button wire:click="editResponse({{ $response['id'] }})"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-100 hover:text-indigo-600 text-slate-500 flex items-center justify-center transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="deleteResponse({{ $response['id'] }})"
                                wire:confirm="هل أنت متأكد من حذف هذا الرد؟"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-600 text-slate-500 flex items-center justify-center transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-slate-400 bg-white rounded-2xl border border-slate-100">
                    <svg class="w-10 h-10 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="text-sm font-bold">
                        @if($search)
                            لا توجد نتائج لـ "{{ $search }}"
                        @else
                            لا توجد ردود جاهزة بعد
                        @endif
                    </p>
                    @if(!$search)
                        <p class="text-xs font-semibold mt-1 text-slate-300">أضف ردك الأول من النموذج على اليسار</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
