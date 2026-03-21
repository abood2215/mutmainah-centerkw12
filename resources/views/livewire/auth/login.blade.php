<div class="w-full max-w-md">

    {{-- Logo & Title --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-white/10 backdrop-blur border border-white/20 mb-4 shadow-2xl">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <h1 class="text-3xl font-black text-white">Loving Homes</h1>
        <p class="text-indigo-300 text-sm font-semibold mt-1">نظام إدارة العملاء</p>
    </div>

    {{-- Card --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
        <h2 class="text-xl font-black text-white mb-6 text-center">تسجيل الدخول</h2>

        @if($error)
            <div class="mb-4 bg-red-500/20 border border-red-400/40 rounded-2xl px-4 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-300 text-sm font-semibold">{{ $error }}</span>
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label class="block text-indigo-200 text-sm font-bold mb-2">اسم المستخدم</label>
                <div class="relative">
                    <input type="text" wire:model="username"
                        placeholder="أدخل اسم المستخدم"
                        autocomplete="username"
                        class="w-full bg-white/10 border border-white/20 rounded-2xl px-4 py-3 pr-12 text-white placeholder:text-indigo-300/60 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400/50 focus:border-indigo-400/50 transition-all">
                    <div class="absolute top-3 right-4 text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-indigo-200 text-sm font-bold mb-2">كلمة المرور</label>
                <div class="relative">
                    <input type="password" wire:model="password"
                        placeholder="أدخل كلمة المرور"
                        autocomplete="current-password"
                        class="w-full bg-white/10 border border-white/20 rounded-2xl px-4 py-3 pr-12 text-white placeholder:text-indigo-300/60 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400/50 focus:border-indigo-400/50 transition-all">
                    <div class="absolute top-3 right-4 text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="remember" id="remember"
                    class="w-4 h-4 rounded border-white/30 bg-white/10 text-indigo-500 focus:ring-indigo-400/50">
                <label for="remember" class="text-indigo-200 text-sm font-semibold cursor-pointer">تذكّرني</label>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-black py-3.5 rounded-2xl shadow-lg shadow-indigo-900/50 transition-all active:scale-[0.98]"
                wire:loading.attr="disabled" wire:loading.class="opacity-75">
                <span wire:loading.remove>دخول</span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    جاري الدخول...
                </span>
            </button>
        </form>
    </div>

    <p class="text-center text-indigo-400/60 text-xs font-semibold mt-6">
        Loving Homes CRM &copy; {{ date('Y') }}
    </p>
</div>
