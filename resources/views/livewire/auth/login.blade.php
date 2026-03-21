<div class="w-full max-w-md">

    {{-- Logo & Brand --}}
    <div class="text-center mb-8">

        {{-- Icon --}}
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl mb-5 shadow-xl"
             style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>

        {{-- Name --}}
        <h1 class="text-4xl font-black brand-line tracking-tight leading-tight">مطمئنة</h1>
        <div class="flex items-center justify-center gap-2 mt-2">
            <span class="h-px w-8 bg-indigo-300 rounded-full"></span>
            <span class="text-sm font-black text-indigo-500 tracking-widest">فرع الكويت</span>
            <span class="h-px w-8 bg-indigo-300 rounded-full"></span>
        </div>
        <p class="text-slate-400 text-xs font-semibold mt-2">نظام إدارة العملاء</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl p-8 shadow-2xl border border-slate-100/80" style="box-shadow: 0 25px 60px -10px rgba(79,70,229,0.15), 0 10px 25px -5px rgba(0,0,0,0.08);">

        <h2 class="text-lg font-black text-slate-800 mb-6 text-center">تسجيل الدخول</h2>

        @if($error)
            <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-4 py-3 flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-red-700 text-sm font-bold">{{ $error }}</span>
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-5">

            {{-- Username --}}
            <div>
                <label class="block text-slate-700 text-sm font-black mb-2">اسم المستخدم</label>
                <div class="relative">
                    <input type="text" wire:model="username"
                        placeholder="أدخل اسم المستخدم"
                        autocomplete="username"
                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 pr-12 text-slate-800 placeholder:text-slate-400 text-sm font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    <div class="absolute top-3.5 right-4 text-slate-400">
                        <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-slate-700 text-sm font-black mb-2">كلمة المرور</label>
                <div class="relative">
                    <input type="password" wire:model="password"
                        placeholder="أدخل كلمة المرور"
                        autocomplete="current-password"
                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 pr-12 text-slate-800 placeholder:text-slate-400 text-sm font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white transition-all">
                    <div class="absolute top-3.5 right-4 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Remember --}}
            <div class="flex items-center gap-2.5">
                <input type="checkbox" wire:model="remember" id="remember"
                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="remember" class="text-slate-600 text-sm font-semibold cursor-pointer select-none">تذكّرني</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full text-white font-black py-3.5 rounded-2xl transition-all active:scale-[0.98] shadow-lg"
                style="background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 8px 20px -4px rgba(79,70,229,0.5);"
                wire:loading.attr="disabled" wire:loading.class="opacity-75">
                <span wire:loading.remove class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14"/>
                    </svg>
                    دخول
                </span>
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

    {{-- Footer --}}
    <p class="text-center text-slate-400 text-xs font-semibold mt-6">
        مطمئنة &mdash; فرع الكويت &copy; {{ date('Y') }}
    </p>

</div>
