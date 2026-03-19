<div dir="rtl" class="min-h-screen bg-[#F0F2FF] p-6 lg:p-8 animate-slide-up">

    <header class="mb-8">
        <h1 class="text-3xl font-black text-slate-900">إعدادات <span class="text-indigo-600">النظام</span></h1>
        <p class="text-sm text-slate-500 font-semibold mt-1">Chatwoot Bridge + WhatsApp Cloud API</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- ─── Chatwoot Status ─── -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-gradient-to-l from-blue-50 to-white flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                        Chatwoot Bridge
                    </h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">الإعدادات من ملف .env</p>
                </div>
                @if($connectionStatus === 'success')
                    <span class="bg-emerald-100 text-emerald-700 text-xs font-black px-3 py-1.5 rounded-xl border border-emerald-200">✓ متصل</span>
                @elseif($connectionStatus === 'error')
                    <span class="bg-red-100 text-red-700 text-xs font-black px-3 py-1.5 rounded-xl border border-red-200 animate-shake">✗ فشل الاتصال</span>
                @endif
            </div>

            <div class="p-5 space-y-3">
                <!-- عرض الإعدادات الحالية (read-only) -->
                <div class="bg-slate-50 rounded-xl divide-y divide-slate-100 overflow-hidden border border-slate-200">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">URL</span>
                        <span class="text-xs font-bold text-slate-800 font-mono" dir="ltr">{{ config('chatwoot.url') ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Account ID</span>
                        <span class="text-xs font-bold text-slate-800 font-mono">{{ config('chatwoot.account_id') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Inbox ID</span>
                        <span class="text-xs font-bold text-slate-800 font-mono">{{ config('chatwoot.inbox_id') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">API Token</span>
                        <span class="text-xs font-bold text-slate-500 font-mono">
                            {{ config('chatwoot.api_token') ? '••••••••' . substr(config('chatwoot.api_token'), -6) : '—' }}
                        </span>
                    </div>
                </div>

                <button wire:click="testChatwoot" wire:loading.attr="disabled"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white py-3 rounded-xl text-sm font-black shadow-lg shadow-blue-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <svg wire:loading wire:target="testChatwoot" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span wire:loading.remove wire:target="testChatwoot">اختبر الاتصال</span>
                    <span wire:loading wire:target="testChatwoot">جارٍ الاختبار...</span>
                </button>

                <a href="{{ config('chatwoot.url') }}" target="_blank"
                   class="w-full flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    افتح Chatwoot Dashboard
                </a>
            </div>
        </div>

        <!-- ─── Right Column ─── -->
        <div class="space-y-5">

            <!-- Webhook URL -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                        WhatsApp Webhook
                    </h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">ضعه في Meta Developer Portal</p>
                </div>

                <div class="p-5 space-y-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3">
                        <code class="text-xs text-indigo-700 font-bold break-all flex-1" dir="ltr">{{ $webhookUrl }}</code>
                        <button onclick="navigator.clipboard.writeText('{{ $webhookUrl }}').then(() => this.textContent = 'تم ✓').catch(() => {}); setTimeout(() => this.textContent = 'نسخ', 2000)"
                            class="text-xs font-black text-slate-500 hover:text-indigo-600 bg-white border border-slate-200 px-3 py-1.5 rounded-lg transition-colors flex-shrink-0 hover:border-indigo-200">
                            نسخ
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Verify Token</div>
                            <div class="text-xs font-black text-slate-700 font-mono break-all">{{ config('whatsapp.verify_token') }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Webhook Fields</div>
                            <div class="text-xs font-black text-slate-700">messages, statuses</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp API Status -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.012 2.25c-5.378 0-9.755 4.377-9.755 9.755 0 1.719.447 3.332 1.233 4.737l-1.31 4.793 4.907-1.288a9.704 9.704 0 004.66.19c1.925 0 3.73-.553 5.257-1.51A9.755 9.755 0 0021.767 12c0-5.378-4.378-9.75-9.755-9.75z"/>
                        </svg>
                        WhatsApp Business API
                    </h2>
                </div>
                <div class="p-5">
                    <div class="bg-slate-50 rounded-xl divide-y divide-slate-100 overflow-hidden border border-slate-200">
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-xs font-black text-slate-500">Phone Number ID</span>
                            <span class="text-xs font-bold text-slate-700 font-mono" dir="ltr">{{ config('whatsapp.phone_number_id') ?: '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-xs font-black text-slate-500">Business Account ID</span>
                            <span class="text-xs font-bold text-slate-700 font-mono" dir="ltr">{{ config('whatsapp.business_account_id') ?: '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-xs font-black text-slate-500">Access Token</span>
                            @if(config('whatsapp.token'))
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">✓ مُعيّن</span>
                            @else
                                <span class="text-xs font-black text-red-600 bg-red-50 px-2.5 py-1 rounded-lg border border-red-200">✗ غير مُعيّن</span>
                            @endif
                        </div>
                    </div>

                    @if(config('whatsapp.token'))
                        <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-700 font-semibold">
                            ⚠️ تأكد إن الـ Token صالح من Meta Developer Portal — الـ Token ينتهي دورياً ويحتاج تجديد.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scheduler -->
            <div class="bg-gradient-to-l from-indigo-600 to-violet-600 rounded-2xl p-5 shadow-lg text-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black">Campaign Scheduler</h4>
                        <p class="text-xs text-white/70 font-semibold mt-0.5">يعمل كل دقيقة — حملات WhatsApp تلقائية</p>
                        <p class="text-[10px] text-white/50 font-mono mt-1">php artisan schedule:run</p>
                    </div>
                    <div class="mr-auto">
                        <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse shadow-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
