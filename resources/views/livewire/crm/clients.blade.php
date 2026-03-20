<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-4 lg:p-8 animate-slide-up">

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm font-bold px-4 py-3 rounded-xl">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header -->
    <header class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-slate-900">سجل <span class="text-indigo-600">العملاء</span></h1>
                <p class="text-xs lg:text-sm text-slate-500 font-semibold mt-0.5">Loving Homes Client Registry</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="export"
                        class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 lg:px-5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-black shadow-lg shadow-emerald-500/25 active:scale-95 transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden sm:inline">تصدير CSV</span>
                </button>
                <button wire:click="openModal"
                        class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3 lg:px-5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-black shadow-lg shadow-indigo-500/25 active:scale-95 transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>عميل جديد</span>
                </button>
            </div>
        </div>

        <!-- Filters Row -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <select wire:model.live="filterStage" class="text-xs font-bold text-slate-700 bg-transparent outline-none border-none">
                    <option value="">كل المراحل</option>
                    <option value="new">جديد</option>
                    <option value="contacted">تم التواصل</option>
                    <option value="interested">مهتم</option>
                    <option value="booked">محجوز</option>
                    <option value="active">نشط</option>
                    <option value="followup">متابعة</option>
                    <option value="completed">مكتمل</option>
                </select>
            </div>

            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                <select wire:model.live="filterSource" class="text-xs font-bold text-slate-700 bg-transparent outline-none border-none">
                    <option value="">كل المصادر</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                    <option value="referral">إحالة</option>
                    <option value="direct">مباشر</option>
                    <option value="website">الموقع</option>
                </select>
            </div>

            <div class="relative flex-1 min-w-[160px]">
                <svg class="w-3.5 h-3.5 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search"
                    placeholder="ابحث..."
                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 pr-9 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 shadow-sm placeholder:text-slate-400">
            </div>
        </div>
    </header>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right min-w-[600px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80">
                        <th class="px-4 lg:px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">العميل</th>
                        <th class="px-4 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">المرحلة</th>
                        <th class="px-4 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider hidden sm:table-cell">المصدر</th>
                        <th class="px-4 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider hidden md:table-cell">القيمة</th>
                        <th class="px-4 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider hidden lg:table-cell">آخر تحديث</th>
                        <th class="px-4 lg:px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clients as $client)
                        <tr class="group hover:bg-indigo-50/50 transition-colors">
                            <td class="px-4 lg:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('crm.client-show', $client->id) }}"
                                       class="w-9 h-9 lg:w-10 lg:h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-sm font-black shadow-sm flex-shrink-0 hover:scale-105 transition-transform">
                                        {{ mb_substr($client->name, 0, 1) }}
                                    </a>
                                    <div>
                                        <a href="{{ route('crm.client-show', $client->id) }}"
                                           class="text-sm font-black text-slate-900 hover:text-indigo-600 transition-colors">
                                            {{ $client->name }}
                                        </a>
                                        <div class="text-xs text-slate-400 font-semibold mt-0.5" dir="ltr">{{ $client->phone ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $stageBg = ['new'=>'bg-slate-100 text-slate-600','contacted'=>'bg-blue-100 text-blue-700','interested'=>'bg-yellow-100 text-yellow-700','booked'=>'bg-purple-100 text-purple-700','active'=>'bg-green-100 text-green-700','followup'=>'bg-orange-100 text-orange-700','completed'=>'bg-emerald-100 text-emerald-700'];
                                    $cls = $stageBg[$client->stage] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <span class="inline-flex items-center gap-1 {{ $cls }} px-2 py-1 rounded-lg text-[11px] font-bold">
                                    <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></div>
                                    {{ $client->stage }}
                                </span>
                            </td>
                            <td class="px-4 py-4 hidden sm:table-cell">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide">{{ $client->source }}</span>
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <div class="text-sm font-black text-slate-900">{{ number_format($client->deal_value) }} <span class="text-[10px] text-slate-400 font-bold">KD</span></div>
                            </td>
                            <td class="px-4 py-4 hidden lg:table-cell">
                                <span class="text-xs text-slate-400 font-semibold">{{ $client->updated_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <a href="{{ route('crm.client-show', $client->id) }}"
                                       class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="deleteClient({{ $client->id }})"
                                        wire:confirm="هل أنت متأكد من حذف هذا العميل؟"
                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-sm font-bold">لا يوجد عملاء</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 lg:px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- New Client Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>

        <div class="relative bg-white w-full sm:rounded-2xl sm:max-w-lg max-h-[95vh] overflow-y-auto rounded-t-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-900">إضافة عميل جديد</h2>
                <button wire:click="closeModal" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="saveClient" class="px-5 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">الاسم <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="newName" placeholder="اسم العميل"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 @error('newName') border-red-400 @enderror">
                    @error('newName') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">الهاتف</label>
                        <input type="text" wire:model="newPhone" placeholder="965XXXXXXXX" dir="ltr"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">البريد الإلكتروني</label>
                        <input type="email" wire:model="newEmail" placeholder="email@example.com" dir="ltr"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 @error('newEmail') border-red-400 @enderror">
                        @error('newEmail') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">المصدر</label>
                        <select wire:model="newSource" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 bg-white">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                            <option value="referral">إحالة</option>
                            <option value="direct">مباشر</option>
                            <option value="website">الموقع</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">المرحلة</label>
                        <select wire:model="newStage" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 bg-white">
                            <option value="new">جديد</option>
                            <option value="contacted">تم التواصل</option>
                            <option value="interested">مهتم</option>
                            <option value="booked">محجوز</option>
                            <option value="active">نشط</option>
                            <option value="followup">متابعة</option>
                            <option value="completed">مكتمل</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">الأولوية</label>
                        <select wire:model="newPriority" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 bg-white">
                            <option value="low">منخفضة</option>
                            <option value="medium">متوسطة</option>
                            <option value="high">عالية</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">القيمة (KD)</label>
                        <input type="number" wire:model="newDealValue" placeholder="0" min="0" dir="ltr"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">ملاحظات</label>
                    <textarea wire:model="newNotes" rows="2" placeholder="أي ملاحظات إضافية..."
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-1 pb-2">
                    <button type="button" wire:click="closeModal"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/25 transition-colors">
                        حفظ العميل
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
