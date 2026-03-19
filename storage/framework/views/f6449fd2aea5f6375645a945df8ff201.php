<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-6 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900">سجل <span class="text-indigo-600">العملاء</span></h1>
            <p class="text-sm text-slate-500 font-semibold mt-1">Loving Homes Client Registry</p>
        </div>

        <div class="flex items-center gap-3 w-full xl:w-auto">
            <!-- Filters -->
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <select wire:model.live="filterStage" class="text-sm font-bold text-slate-700 bg-transparent outline-none border-none">
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

            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm">
                <select wire:model.live="filterSource" class="text-sm font-bold text-slate-700 bg-transparent outline-none border-none">
                    <option value="">كل المصادر</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                    <option value="referral">إحالة</option>
                    <option value="direct">مباشر</option>
                    <option value="website">الموقع</option>
                </select>
            </div>

            <!-- Search -->
            <div class="relative flex-1 xl:w-64">
                <svg class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search"
                    placeholder="ابحث..."
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 pr-11 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 shadow-sm placeholder:text-slate-400">
            </div>

            <button class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl text-sm font-black shadow-lg shadow-indigo-500/25 active:scale-95 transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                عميل جديد
            </button>
        </div>
    </header>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-right">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/80">
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">العميل</th>
                    <th class="px-5 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">المرحلة</th>
                    <th class="px-5 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">المصدر</th>
                    <th class="px-5 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">القيمة</th>
                    <th class="px-5 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">آخر تحديث</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-left">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="group hover:bg-indigo-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="<?php echo e(route('crm.client-show', $client->id)); ?>"
                                   class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-sm font-black shadow-sm flex-shrink-0 hover:scale-105 transition-transform">
                                    <?php echo e(mb_substr($client->name, 0, 1)); ?>

                                </a>
                                <div>
                                    <a href="<?php echo e(route('crm.client-show', $client->id)); ?>"
                                       class="text-sm font-black text-slate-900 hover:text-indigo-600 transition-colors">
                                        <?php echo e($client->name); ?>

                                    </a>
                                    <div class="text-xs text-slate-400 font-semibold mt-0.5" dir="ltr"><?php echo e($client->phone ?: '—'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php
                                $stageBg = ['new'=>'bg-slate-100 text-slate-600','contacted'=>'bg-blue-100 text-blue-700','interested'=>'bg-yellow-100 text-yellow-700','booked'=>'bg-purple-100 text-purple-700','active'=>'bg-green-100 text-green-700','followup'=>'bg-orange-100 text-orange-700','completed'=>'bg-emerald-100 text-emerald-700'];
                                $cls = $stageBg[$client->stage] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="inline-flex items-center gap-1.5 <?php echo e($cls); ?> px-3 py-1 rounded-lg text-xs font-bold">
                                <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></div>
                                <?php echo e($client->stage); ?>

                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide"><?php echo e($client->source); ?></span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-black text-slate-900"><?php echo e(number_format($client->deal_value)); ?> <span class="text-[10px] text-slate-400 font-bold">KD</span></div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs text-slate-400 font-semibold"><?php echo e($client->updated_at->diffForHumans()); ?></span>
                        </td>
                        <td class="px-6 py-4 text-left">
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                                <button class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="deleteClient(<?php echo e($client->id); ?>)"
                                    wire:confirm="هل أنت متأكد من حذف هذا العميل؟"
                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-24 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-sm font-bold">لا يوجد عملاء</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            <?php echo e($clients->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Users\abd-allah\Desktop\New folder (2)\resources\views/livewire/crm/clients.blade.php ENDPATH**/ ?>