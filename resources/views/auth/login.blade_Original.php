<x-guest-layout>
    <style>
        [x-cloak]{ display:none !important; }
        @keyframes shake{ 10%,90%{transform:translateX(-1px)} 20%,80%{transform:translateX(2px)} 30%,50%,70%{transform:translateX(-4px)} 40%,60%{transform:translateX(4px)} }
        .shake{ animation: shake 0.4s ease-in-out 1; }
    </style>

    <div class="mb-5 text-center">
        <h1 class="text-xl font-semibold">{{ __('Welcome back') }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ __('Sign in to your account') }}</p>
    </div>

    {{-- 全局状态（如重置密码邮件已发送等） --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- CSRF 防护成功提示（bootstrap/app.php 已注入 csrf_defended） --}}
    @if (session('csrf_defended'))
        <x-security-banner type="success"
            title="Request blocked by CSRF protection."
            message="Your session token was invalid or missing. The app prevented a forged request." />
    @endif

    {{-- Brute Force 友好提示（倒计时/剩余次数/进度条/ARIA） --}}
    @php
        $cooldown = session('login_cooldown');              // 冷却秒
        $attempts = session('login_attempts');              // ['remaining'=>x,'max'=>y,'window'=>60]
    @endphp
    <div
        x-data="rateGuard({
            cooldownFromServer: {{ $cooldown ? (int)$cooldown : 0 }},
            attemptsFromServer: {!! $attempts ? json_encode($attempts) : 'null' !!}
        })"
        x-init="init()"
        class="mb-4"
        x-cloak
    >
        {{-- 冷却横幅 --}}
        <template x-if="cooldown > 0">
            <div class="rounded-xl border border-amber-300/70 bg-amber-50 text-amber-900 p-3" role="alert" aria-live="polite">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1010 10A10.011 10.011 0 0012 2zm.75 5v6.25l4 2.4-.75 1.23L11 14V7z"/>
                    </svg>
                    <div class="flex-1">
                        <div class="font-medium">Hold on — login temporarily locked</div>
                        <p class="text-sm opacity-90">
                            Too many attempts. Please wait
                            <span class="font-semibold" x-text="cooldown"></span>s, then try again.
                        </p>
                        <div class="mt-2 h-2 w-full rounded bg-amber-100">
                            <div class="h-2 rounded bg-amber-400"
                                 x-bind:style="`width: ${Math.max(0, Math.min(100, Math.round(cooldown / window * 100)))}%`"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- 剩余次数提示 --}}
        <template x-if="cooldown === 0 && attempts.max > 0">
            <div class="rounded-xl border border-blue-200 bg-blue-50 text-blue-900 p-3" role="status" aria-live="polite">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 100 20 10.011 10.011 0 000-20zm-1 15h2v2h-2zm2-10h-2v8h2z"/>
                    </svg>
                    <div class="flex-1">
                        <div class="text-sm">
                            Attempts left:
                            <span class="font-semibold" x-text="attempts.remaining"></span>
                            / <span x-text="attempts.max"></span>
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded bg-blue-100">
                            <div class="h-1.5 rounded bg-blue-400"
                                 x-bind:style="`width: ${Math.round((attempts.remaining/attempts.max)*100)}%`"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- 暴露给下方表单使用（禁用按钮/显示倒计时） --}}
        <input type="hidden" x-ref="cooldown" :value="cooldown">
        <input type="hidden" x-ref="attemptsRemaining" :value="attempts.remaining">
        <input type="hidden" x-ref="attemptsMax" :value="attempts.max">
    </div>

    <div x-data="sqliGuard()" x-cloak>
        {{-- SQLi 可视化告警卡 --}}
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

        <form method="POST" action="{{ route('login') }}" novalidate class="space-y-4" @submit.prevent="handleSubmit($event)">
            @csrf

            {{-- Email --}}
            <div :class="{'shake': sqliTriggered && lastField==='email'}">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    x-model.trim="email"
                    @input="scan('email')"
                    x-bind:class="sqliTriggered && lastField==='email' ? 'border-red-400 ring-2 ring-red-200' : ''"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password（带可见性切换 + SQLi 扫描） --}}
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" />
                    @if (Route::has('password.request'))
                        <a class="text-xs text-indigo-600 hover:underline rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                           href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <div x-data="{ show: false }" class="relative" x-cloak :class="{'shake': sqliTriggered && lastField==='password'}">
                    <x-text-input
                        id="password"
                        name="password"
                        class="block mt-1 w-full pr-10"
                        x-bind:type="show ? 'text' : 'password'"
                        x-model="password"
                        @input="scan('password')"
                        x-bind:class="sqliTriggered && lastField==='password' ? 'border-red-400 ring-2 ring-red-200' : ''"
                        required
                        autocomplete="current-password"
                    />

                    <button type="button"
                            @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Toggle password visibility">
                        {{-- eye-off --}}
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18M10.5 6.3C11 6.1 11.5 6 12 6c6 0 9.75 6 9.75 6a11.1 11.1 0 0 1-3.2 3.3M6.2 6.2A11.1 11.1 0 0 0 2.25 12S6 18 12 18c1.1 0 2.1-.2 3-.5" />
                        </svg>
                        {{-- eye --}}
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.25 12S6 6 12 6s9.75 6 9.75 6-3.75 6-9.75 6S2.25 12 2.25 12z"/>
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Remember me --}}
            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            {{-- 登录按钮（冷却时禁用 + 倒计时） --}}
            <div class="pt-2">
                <x-primary-button
                    class="w-full justify-center"
                    x-bind:class="(Number(document.querySelector('input[x-ref=cooldown]')?.value || 0) > 0 || sqliTriggered) ? 'opacity-60 cursor-not-allowed' : ''"
                    x-bind:disabled="Number(document.querySelector('input[x-ref=cooldown]')?.value || 0) > 0 || sqliTriggered"
                >
                    <span x-show="Number(document.querySelector('input[x-ref=cooldown]')?.value || 0) === 0">
                        {{ __('Log in') }}
                    </span>
                    <span x-show="Number(document.querySelector('input[x-ref=cooldown]')?.value || 0) > 0">
                        {{ __('Please wait') }}
                        (<span x-text="document.querySelector('input[x-ref=cooldown]')?.value"></span>s)
                    </span>
                </x-primary-button>
            </div>

            {{-- 注册入口 --}}
            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-600">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">
                        {{ __('Register here') }}
                    </a>
                </p>
            @endif
        </form>
    </div>

    {{-- 共享 SQLi 守卫脚本 --}}
    @include('partials.sqli-guard')

    {{-- Brute-force UI 脚本（倒计时/剩余次数） --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rateGuard', ({cooldownFromServer = 0, attemptsFromServer = null} = {}) => ({
            cooldown: Number(cooldownFromServer || 0),
            window: (attemptsFromServer && attemptsFromServer.window) ? Number(attemptsFromServer.window) : 60,
            attempts: {
                remaining: attemptsFromServer && attemptsFromServer.remaining != null ? Number(attemptsFromServer.remaining) : 0,
                max:       attemptsFromServer && attemptsFromServer.max != null       ? Number(attemptsFromServer.max)       : 0,
                window:    (attemptsFromServer && attemptsFromServer.window) ? Number(attemptsFromServer.window) : 60,
            },
            init() {
                if (this.cooldown > 0) {
                    const t = setInterval(() => {
                        this.cooldown = Math.max(0, this.cooldown - 1);
                        const hidden = document.querySelector('input[x-ref=cooldown]');
                        if (hidden) hidden.value = this.cooldown;
                        if (this.cooldown === 0) clearInterval(t);
                    }, 1000);
                }
            },
        }));
    });
    </script>

    {{-- 有错误时抖动 --}}
    @error('email')
        <script>document.getElementById('email')?.closest('div')?.classList?.add('shake');</script>
    @enderror
    @error('password')
        <script>document.getElementById('password')?.closest('div')?.classList?.add('shake');</script>
    @enderror
</x-guest-layout>
