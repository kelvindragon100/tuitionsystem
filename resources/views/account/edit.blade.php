@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $mustVerify = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail;
    $verified   = $mustVerify ? $user->hasVerifiedEmail() : true;
    $canUpdate  = $verified; // 只有已验证邮箱时才允许更新
@endphp

<div class="p-6 max-w-5xl mx-auto space-y-8">

    {{-- 全局成功/状态提示（带关闭 & 自动淡出） --}}
    @php
        $status = session('status');
        $alertMap = [
            'account-updated'        => ['bg' => 'emerald', 'text' => 'Account updated successfully.'],
            'password-updated'       => ['bg' => 'emerald', 'text' => 'Password updated successfully.'],
            'verification-link-sent' => ['bg' => 'indigo',  'text' => 'A new verification link has been sent to your email.'],
            'email-verified'         => ['bg' => 'emerald', 'text' => 'Your email has been verified successfully.'],
            // 新增：未验证阻止时的文案（用于后端拦截返回）
            'email-not-verified'     => ['bg' => 'amber',   'text' => 'Please verify your email before updating account information.'],
        ];
    @endphp

    @if ($status && isset($alertMap[$status]))
        @php $cfg = $alertMap[$status]; @endphp
        <div id="globalAlert"
             class="rounded-lg border border-{{ $cfg['bg'] }}-300 bg-{{ $cfg['bg'] }}-50 text-{{ $cfg['bg'] }}-700
                    dark:bg-{{ $cfg['bg'] }}-900/30 dark:border-{{ $cfg['bg'] }}-700 dark:text-{{ $cfg['bg'] }}-200
                    p-4 relative">
            <button type="button"
                    class="absolute right-2 top-2 text-{{ $cfg['bg'] }}-700 dark:text-{{ $cfg['bg'] }}-200 hover:opacity-80"
                    onclick="document.getElementById('globalAlert')?.remove()">✕</button>
            {{ $cfg['text'] }}
        </div>
        <script>
            setTimeout(() => {
                const a = document.getElementById('globalAlert');
                if (a) {
                    a.style.transition = 'opacity .5s ease';
                    a.style.opacity = '0';
                    setTimeout(() => a.remove(), 500);
                }
            }, 4500);
        </script>
    @endif

    {{-- 顶部账户头像 + 简要信息 --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/60 shadow-lg p-6 flex items-center gap-4">
        <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center text-xl font-semibold shadow">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <div>
            <div class="text-lg font-semibold">{{ auth()->user()->name }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                    {{ auth()->user()->role === 'admin'
                        ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-200'
                        : (auth()->user()->role === 'tutor'
                            ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200'
                            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200') }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
                <span>{{ auth()->user()->email }}</span>
            </div>
        </div>
    </div>

    {{-- 1) 账户基本信息（未验证时禁用保存 + 禁用输入 + 显示遮罩） --}}
    <div class="relative rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white flex items-center gap-3">
            <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5.121 17.804A3 3 0 016 17h12a3 3 0 01.879 5.804M15 11a3 3 0 00-6 0v1h6v-1z"/>
            </svg>
            <div>
                <div class="text-lg font-semibold">Account Information</div>
                <div class="text-sm opacity-90">Update your account details and email address.</div>
            </div>
        </div>

        {{-- 遮罩：邮箱需要验证且未验证时显示（阻止） --}}
        @if($mustVerify && ! $verified)
            <div class="absolute inset-0 bg-gray-200/60 dark:bg-black/50 flex items-center justify-center z-10 backdrop-blur-sm">
                <div class="text-center text-sm text-gray-700 dark:text-gray-200 space-y-2">
                    <div class="font-medium">
                        Please verify your email to update account information.
                    </div>
                    <button type="submit" form="resendVerificationForm"
                            class="px-3 py-1.5 rounded-md text-white bg-indigo-500 hover:bg-indigo-600 transition text-sm inline-flex items-center gap-2">
                        Re-send verification email
                    </button>
                    <p class="text-xs opacity-80">Once verified, refresh this page and you can save changes.</p>
                </div>
            </div>
        @endif

        <form method="POST"
              action="{{ route('account.update') }}"
              class="p-6 space-y-5"
              novalidate
              data-allowed="{{ $canUpdate ? '1' : '0' }}"
              id="accountForm">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           autocomplete="name"
                           @if(!$canUpdate) disabled @endif
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                  focus:ring-indigo-400 disabled:opacity-70"/>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           autocomplete="email"
                           @if(!$canUpdate) disabled @endif
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                  focus:ring-indigo-400 disabled:opacity-70"/>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 邮箱验证状态（按钮使用非嵌套 form 提交） --}}
            @if ($mustVerify)
                @if ($verified)
                    <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Your email is verified.
                    </div>
                @else
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="text-sm text-amber-700 dark:text-amber-300">
                            Your email address is unverified.
                        </div>

                        {{-- 非嵌套表单触发（form 属性） --}}
                        <button type="submit" form="resendVerificationForm" id="resendBtn"
                                class="px-3 py-1.5 rounded-md text-white bg-indigo-500 hover:bg-indigo-600 transition text-sm inline-flex items-center gap-2">
                            <svg id="resendSpinner" class="hidden animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span id="resendText">Re-send verification email</span>
                        </button>

                        <span id="resendCooldown"
                              class="hidden text-xs text-gray-500 dark:text-gray-400">
                            Please wait <span id="cooldownSec">10</span>s...
                        </span>
                    </div>
                @endif
            @endif

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="submit" id="accountSubmit"
                        @if(!$canUpdate) disabled @endif
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 transition
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- 隐藏表单：重发验证邮件（非嵌套） --}}
    <form id="resendVerificationForm" method="POST" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    {{-- 2) 修改密码（与之前一致） --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-sky-500 to-indigo-500 text-white flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 11c-1.657 0-3 1.343-3 3v3h6v-3c0-1.657-1.343-3-3-3z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 11V8a5 5 0 0110 0v3"/>
            </svg>
            <div>
                <div class="text-lg font-semibold">Update Password</div>
                <div class="text-sm opacity-90">Use a long, random password to stay secure.</div>
            </div>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="p-6 space-y-5" id="passwordForm" novalidate>
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" autocomplete="current-password" required
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                      text-gray-800 dark:text-gray-200 px-4 py-2 pr-10 focus:outline-none focus:ring-2
                                      focus:ring-sky-400"/>
                        <button type="button" data-toggle="#current_password"
                                class="toggle-pass absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                aria-label="Show password">
                            <svg class="w-5 h-5 icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="w-5 h-5 icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.94 10.94 0 0112 19c-6.5 0-10-7-10-7a19.77 19.77 0 014.22-5.31"/>
                                <path d="M9.9 4.24A10.94 10.94 0 0112 5c6.5 0 10 7 10 7a19.8 19.8 0 01-3.17 4.13"/>
                                <path d="M9.88 9.88a3 3 0 104.24 4.24"/>
                                <path d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">New Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="new_password" autocomplete="new-password" required
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                      text-gray-800 dark:text-gray-200 px-4 py-2 pr-10 focus:outline-none focus:ring-2
                                      focus:ring-sky-400"/>
                        <button type="button" data-toggle="#new_password"
                                class="toggle-pass absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                aria-label="Show password">
                            <svg class="w-5 h-5 icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="w-5 h-5 icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.94 10.94 0 0112 19c-6.5 0-10-7-10-7a19.77 19.77 0 014.22-5.31"/>
                                <path d="M9.9 4.24A10.94 10.94 0 0112 5c6.5 0 10 7 10 7a19.8 19.8 0 01-3.17 4.13"/>
                                <path d="M9.88 9.88a3 3 0 104.24 4.24"/>
                                <path d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    {{-- 强度条 --}}
                    <div class="mt-2">
                        <div class="w-full h-1.5 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <div id="strength_bar" class="h-1.5 w-0 bg-red-500 transition-all duration-300"></div>
                        </div>
                        <div id="strength_text" class="mt-1 text-xs text-gray-600 dark:text-gray-400">Strength: -</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="confirm_password" autocomplete="new-password" required
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                      text-gray-800 dark:text-gray-200 px-4 py-2 pr-10 focus:outline-none focus:ring-2
                                      focus:ring-sky-400"/>
                        <button type="button" data-toggle="#confirm_password"
                                class="toggle-pass absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                aria-label="Show password">
                            <svg class="w-5 h-5 icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="w-5 h-5 icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.94 10.94 0 0112 19c-6.5 0-10-7-10-7a19.77 19.77 0 014.22-5.31"/>
                                <path d="M9.9 4.24A10.94 10.94 0 0112 5c6.5 0 10 7 10 7a19.8 19.8 0 01-3.17 4.13"/>
                                <path d="M9.88 9.88a3 3 0 104.24 4.24"/>
                                <path d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="submit" id="passwordSubmit"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-sky-500 to-indigo-500 hover:from-sky-600 hover:to-indigo-600 transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- 3) 删除账户（带 Modal 输入密码确认） --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white flex items-center gap-3">
            <svg class="w-5 h-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
            </svg>
            <div>
                <div class="text-lg font-semibold">Delete Account</div>
                <div class="text-sm opacity-90">This action cannot be undone.</div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Before deleting your account, please download any data or information that you wish to retain.
            </p>

            <button type="button"
                    class="open-delete-modal px-4 py-2 rounded-lg text-white shadow bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 transition"
                    data-entity="Account"
                    data-name="{{ auth()->user()->name }}"
                    data-email="{{ auth()->user()->email }}"
                    data-delete-url="{{ route('account.destroy') }}">
                Delete Account
            </button>
        </div>
    </div>
</div>

{{-- ===== 删除确认 Modal（带密码输入） ===== --}}
<form id="profileDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="password" id="hiddenDeletePassword">
</form>

<div id="deleteModal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
    <div id="deleteOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div id="deleteDialog"
             role="dialog" aria-modal="true" tabindex="-1"
             class="w-full max-w-md scale-95 opacity-0 transition-all duration-200
                    rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl
                    bg-white/90 dark:bg-gray-900/70 backdrop-blur outline-none">
            <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-t-2xl">
                <h3 class="text-lg font-semibold">Confirm Account Deletion</h3>
                <p class="text-sm opacity-90">This action cannot be undone.</p>
            </div>

            <div class="p-6 space-y-4">
                <div class="text-sm text-gray-700 dark:text-gray-200">
                    Please enter your password to confirm you would like to permanently delete your account.
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" id="deletePassword"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                  focus:ring-red-400"/>
                    <p id="deleteError" class="mt-1 text-sm text-red-600 dark:text-red-400 hidden">
                        Password is required.
                    </p>
                </div>

                <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded p-2">
                    Deleting your account is permanent. You cannot restore it later.
                </div>
            </div>

            <div class="px-6 py-4 flex items-center justify-end gap-3">
                <button type="button" id="cancelDelete"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                               text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button id="confirmDelete"
                        class="relative px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 transition
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center gap-2">
                        <svg id="confirmSpinner" class="hidden animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                        </svg>
                        <span id="confirmText">Delete</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== 脚本：增强交互（眼睛切换、强度条、删除 Modal、重发验证邮件、阻止未验证提交） ===== --}}
<script>
(function () {
    /* -------------------- 未验证前阻止提交（双保险，防止表单通过） -------------------- */
    const accountForm   = document.getElementById('accountForm');
    const accountSubmit = document.getElementById('accountSubmit');
    if (accountForm && accountSubmit) {
        const allowed = accountForm.getAttribute('data-allowed') === '1';
        if (!allowed) {
            accountForm.addEventListener('submit', (e) => {
                e.preventDefault();
                // 若你希望只禁用按钮、不禁用输入框，可以用下面的提示代替禁用输入逻辑：
                alert('Please verify your email before updating account information.');
            });
        }
    }

    /* -------------------- 密码显示/隐藏（SVG 图标切换版） -------------------- */
    document.querySelectorAll('.toggle-pass').forEach((btn) => {
        btn.addEventListener('click', () => {
            const selector = btn.getAttribute('data-toggle');
            const input = document.querySelector(selector);
            if (!input) return;

            const show = (input.type === 'password');
            input.type = show ? 'text' : 'password';

            const eye    = btn.querySelector('.icon-eye');
            const eyeOff = btn.querySelector('.icon-eye-off');

            if (eye && eyeOff) {
                if (show) {
                    eye.classList.add('hidden');
                    eyeOff.classList.remove('hidden');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    eye.classList.remove('hidden');
                    eyeOff.classList.add('hidden');
                    btn.setAttribute('aria-label', 'Show password');
                }
            }
        });
    });

    /* -------------------- 密码强度指示 -------------------- */
    const newPwd = document.getElementById('new_password');
    const bar    = document.getElementById('strength_bar');
    const txt    = document.getElementById('strength_text');

    function scorePassword(pwd) {
        let score = 0;
        if (!pwd) return score;
        const rules = [/.{8,}/, /[A-Z]/, /[a-z]/, /\d/, /[^A-Za-z0-9]/];
        rules.forEach(r => { if (r.test(pwd)) score++; });
        return score;
    }
    function updateStrength(pwd) {
        const s = scorePassword(pwd);
        const width = (s / 5) * 100;
        bar.style.width = width + '%';
        let label = 'Too weak', color = 'bg-red-500';
        if (s >= 4) { label = 'Strong'; color = 'bg-emerald-500'; }
        else if (s === 3) { label = 'Medium'; color = 'bg-yellow-500'; }
        else if (s === 2) { label = 'Weak'; color = 'bg-orange-500'; }
        bar.className = 'h-1.5 transition-all duration-300 ' + color;
        txt.textContent = 'Strength: ' + label;
    }
    newPwd?.addEventListener('input', e => updateStrength(e.target.value));

    /* -------------------- 删除 Modal -------------------- */
    const modal      = document.getElementById('deleteModal');
    const overlay    = document.getElementById('deleteOverlay');
    const dialog     = document.getElementById('deleteDialog');
    const cancelBtn  = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    const spinner    = document.getElementById('confirmSpinner');
    const confirmTxt = document.getElementById('confirmText');

    const passwordInput = document.getElementById('deletePassword');
    const passwordError = document.getElementById('deleteError');
    const hiddenPassword = document.getElementById('hiddenDeletePassword');
    const form      = document.getElementById('profileDeleteForm');

    let pendingAction = null;
    let lastFocused   = null;

    function setLoading(loading) {
        if (loading) {
            confirmBtn.setAttribute('disabled', 'disabled');
            spinner.classList.remove('hidden');
            confirmTxt.textContent = 'Deleting...';
        } else {
            confirmBtn.removeAttribute('disabled');
            spinner.classList.add('hidden');
            confirmTxt.textContent = 'Delete';
        }
    }
    function onKeydown(e) {
        if (e.key === 'Escape') closeModal();
        if (e.key === 'Enter' && !modal.classList.contains('hidden')) {
            e.preventDefault();
            submitDelete();
        }
    }
    function openModal(action) {
        lastFocused = document.activeElement;
        pendingAction = action;
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            dialog.classList.remove('opacity-0', 'scale-95');
        });
        setTimeout(() => passwordInput.focus({ preventScroll: true }), 0);
        document.addEventListener('keydown', onKeydown);
    }
    function closeModal() {
        overlay.classList.add('opacity-0');
        dialog.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            setLoading(false);
            passwordInput.value = '';
            passwordError.classList.add('hidden');
            if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
        }, 180);
        document.removeEventListener('keydown', onKeydown);
    }
    function submitDelete() {
        const pwd = (passwordInput.value || '').trim();
        if (!pwd) {
            passwordError.classList.remove('hidden');
            passwordInput.focus();
            return;
        }
        passwordError.classList.add('hidden');
        setLoading(true);
        hiddenPassword.value = pwd;
        form.setAttribute('action', pendingAction);
        setTimeout(() => form.submit(), 50);
    }
    document.querySelectorAll('.open-delete-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-delete-url');
            openModal(action);
        });
    });
    overlay.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });
    confirmBtn.addEventListener('click', (e) => { e.preventDefault(); submitDelete(); });

    /* -------------------- 重新发送验证邮件：按钮 Loading + 冷却 -------------------- */
    const resendForm   = document.getElementById('resendVerificationForm');
    const resendBtn    = document.getElementById('resendBtn');
    const resendSpin   = document.getElementById('resendSpinner');
    const resendText   = document.getElementById('resendText');
    const cooldownWrap = document.getElementById('resendCooldown');
    const cooldownSec  = document.getElementById('cooldownSec');

    function startCooldown(seconds = 10) {
        if (!cooldownWrap || !cooldownSec || !resendBtn) return;
        let s = seconds;
        cooldownWrap.classList.remove('hidden');
        resendBtn.classList.add('opacity-60', 'pointer-events-none');
        const timer = setInterval(() => {
            s--;
            cooldownSec.textContent = s;
            if (s <= 0) {
                clearInterval(timer);
                cooldownWrap.classList.add('hidden');
                resendBtn.classList.remove('opacity-60', 'pointer-events-none');
                resendBtn.removeAttribute('disabled');
                resendSpin.classList.add('hidden');
                resendText.textContent = 'Re-send verification email';
            }
        }, 1000);
    }

    if (resendForm && resendBtn && resendSpin) {
        resendForm.addEventListener('submit', () => {
            resendBtn.setAttribute('disabled', 'disabled');
            resendSpin.classList.remove('hidden');
            resendText.textContent = 'Sending...';
            startCooldown(10);
        });
    }
})();
</script>
@endsection
