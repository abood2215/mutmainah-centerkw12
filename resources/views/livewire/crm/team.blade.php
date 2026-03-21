<div dir="rtl" class="p-6" wire:poll.10000ms="loadUsers">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold rounded-xl">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black text-slate-900">فريق العمل</h1>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">إدارة أعضاء الفريق وحالة التواجد</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-indigo-600 text-white text-xs font-black px-3 py-1.5 rounded-full shadow-sm">
                {{ count($users) }} عضو
            </span>
            @if(auth()->user()->isAdmin())
                <button wire:click="openAddUser"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black px-4 py-2 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة عضو
                </button>
            @endif
        </div>
    </div>

    {{-- Grid --}}
    @if(count($users) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($users as $user)
                @php
                    $gradients = [
                        'from-indigo-500 to-violet-500',
                        'from-emerald-500 to-teal-500',
                        'from-amber-500 to-orange-500',
                        'from-pink-500 to-rose-500',
                        'from-sky-500 to-blue-500',
                        'from-purple-500 to-fuchsia-500',
                    ];
                    $gradientClass = $gradients[$user['id'] % count($gradients)];

                    $statusDotColor = match($user['status']) {
                        'online' => 'bg-emerald-500',
                        'away'   => 'bg-amber-400',
                        default  => 'bg-slate-300',
                    };
                    $statusBgColor = match($user['status']) {
                        'online' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'away'   => 'bg-amber-50 text-amber-700 border-amber-100',
                        default  => 'bg-slate-50 text-slate-500 border-slate-100',
                    };
                    $roleBadge = match($user['role'] ?? 'agent') {
                        'admin' => 'bg-indigo-100 text-indigo-700',
                        default => 'bg-slate-100 text-slate-600',
                    };
                    $roleLabel = match($user['role'] ?? 'agent') {
                        'admin' => 'مدير النظام',
                        default => 'موظف استقبال',
                    };
                    $genderIcon = ($user['gender'] ?? 'male') === 'female' ? '♀' : '♂';
                    $genderColor = ($user['gender'] ?? 'male') === 'female' ? 'text-pink-500' : 'text-sky-500';
                @endphp

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all p-5 flex flex-col items-center text-center gap-3">

                    {{-- Avatar --}}
                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $gradientClass }} flex items-center justify-center text-white text-2xl font-black shadow-md">
                            {{ mb_substr($user['name'], 0, 1) }}
                        </div>
                        <span class="absolute -bottom-1 -left-1 w-4 h-4 rounded-full border-2 border-white {{ $statusDotColor }} shadow-sm"></span>
                    </div>

                    {{-- Info --}}
                    <div class="w-full">
                        <h3 class="text-sm font-black text-slate-900 leading-tight truncate">
                            {{ $user['name'] }}
                            <span class="{{ $genderColor }} text-base">{{ $genderIcon }}</span>
                        </h3>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5 truncate" dir="ltr">&#64;{{ $user['username'] }}</p>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-1.5 justify-center">
                        <span class="text-[10px] font-black px-2 py-1 rounded-lg {{ $roleBadge }}">
                            {{ $roleLabel }}
                        </span>
                        <span class="text-[10px] font-black px-2 py-1 rounded-lg border {{ $statusBgColor }}">
                            {{ $user['status_label'] }}
                        </span>
                    </div>

                    {{-- Last seen --}}
                    @if($user['last_seen_at'])
                        <p class="text-[10px] text-slate-400 font-semibold">
                            آخر ظهور: {{ \Carbon\Carbon::parse($user['last_seen_at'])->diffForHumans() }}
                        </p>
                    @else
                        <p class="text-[10px] text-slate-300 font-semibold">لم يسجّل دخول بعد</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-24 text-slate-400">
            <svg class="w-12 h-12 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-sm font-bold">لا يوجد أعضاء في الفريق</p>
        </div>
    @endif

    {{-- Add User Modal (Admin only) --}}
    @if(auth()->user()->isAdmin() && $showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-init="document.body.classList.add('overflow-hidden')"
             x-destroy="document.body.classList.remove('overflow-hidden')">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                 wire:click="$set('showModal', false)"></div>

            {{-- Modal Card --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-black text-slate-900">إضافة عضو جديد</h2>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5">أدخل بيانات العضو وصلاحياته</p>
                    </div>
                    <button wire:click="$set('showModal', false)"
                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveUser" class="space-y-4">

                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">الاسم</label>
                        <input wire:model="form_name" type="text" placeholder="أدخل الاسم الكامل"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @error('form_name')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">الجنس</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                                          {{ $form_gender === 'male' ? 'border-sky-500 bg-sky-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model="form_gender" value="male" class="sr-only">
                                <span class="text-xl">♂</span>
                                <span class="text-sm font-black {{ $form_gender === 'male' ? 'text-sky-700' : 'text-slate-600' }}">ذكر</span>
                            </label>
                            <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                                          {{ $form_gender === 'female' ? 'border-pink-500 bg-pink-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model="form_gender" value="female" class="sr-only">
                                <span class="text-xl">♀</span>
                                <span class="text-sm font-black {{ $form_gender === 'female' ? 'text-pink-700' : 'text-slate-600' }}">أنثى</span>
                            </label>
                        </div>
                        @error('form_gender')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">الصلاحية</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                                          {{ $form_role === 'admin' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model="form_role" value="admin" class="sr-only">
                                <svg class="w-4 h-4 {{ $form_role === 'admin' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-sm font-black {{ $form_role === 'admin' ? 'text-indigo-700' : 'text-slate-600' }}">مدير</span>
                            </label>
                            <label class="relative flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                                          {{ $form_role === 'agent' ? 'border-slate-500 bg-slate-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model="form_role" value="agent" class="sr-only">
                                <svg class="w-4 h-4 {{ $form_role === 'agent' ? 'text-slate-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-sm font-black {{ $form_role === 'agent' ? 'text-slate-700' : 'text-slate-600' }}">موظف استقبال</span>
                            </label>
                        </div>
                        @error('form_role')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">اسم المستخدم</label>
                        <div class="relative">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">&#64;</span>
                            <input wire:model="form_username" type="text" placeholder="username"
                                   class="w-full border border-slate-200 rounded-xl pr-8 pl-4 py-2.5 text-sm font-semibold text-slate-800 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                   dir="ltr">
                        </div>
                        @error('form_username')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">كلمة المرور</label>
                        <input wire:model="form_password" type="password" placeholder="••••••••"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                               dir="ltr">
                        @error('form_password')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black py-2.5 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="saveUser">حفظ العضو</span>
                            <span wire:loading wire:target="saveUser">جاري الحفظ...</span>
                        </button>
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-black py-2.5 rounded-xl transition-colors">
                            إلغاء
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

</div>
