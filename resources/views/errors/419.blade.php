{{-- resources/views/errors/419.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 — {{ __('Page Expired') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{ display:none !important; }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <a href="/" class="mb-6 inline-flex items-center justify-center">
            <x-application-logo class="w-16 h-16 text-gray-500" />
        </a>

        <div class="w-full max-w-md">
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-gray-200">
                <div class="p-6 sm:p-7">

                    <div class="mb-4 text-center">
                        <h1 class="text-xl font-semibold">419 — {{ __('Page Expired') }}</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('Your form session has expired or the CSRF token was missing/invalid.') }}
                        </p>
                    </div>

                    {{-- ✅ 绿色盾牌（与页面里一致的组件） --}}
                    <x-security-banner
                        type="success"
                        :title="__('Request blocked by CSRF protection.')"
                        :message="__('The app prevented a forged request for your security.')" />

                    <div class="mt-4 space-y-3">
                        <a href="{{ url()->previous() ?: route('login') }}"
                           class="inline-flex w-full justify-center items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 transition">
                            {{ __('Go back and try again') }}
                        </a>

                        <p class="text-xs text-gray-500 text-center">
                            {{ __('Tip: If the page has been open for a long time, refresh it to get a fresh CSRF token.') }}
                        </p>
                    </div>

                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                &copy; {{ now()->year }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
