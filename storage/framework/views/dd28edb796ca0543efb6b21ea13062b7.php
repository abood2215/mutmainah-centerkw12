<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-6 lg:p-8 animate-slide-up">

    <!-- Header -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4">
        <div class="flex items-center gap-4">
            <a href="/crm/clients" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 flex items-center justify-center shadow-sm transition-colors group">
                <svg class="w-5 h-5 text-slate-500 group-hover:text-indigo-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-2xl font-black shadow-lg">
                <?php echo e(mb_substr($client->name, 0, 1)); ?>

            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900"><?php echo e($client->name); ?></h1>
                <div class="flex items-center gap-2 mt-1">
                    <?php
                        $stageBg = ['new'=>'bg-slate-100 text-slate-600','contacted'=>'bg-blue-100 text-blue-700','interested'=>'bg-yellow-100 text-yellow-700','booked'=>'bg-purple-100 text-purple-700','active'=>'bg-green-100 text-green-700','followup'=>'bg-orange-100 text-orange-700','completed'=>'bg-emerald-100 text-emerald-700'];
                        $cls = $stageBg[$client->stage] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <span class="inline-flex items-center gap-1.5 <?php echo e($cls); ?> px-2.5 py-1 rounded-lg text-xs font-bold"><?php echo e($client->stage); ?></span>
                    <span class="text-xs text-slate-400 font-semibold"><?php echo e($client->source); ?></span>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-xl text-sm font-black shadow-lg shadow-emerald-500/25 active:scale-95 transition-all">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                </svg>
                ارسل واتساب
            </button>
            <button class="flex items-center gap-2 bg-white border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-600 px-5 py-3 rounded-xl text-sm font-black shadow-sm active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                تحرير الملف
            </button>
        </div>
    </header>

    <!-- Tabs -->
    <nav class="flex gap-1 mb-6 bg-white border border-slate-200 rounded-xl p-1 w-fit shadow-sm">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['overview' => 'نظرة عامة', 'notes' => 'الملاحظات', 'tasks' => 'المهام', 'activities' => 'النشاطات', 'conversations' => 'المحادثات']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button wire:click="setTab('<?php echo e($key); ?>')"
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all
                    <?php echo e($activeTab == $key ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'); ?>">
                <?php echo e($label); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>

    <!-- Content -->
    <div class="animate-slide-up">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab == 'overview'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <h2 class="text-base font-black text-slate-900 mb-5 flex items-center gap-2">
                            <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                            بيانات العميل
                        </h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">الهاتف</div>
                                <div class="text-base font-bold text-slate-800" dir="ltr"><?php echo e($client->phone ?: '—'); ?></div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">الإيميل</div>
                                <div class="text-base font-bold text-slate-800"><?php echo e($client->email ?: '—'); ?></div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">المصدر</div>
                                <div class="text-base font-bold text-indigo-600 uppercase"><?php echo e($client->source); ?></div>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">معين لـ</div>
                                <div class="text-base font-bold text-slate-800">الموظف المسؤول</div>
                            </div>
                        </div>
                    </div>

                    <?php $clientNotesText = $client->getAttributes()['notes'] ?? null; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clientNotesText): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                            <h3 class="text-xs font-black text-amber-700 uppercase tracking-widest mb-2">ملاحظات عامة</h3>
                            <p class="text-sm text-amber-900 font-semibold leading-relaxed"><?php echo e($clientNotesText); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Deal Value -->
                <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl p-6 shadow-lg text-white">
                    <div class="text-xs font-black uppercase tracking-widest opacity-70 mb-3">القيمة التقديرية</div>
                    <div class="text-4xl font-black tracking-tight"><?php echo e(number_format($client->deal_value)); ?></div>
                    <div class="text-lg font-bold opacity-70 mt-1">دينار كويتي</div>
                    <div class="mt-6 pt-6 border-t border-white/20">
                        <div class="text-xs font-black opacity-60 uppercase tracking-widest mb-1">تاريخ الإضافة</div>
                        <div class="text-sm font-bold"><?php echo e($client->created_at->format('Y/m/d')); ?></div>
                    </div>
                </div>
            </div>

        <?php elseif($activeTab == 'notes'): ?>
            <div class="max-w-3xl space-y-5">
                <!-- Add Note -->
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

                <!-- Notes List -->
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $client->notes()->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-indigo-200 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-xs font-black text-indigo-600">م</div>
                                    <span class="text-sm font-bold text-slate-700">الموظف المسؤول</span>
                                </div>
                                <span class="text-xs text-slate-400 font-semibold"><?php echo e($note->created_at->diffForHumans()); ?></span>
                            </div>
                            <p class="text-sm text-slate-700 font-semibold leading-relaxed"><?php echo e($note->content); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-16 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-bold">لا توجد ملاحظات بعد</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        <?php elseif($activeTab == 'tasks'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $client->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $taskStatus = ['pending'=>['bg-slate-100','text-slate-600'],'inprogress'=>['bg-yellow-100','text-yellow-700'],'done'=>['bg-emerald-100','text-emerald-700']];
                        [$tbg,$tcl] = $taskStatus[$task->status] ?? ['bg-slate-100','text-slate-600'];
                    ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <span class="<?php echo e($tbg); ?> <?php echo e($tcl); ?> text-xs font-bold px-2.5 py-1 rounded-lg"><?php echo e($task->status); ?></span>
                            <span class="text-xs text-slate-400 font-semibold"><?php echo e($task->due_date ? $task->due_date->format('M d') : '—'); ?></span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 mb-2"><?php echo e($task->title); ?></h3>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-slate-400 font-bold"><?php echo e($task->priority); ?> priority</span>
                            <div class="h-1.5 w-20 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: <?php echo e($task->status == 'done' ? '100' : ($task->status == 'inprogress' ? '50' : '10')); ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-3 text-center py-16 text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm font-bold">لا توجد مهام</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        <?php elseif($activeTab == 'activities'): ?>
            <div class="max-w-2xl">
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $client->activityLogs()->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $actionColors = ['client_created'=>'bg-emerald-100 text-emerald-700','stage_changed'=>'bg-blue-100 text-blue-700','task_completed'=>'bg-violet-100 text-violet-700'];
                            $ac = $actionColors[$log->action] ?? 'bg-slate-100 text-slate-600';
                        ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-start gap-4">
                            <div class="w-9 h-9 rounded-xl <?php echo e($ac); ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-black <?php echo e($ac); ?> px-2 py-0.5 rounded-md"><?php echo e($log->action); ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold"><?php echo e($log->created_at->format('Y/m/d H:i')); ?></span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->metadata): ?>
                                    <p class="text-xs text-slate-600 font-semibold">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($log->metadata['from'])): ?>
                                            من: <?php echo e($log->metadata['from']); ?> → إلى: <?php echo e($log->metadata['to']); ?>

                                        <?php else: ?>
                                            <?php echo e(json_encode($log->metadata)); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-16 text-slate-400">
                            <p class="text-sm font-bold">لا توجد نشاطات</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        <?php elseif($activeTab == 'conversations'): ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\abd-allah\Desktop\New folder (2)\resources\views/livewire/crm/client-show.blade.php ENDPATH**/ ?>