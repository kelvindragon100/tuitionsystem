<x-guest-layout>
    <style>
        [x-cloak]{ display:none !important; }
        @keyframes shake{ 10%,90%{transform:translateX(-1px)} 20%,80%{transform:translateX(2px)} 30%,50%,70%{transform:translateX(-4px)} 40%,60%{transform:translateX(4px)} }
        .shake{ animation: shake 0.4s ease-in-out 1; }
    </style>

    <noscript>
        <div class="mb-4 rounded-xl border border-yellow-300 bg-yellow-50 text-yellow-900 p-3">
            JavaScript is disabled. Some interactive features (cooldown and attempt counters) won’t work.
        </div>
    </noscript>

    <div class="mb-5 text-center">
        <h1 class="text-xl font-semibold">{{ __('Welcome back') }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ __('Sign in to your account') }}</p>
    </div>

    {{-- 全局状态 --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- CSRF 防护提示 --}}
    @if (session('csrf_defended'))
        <x-security-banner type="success"
            title="Request blocked by CSRF protection."
            message="Your session token was invalid or missing. The app prevented a forged request." />
    @endif

    {{-- Brute Force 提示 --}}
    @php
        $cooldown = session('login_cooldown');
        $attempts = session('login_attempts');
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
                    <div class="flex-1">
                        <div class="font-medium">Hold on — login temporarily locked</div>
                        <p class="text-sm opacity-90">
                            Too many attempts. Please wait <span class="font-semibold" x-text="cooldown"></span>s.
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
                    <div class="flex-1">
                        <div class="text-sm">
                            Attempts left:
                            <span class="font-semibold" x-text="attempts.remaining"></span> /
                            <span x-text="attempts.max"></span>
                        </div>
                        <div class="text-xs mt-1 opacity-80">
                            Window: <span x-text="attempts.window"></span>s
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded bg-blue-100">
                            <div class="h-1.5 rounded bg-blue-400"
                                 x-bind:style="`width: ${Math.round((attempts.remaining/attempts.max)*100)}%`"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- 暴露状态给表单 --}}
        <input type="hidden" id="cooldownInput" x-ref="cooldown" :value="cooldown">
    </div>

    <div x-data="sqliGuard()" x-cloak>
        {{-- SQLi 告警 --}}
        <template x-if="sqliTriggered">
            <div class="mb-4 rounded-xl border border-red-300/70 bg-red-50 text-red-800 p-3">
                <div class="font-medium">Potential SQL Injection detected</div>
                <div class="text-sm opacity-90">Suspicious input blocked. Please remove SQL operators or keywords.</div>
            </div>
        </template>

        <form method="POST" action="{{ route('login') }}" class="space-y-4"
              @submit.prevent="if (Number(document.getElementById('cooldownInput').value || 0) > 0 || sqliTriggered) return; handleSubmit($event)">
            @csrf

            {{-- Email --}}
            <div :class="{'shake': sqliTriggered && lastField==='email'}">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                    x-model.trim="email"
                    @input="scan('email', $event.target.value)"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
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
                    <x-text-input id="password" name="password" class="block mt-1 w-full pr-10"
                        x-bind:type="show ? 'text' : 'password'"
                        x-model="password"
                        @input="scan('password', $event.target.value)"
                        required autocomplete="current-password" />

                    <button type="button"
                            @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Toggle password visibility">
                        {{-- eye-off --}}
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18M10.5 6.3C11 6.1 11.5 6 12 6c6 0 9.75 6 9.75 6a11.1 11.1 0 0 1-3.2 3.3M6.2 6.2A11.1 11.1 0 0 0 2.25 12S6 18 12 18c1.1 0 2.1-.2 3-.5" />
                        </svg>
                        {{-- eye --}}
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            {{-- 登录按钮 --}}
            <div class="pt-2">
                <x-primary-button
                    class="w-full justify-center"
                    x-bind:class="(Number(document.getElementById('cooldownInput').value || 0) > 0 || sqliTriggered) ? 'opacity-60 cursor-not-allowed' : ''"
                    x-bind:disabled="(Number(document.getElementById('cooldownInput').value || 0) > 0) || sqliTriggered"
                >
                    <span x-show="Number(document.getElementById('cooldownInput').value || 0) === 0">
                        {{ __('Log in') }}
                    </span>
                    <span x-show="Number(document.getElementById('cooldownInput').value || 0) > 0">
                        {{ __('Please wait') }} (<span x-text="document.getElementById('cooldownInput').value"></span>s)
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

    {{-- Brute-force & SQLi 脚本 --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rateGuard', ({cooldownFromServer = 0, attemptsFromServer = null} = {}) => ({
            cooldown: Number(cooldownFromServer || 0),
            attempts: {
                remaining: attemptsFromServer ? Number(attemptsFromServer.remaining) : 0,
                max: attemptsFromServer ? Number(attemptsFromServer.max) : 0,
                window: attemptsFromServer ? Number(attemptsFromServer.window) : 60,
            },
            window: attemptsFromServer ? Number(attemptsFromServer.window) : 60,
            init() {
                if (this.cooldown > 0) {
                    var t = setInterval(() => {
                        this.cooldown = Math.max(0, this.cooldown - 1);
                        var hidden = document.getElementById('cooldownInput');
                        if (hidden) hidden.value = this.cooldown;
                        if (this.cooldown === 0) clearInterval(t);
                    }, 1000);
                }
            },
        }));

        // sqliGuard：始终把值当字符串处理
        Alpine.data('sqliGuard', () => ({
            email: '',
            password: '',
            lastField: '',
            sqliTriggered: false,
            hits: [],
            scan: function(field, value) {
                this.lastField = field;
                var v = (value === undefined || value === null) ? '' : String(value);
                this[field] = v;

                var patterns = [
                    /(\b)(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|--|#)(\b)/i,
                    /['";]{2,}/,
                    /or\s+1=1/i
                ];
                this.hits = [];
                for (var i = 0; i < patterns.length; i++) {
                    if (patterns[i].test(v)) this.hits.push(String(patterns[i]));
                }
                this.sqliTriggered = this.hits.length > 0;
            },
            handleSubmit: function(e) {
                var cooldownEl = document.getElementById('cooldownInput');
                var cooldown = cooldownEl ? Number(cooldownEl.value) : 0;
                if (this.sqliTriggered || cooldown > 0) return;
                e.target.submit();
            }
        }));
    });
    </script>

    {{-- 有错误时抖动并聚焦（无可选链，兼容性更好） --}}
    @error('email')
        <script>
            (function(){
                var el = document.getElementById('email');
                if (el) {
                    var wrap = el.closest ? el.closest('div') : null;
                    if (wrap && wrap.classList) wrap.classList.add('shake');
                    try { el.focus(); } catch(e){}
                }
            })();
        </script>
    @enderror
    @error('password')
        <script>
            (function(){
                var el = document.getElementById('password');
                if (el) {
                    var wrap = el.closest ? el.closest('div') : null;
                    if (wrap && wrap.classList) wrap.classList.add('shake');
                    try { el.focus(); } catch(e){}
                }
            })();
        </script>
    @enderror
</x-guest-layout>
