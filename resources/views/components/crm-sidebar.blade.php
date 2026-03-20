@php
    $currentPath = request()->path();
    $links = [
        ['id' => 'pipeline',   'label' => 'لوحة المتابعة',  'route' => '/crm/pipeline',   'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['id' => 'clients',    'label' => 'سجل العملاء',    'route' => '/crm/clients',    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['id' => 'inbox',      'label' => 'صندوق الرسائل',  'route' => '/crm/inbox',      'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ['id' => 'tasks',      'label' => 'إدارة المهام',   'route' => '/crm/tasks',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['id' => 'campaigns',  'label' => 'الحملات الذكية', 'route' => '/crm/campaigns',  'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
        ['id' => 'settings',   'label' => 'الإعدادات',      'route' => '/crm/settings',   'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp

<aside class="fixed top-0 right-0 h-full w-64 bg-white border-l border-slate-200 shadow-xl z-40 flex flex-col
              transform transition-transform duration-300 ease-in-out
              translate-x-full md:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">

    <!-- Close button (mobile only) -->
    <button x-on:click="sidebarOpen = false"
            class="md:hidden absolute top-4 left-4 w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Logo / Brand -->
    <div class="px-6 py-7 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-black text-slate-900 leading-none">Loving Homes</div>
                <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">CRM System</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto no-scrollbar">
        <div class="text-[9px] font-black text-slate-400 uppercase tracking-[4px] px-3 mb-3">القائمة الرئيسية</div>

        @foreach($links as $link)
            @php $isActive = str_contains($currentPath, $link['id']); @endphp
            <a href="{{ $link['route'] }}"
               x-on:click="sidebarOpen = false"
               class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-xl {{ $isActive ? 'active' : 'text-slate-600' }}">
                <svg class="w-5 h-5 {{ $isActive ? 'stroke-white' : 'stroke-slate-500' }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
                <span class="text-sm font-bold">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- Footer / User -->
    <div class="px-4 py-5 border-t border-slate-100">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-xs font-black shadow">
                م
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-black text-slate-800 leading-none truncate"> النظام</div>
                <div class="text-[9px] text-slate-400 font-bold mt-0.5 uppercase tracking-wide">Admin</div>
            </div>
            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm"></div>
        </div>
    </div>
</aside>
