<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Loving Homes CRM' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Cairo', sans-serif; }
        body { background: #F0F2FF; }

        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover  { background: #EEF2FF; color: #4338CA; }
        .sidebar-link.active { background: #4F46E5; color: #fff; box-shadow: 0 8px 24px rgba(79,70,229,0.35); }
        .sidebar-link.active svg { stroke: #fff; }
        .sidebar-link:hover svg { stroke: #4338CA; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slideUp 0.35s ease-out forwards; }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-4px); }
            75%      { transform: translateX(4px); }
        }
        .animate-shake { animation: shake 0.25s ease-in-out 3; }
    </style>
</head>
<body>
    <div class="flex min-h-screen" dir="rtl" x-data="{ sidebarOpen: false }">

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen"
             x-on:click="sidebarOpen = false"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-30 md:hidden"
             style="display:none"></div>

        <!-- Sidebar -->
        <x-crm-sidebar />

        <!-- Main Content -->
        <main class="flex-1 md:mr-64 min-h-screen overflow-x-hidden">

            <!-- Mobile Top Bar -->
            <div class="md:hidden flex items-center justify-between bg-white border-b border-slate-200 px-4 py-3 sticky top-0 z-20 shadow-sm">
                <button x-on:click="sidebarOpen = true"
                        class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-slate-900">Loving Homes CRM</span>
                </div>
                <div class="w-10"></div>
            </div>

            {{ $slot }}
        </main>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
</body>
</html>
