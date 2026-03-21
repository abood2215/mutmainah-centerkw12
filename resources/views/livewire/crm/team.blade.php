<div dir="rtl" class="p-6" wire:poll.10000ms="loadUsers">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black text-slate-900">فريق العمل</h1>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">إدارة أعضاء الفريق وحالة التواجد</p>
        </div>
        <span class="bg-indigo-600 text-white text-xs font-black px-3 py-1.5 rounded-full shadow-sm">
            {{ count($users) }} عضو
        </span>
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
                        'admin'   => 'bg-indigo-100 text-indigo-700',
                        'manager' => 'bg-violet-100 text-violet-700',
                        default   => 'bg-slate-100 text-slate-600',
                    };
                    $roleLabel = match($user['role'] ?? 'agent') {
                        'admin'   => 'مدير النظام',
                        'manager' => 'مشرف',
                        default   => 'وكيل',
                    };
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
                        <h3 class="text-sm font-black text-slate-900 leading-tight truncate">{{ $user['name'] }}</h3>
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
</div>
