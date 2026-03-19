<div dir="rtl" class="flex h-screen bg-[#F0F2FF] overflow-hidden">

    <!-- Conversations List -->
    <div class="w-[320px] flex-shrink-0 bg-white border-l border-slate-200 flex flex-col shadow-sm">

        <!-- Header -->
        <div class="px-5 py-5 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">صندوق الرسائل</h2>
                <span class="bg-indigo-600 text-white text-xs font-black px-2.5 py-1 rounded-full shadow-sm">
                    <?php echo e(count($conversations)); ?>

                </span>
            </div>
            <div class="flex gap-2 mt-3">
                <button wire:click="loadConversations"
                    class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-600 hover:text-white transition-all">
                    تحديث
                </button>
                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-100 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    Chatwoot Live
                </span>
            </div>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-3 space-y-1" wire:poll.8000ms="loadConversations">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div wire:click="selectConversation(<?php echo e($conv->id); ?>)"
                    class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border-2
                        <?php echo e($activeConversationId == $conv->id
                            ? 'bg-indigo-50 border-indigo-200'
                            : 'border-transparent hover:bg-slate-50'); ?>">

                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-black shadow-sm">
                            <?php echo e(mb_substr($conv->client_name, 0, 1)); ?>

                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($conv->unread > 0): ?>
                            <span class="absolute -top-1 -left-1 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">
                                <?php echo e($conv->unread); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 truncate <?php echo e($conv->unread > 0 ? 'text-indigo-700' : ''); ?>">
                                <?php echo e($conv->client_name); ?>

                            </h3>
                            <span class="text-[10px] text-slate-400 font-semibold flex-shrink-0 mr-1">
                                <?php echo e($conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '—'); ?>

                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                            </svg>
                            <span class="text-[10px] text-slate-400 font-semibold truncate" dir="ltr">
                                <?php echo e($conv->client_phone ?: 'WhatsApp'); ?>

                            </span>
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 <?php echo e($conv->status === 'open' ? 'bg-emerald-500' : 'bg-slate-300'); ?>"></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="py-20 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="text-xs font-bold">لا توجد محادثات</p>
                    <p class="text-[10px] text-slate-300 mt-1">تأكد من إعدادات Chatwoot</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeConversationId && $activeConvData): ?>
            <!-- Chat Header -->
            <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-black shadow-sm">
                        <?php echo e(mb_substr($activeConvData->client_name, 0, 1)); ?>

                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900"><?php echo e($activeConvData->client_name); ?></h2>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full <?php echo e($activeConvData->status === 'open' ? 'bg-emerald-500' : 'bg-slate-300'); ?>"></div>
                            <span class="text-xs text-slate-500 font-semibold" dir="ltr"><?php echo e($activeConvData->client_phone); ?></span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-md">
                                #<?php echo e($activeConversationId); ?>

                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- رابط لصفحة العميل في CRM إذا وجد -->
                    <?php
                        $phone = preg_replace('/[^0-9]/', '', $activeConvData->client_phone ?? '');
                        $crmClient = $phone ? \App\Models\CrmClient::where('phone', 'LIKE', "%{$phone}%")->first() : null;
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($crmClient): ?>
                        <a href="<?php echo e(route('crm.client-show', $crmClient->id)); ?>"
                           class="text-xs font-black px-3 py-2 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-all">
                            ملف العميل ←
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button wire:click="toggleStatus(<?php echo e($activeConversationId); ?>)"
                        class="text-xs font-black px-4 py-2 rounded-xl transition-all border
                            <?php echo e($activeConvData->status === 'open'
                                ? 'bg-white border-slate-200 text-slate-600 hover:bg-red-50 hover:border-red-200 hover:text-red-600'
                                : 'bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600'); ?>">
                        <?php echo e($activeConvData->status === 'open' ? 'إغلاق المحادثة' : 'إعادة فتح'); ?>

                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar bg-[#F0F2FF]"
                 wire:poll.8000ms="loadMessages"
                 x-data x-init="$el.scrollTop = $el.scrollHeight"
                 x-on:livewire:update="$nextTick(() => $el.scrollTop = $el.scrollHeight)">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex <?php echo e($msg->direction === 'out' ? 'justify-start' : 'justify-end'); ?>">
                        <div class="max-w-[70%]">
                            <div class="px-4 py-3 rounded-2xl text-sm font-semibold leading-relaxed shadow-sm
                                <?php echo e($msg->direction === 'out'
                                    ? 'bg-indigo-600 text-white rounded-tr-sm'
                                    : 'bg-white text-slate-800 border border-slate-200 rounded-tl-sm'); ?>">
                                <?php echo e($msg->content); ?>

                            </div>
                            <div class="text-[10px] mt-1 font-bold text-slate-400 flex items-center gap-1
                                <?php echo e($msg->direction === 'out' ? 'justify-end' : 'justify-start'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->direction === 'in'): ?>
                                    <svg class="w-3 h-3 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                                    </svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php echo e($msg->sent_at ? $msg->sent_at->format('H:i') : ''); ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <p class="text-sm font-bold">لا توجد رسائل</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Input -->
            <div class="bg-white border-t border-slate-200 p-4">
                <form wire:submit.prevent="sendMessage" class="flex items-center gap-3">
                    <input type="text" wire:model.defer="newMessage"
                        placeholder="اكتب رسالة — ستُرسل عبر Chatwoot → WhatsApp..."
                        class="flex-1 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 placeholder:text-slate-400">
                    <button type="submit"
                        class="w-11 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/25 active:scale-95 transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400">
                <div class="w-20 h-20 rounded-3xl bg-white border border-slate-200 flex items-center justify-center mb-4 shadow-sm">
                    <svg class="w-10 h-10 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-slate-600 mb-1">اختر محادثة</h3>
                <p class="text-sm text-slate-400 font-semibold">المحادثات مباشرة من Chatwoot</p>
                <div class="mt-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-xs font-bold text-emerald-700 text-center">
                    متصل بـ: <?php echo e(config('chatwoot.url')); ?><br>
                    Account: <?php echo e(config('chatwoot.account_id')); ?> | Inbox: <?php echo e(config('chatwoot.inbox_id')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\abd-allah\Desktop\New folder (2)\resources\views/livewire/crm/inbox.blade.php ENDPATH**/ ?>