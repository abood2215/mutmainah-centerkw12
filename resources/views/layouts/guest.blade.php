<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول — مطمئنة | فرع الكويت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Cairo', sans-serif; }
        .bg-pattern {
            background-color: #f8f7ff;
            background-image:
                radial-gradient(at 20% 20%, rgba(99,102,241,0.08) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(139,92,246,0.08) 0px, transparent 50%),
                radial-gradient(at 50% 0%, rgba(79,70,229,0.06) 0px, transparent 50%);
        }
        .brand-line {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">
    {{ $slot }}
    @livewireScripts
</body>
</html>
