<x-guest-layout>
    {{-- 动画 & x-cloak --}}
    <style>
        [x-cloak]{ display:none !important; }
        @keyframes shake{10%,90%{transform:translateX(-1px)}20%,80%{transform:translateX(2px)}30%,50%,70%{transform:translateX(-4px)}40%,60%{transform:translateX(4px)}}
        .shake{ animation: shake .4s ease-in-out 1; }
    </style>

    {{-- 标题区 --}}
    <div class="mb-5 text-center">
        <h1 class="text-xl font-semibold">{{ __('Forgot password') }}</h1>
        <p class="text-sm text-gray-600 mt-1">
            {{ __('Enter your email and we will send you a link to reset your password.') }}
        </p>
    </div>

    {{-- ✅ 用组件显示“已发送邮件”等状态 --}}
    @if (session('status'))
        <x-security-banner
            type="info"
            :title="__('Email sent')"
            :message="session('status')" />
    @endif

    {{-- CSRF 防护成功提示 --}}
    @if (session('csrf_defended'))
        <x-security-banner type="success"
            title="Request blocked by CSRF protection."
            message="CSRF token was missing or invalid. The app prevented a forged request." />
    @endif

    <div x-data="sqliGuard()" x-cloak>
        {{-- SQLi 告警卡（命中时显示） --}}
        <template x-if="sqliTriggered">
            <div class="mb-4 rounded-xl border border-red-300/70 bg-red-50 text-red-800 p-3 flex items-start gap-3">
                <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M11 2l9 18H2L11 2zm0 6a1 1 0 00-1 1v4a1 1 0 102 0V9a1 1 0 00-1-1zm0 8a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/>
                </svg>
                <div>
                    <div class="font-medium">Potential SQL Injection detected</div>
                    <div class="text-sm opacity-90">Suspicious input blocked. Please remove SQL operators or keywords.</div>
                    <details class="mt-2 text-sm">
                        <summary class="cursor-pointer underline">Details</summary>
                        <ul class="list-disc pl-5 mt-1">
                            <template x-for="hit in hits" :key="hit">
                                <li x-text="hit"></li>
                            </template>
                        </ul>
                    </details>
                </div>
            </div>
        </template>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate
              @submit.prevent="handleSubmit($event)">
            @csrf

            {{-- Email --}}
            <div x-bind:class="{'shake': sqliTriggered && lastField==='email'}">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    x-model.trim="email"
                    @input="scan('email')"
                    x-bind:class="sqliTriggered && lastField==='email' ? 'border-red-400 ring-2 ring-red-200' : ''"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- 动作按钮 --}}
            <div class="pt-2 space-y-3">
                <x-primary-button class="w-full justify-center"
                    x-bind:class="sqliTriggered ? 'opacity-60 cursor-not-allowed' : ''"
                    x-bind:disabled="sqliTriggered">
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>

                {{-- 返回登录入口 --}}
                @if (Route::has('login'))
                    <p class="text-center text-sm text-gray-600">
                        {{ __('Remember your password?') }}
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">
                            {{ __('Back to Sign in') }}
                        </a>
                    </p>
                @endif
            </div>
        </form>

        {{-- 贴心提示（可选） --}}
        <div class="mt-6 text-xs text-gray-500">
            <p>• {{ __('Didn’t get the email? Check your spam folder or try again in a minute.') }}</p>
            <p>• {{ __('If you signed up with the wrong email, register a new account with the correct address.') }}</p>
        </div>
    </div>

    {{-- 共享 SQLi 守卫脚本 --}}
    @include('partials.sqli-guard')
</x-guest-layout>
