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
    <div class="flex min-h-screen" dir="rtl">
        <!-- Fixed Sidebar (Right in RTL) -->
        <x-crm-sidebar />

        <!-- Main Content -->
        <main class="flex-1 mr-64 min-h-screen overflow-x-hidden">
            {{ $slot }}
        </main>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
</body>
</html>
