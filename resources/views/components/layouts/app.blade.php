@props(['title' => 'DIP ERP'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { dark: '#111111', black: '#0a0a0a', gold: '#ffc20c', hover: '#e0a800', light: '#f8f9fa' } },
                    fontFamily: { sans: ['Figtree', 'sans-serif'] }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #ffc20c; }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-brand-light overflow-hidden" x-data="{ sidebarOpen: false, profileOpen: false }">
    <div class="flex h-screen w-full">
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-transition.opacity></div>
        <x-layouts.sidebar />
        <div class="flex-1 flex flex-col min-w-0 bg-brand-light relative">
            <x-layouts.header :title="$title" />
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
