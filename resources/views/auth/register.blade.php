<x-guest-layout>
    {{-- 防止 Alpine 条件块闪现 + 抖动动画 --}}
    <style>
        [x-cloak]{ display:none !important; }
        @keyframes shake{10%,90%{transform:translateX(-1px)}20%,80%{transform:translateX(2px)}30%,50%,70%{transform:translateX(-4px)}40%,60%{transform:translateX(4px)}}
        .shake{ animation: shake .4s ease-in-out 1; }
    </style>

    {{-- 顶部标题 --}}
    <div class="mb-5 text-center">
        <h1 class="text-xl font-semibold">{{ __('Create an account') }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ __('Sign up to access the Tuition System') }}</p>
    </div>

    {{-- CSRF 防护成功提示（bootstrap/app.php 已注入 csrf_defended） --}}
    @if (session('csrf_defended'))
        <x-security-banner type="success"
            title="Request blocked by CSRF protection."
            message="CSRF token was missing or invalid. The app prevented a forged request." />
    @endif

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

        <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate @submit.prevent="handleSubmit($event)">
            @csrf

            {{-- Name --}}
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name"
                              class="block mt-1 w-full"
                              type="text"
                              name="name"
                              value="{{ old('name') }}"
                              required
                              autofocus
                              autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            {{-- Email --}}
            <div x-bind:class="{'shake': sqliTriggered && lastField==='email'}">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email"
                              class="block mt-1 w-full"
                              type="email"
                              name="email"
                              x-model.trim="email"
                              @input="scan('email')"
                              x-bind:class="sqliTriggered && lastField==='email' ? 'border-red-400 ring-2 ring-red-200' : ''"
                              value="{{ old('email') }}"
                              required
                              autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password（带显示/隐藏切换 + SQLi 扫描） --}}
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <div x-data="{ show: false }" class="relative" x-cloak x-bind:class="{'shake': sqliTriggered && lastField==='password'}">
                    <x-text-input id="password"
                                  class="block mt-1 w-full pr-10"
                                  x-bind:type="show ? 'text' : 'password'"
                                  name="password"
                                  x-model="password"
                                  @input="scan('password')"
                                  x-bind:class="sqliTriggered && lastField==='password' ? 'border-red-400 ring-2 ring-red-200' : ''"
                                  required
                                  autocomplete="new-password" />
                    <button type="button"
                            @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Toggle password visibility">
                        {{-- 闭眼 --}}
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18M10.5 6.3C11 6.1 11.5 6 12 6c6 0 9.75 6 9.75 6a11.1 11.1 0 0 1-3.2 3.3M6.2 6.2A11.1 11.1 0 0 0 2.25 12S6 18 12 18c1.1 0 2.1-.2 3-.5" />
                        </svg>
                        {{-- 睁眼 --}}
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.25 12S6 6 12 6s9.75 6 9.75 6-3.75 6-9.75 6S2.25 12 2.25 12z"/>
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <div x-data="{ show: false }" class="relative" x-cloak x-bind:class="{'shake': sqliTriggered && lastField==='password_confirmation'}">
                    <x-text-input id="password_confirmation"
                                  class="block mt-1 w-full pr-10"
                                  x-bind:type="show ? 'text' : 'password'"
                                  name="password_confirmation"
                                  x-model="passwordConfirmation"
                                  @input="scan('password_confirmation')"
                                  x-bind:class="sqliTriggered && lastField==='password_confirmation' ? 'border-red-400 ring-2 ring-red-200' : ''"
                                  required
                                  autocomplete="new-password" />
                    <button type="button"
                            @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            aria-label="Toggle confirm password visibility">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3l18 18M10.5 6.3C11 6.1 11.5 6 12 6c6 0 9.75 6 9.75 6a11.1 11.1 0 0 1-3.2 3.3M6.2 6.2A11.1 11.1 0 0 0 2.25 12S6 18 12 18c1.1 0 2.1-.2 3-.5" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2.25 12S6 6 12 6s9.75 6 9.75 6-3.75 6-9.75 6S2.25 12 2.25 12z"/>
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center"
                    x-bind:class="sqliTriggered ? 'opacity-60 cursor-not-allowed' : ''"
                    x-bind:disabled="sqliTriggered">
                    {{ __('Register') }}
                </x-primary-button>
            </div>

            {{-- Login link --}}
            <p class="text-center text-sm text-gray-600">
                {{ __('Already registered?') }}
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">
                    {{ __('Sign in here') }}
                </a>
            </p>
        </form>
    </div>

    {{-- 共享 SQLi 守卫脚本 --}}
    @include('partials.sqli-guard')
</x-guest-layout>
