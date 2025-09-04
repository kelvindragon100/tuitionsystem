<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>[x-cloak]{ display:none !important; }</style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-100 dark:bg-gray-950 dark:text-gray-100">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
            <!-- 顶部 Logo -->
            <a href="/" class="mb-6 inline-flex items-center justify-center">
                <x-application-logo class="w-16 h-16 text-gray-500 dark:text-gray-300" />
            </a>

            <!-- 卡片容器 -->
            <div class="w-full max-w-md">
                <div class="bg-white/90 dark:bg-gray-900/70 backdrop-blur rounded-2xl shadow-xl border border-gray-200/70 dark:border-gray-800 overflow-hidden">

                    {{-- 可选的“品牌渐变标题条”。在子页面里提供 <x-slot name="cardHeader"> 即会显示 --}}
                    @isset($cardHeader)
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white">
                            {{ $cardHeader }}
                        </div>
                    @endisset

                    <!-- 卡片主体 -->
                    <div class="p-6 sm:p-7">
                        {{ $slot }}
                    </div>
                </div>

                <!-- 页脚（可选） -->
                <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    &copy; {{ now()->year }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>
