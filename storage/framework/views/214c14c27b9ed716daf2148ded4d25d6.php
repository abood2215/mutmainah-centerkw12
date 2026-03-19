<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-6 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900">لوحة <span class="text-indigo-600">المتابعة</span></h1>
            <p class="text-sm text-slate-500 font-semibold mt-1">تتبع العملاء عبر مراحل البيع — <?php echo e(collect($clientsByStage)->flatten()->count()); ?> عميل إجمالاً</p>
        </div>

        <div class="flex items-center gap-3 w-full xl:w-auto">
            <div class="relative flex-1 xl:w-72">
                <svg class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search"
                    placeholder="ابحث بالاسم أو الهاتف..."
                    class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 pr-11 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 shadow-sm placeholder:text-slate-400">
            </div>
            <button class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl text-sm font-black shadow-lg shadow-indigo-500/25 active:scale-95 transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                عميل جديد
            </button>
        </div>
    </header>

    <!-- Kanban Board -->
    <div class="flex gap-5 overflow-x-auto pb-6 no-scrollbar">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex-shrink-0 w-[300px] flex flex-col">

                <!-- Column Header -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: <?php echo e($stage['color']); ?>"></div>
                        <h2 class="text-sm font-black text-slate-700"><?php echo e($stage['name']); ?></h2>
                    </div>
                    <span class="text-xs font-black text-slate-500 bg-slate-200 px-2.5 py-1 rounded-full">
                        <?php echo e(count($clientsByStage[$id])); ?>

                    </span>
                </div>

                <!-- Cards Column -->
                <div class="flex-grow space-y-3 bg-slate-200/50 rounded-2xl p-3 min-h-[500px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clientsByStage[$id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">

                            <!-- Client Name -->
                            <div class="flex items-start justify-between mb-3">
                                <a href="<?php echo e(route('crm.client-show', $client->id)); ?>"
                                   class="text-sm font-black text-slate-900 hover:text-indigo-600 transition-colors leading-tight line-clamp-2">
                                    <?php echo e($client->name); ?>

                                </a>
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-black text-white shadow-sm flex-shrink-0 mr-2"
                                     style="background-color: <?php echo e($stage['color']); ?>">
                                    <?php echo e(mb_substr($client->name, 0, 1)); ?>

                                </div>
                            </div>

                            <!-- Info -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?php echo e($client->source); ?></span>
                                    <span class="text-sm font-black text-indigo-600"><?php echo e(number_format($client->deal_value)); ?> KD</span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-400" dir="ltr"><?php echo e($client->phone ?: '—'); ?></div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                                <span class="text-[10px] text-slate-400 font-semibold"><?php echo e($client->updated_at->diffForHumans()); ?></span>

                                <?php
                                    $stageKeys   = array_keys($stages);
                                    $currentIdx  = array_search($id, $stageKeys);
                                    $nextStage   = $currentIdx < count($stageKeys) - 1 ? $stageKeys[$currentIdx + 1] : null;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextStage): ?>
                                    <button wire:click="moveStage(<?php echo e($client->id); ?>, '<?php echo e($nextStage); ?>')"
                                        class="text-[10px] font-black text-indigo-500 hover:text-white hover:bg-indigo-500 px-2.5 py-1 rounded-lg transition-all">
                                        <?php echo e($stages[$nextStage]['name']); ?> ←
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                            <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <span class="text-xs font-bold">لا يوجد عملاء</span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\abd-allah\Desktop\New folder (2)\resources\views/livewire/crm/pipeline.blade.php ENDPATH**/ ?>