@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    {{-- 顶部提示（含复制新密码） --}}
    @if (session('success'))
        @php $msg = session('success'); @endphp
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 dark:bg-green-900/30 dark:border-green-700 text-green-700 dark:text-green-200 p-4 flex items-start justify-between gap-3">
            <div class="space-y-1">
                <div class="font-semibold">Success</div>
                <div class="text-sm">
                    {!! nl2br(e($msg)) !!}
                </div>
            </div>

            {{-- 如果包含 "New password:"，提供复制按钮 --}}
            @if (\Illuminate\Support\Str::contains($msg, 'New password:'))
                @php
                    preg_match('/New password:\s*([^\s]+)/i', $msg, $m);
                    $pwd = $m[1] ?? '';
                @endphp
                @if($pwd)
                    <button
                        class="shrink-0 px-3 py-2 rounded-lg text-white bg-amber-500 hover:bg-amber-600 transition"
                        onclick="navigator.clipboard.writeText(@js($pwd)).then(()=>alert('Copied!'))">
                        Copy Password
                    </button>
                @endif
            @endif
        </div>
    @endif

    {{-- 标题 + 右上角操作区 --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">
            Edit {{ ucfirst($user->role) }}
        </h1>

        <div class="flex items-center gap-2">
            {{-- Back --}}
            <a href="{{ route('admin.users', ['type' => $user->role]) }}"
               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                ← Back
            </a>

            {{-- Reset Password（需后端有对应路由 admin.users.resetPassword） --}}
            <form action="{{ route('admin.users.resetPassword', $user->id) }}" method="POST"
                  onsubmit="return confirm('Reset password for {{ $user->email }}? A new random password will be generated.')">
                @csrf
                <button type="submit"
                        class="px-3 py-2 rounded-lg text-white bg-amber-500 hover:bg-amber-600 transition">
                    Reset Password
                </button>
            </form>

            {{-- Delete（改：与 users.blade.php 同样写法，用 data-xxx + 统一脚本） --}}
            <button type="button"
                    class="px-3 py-2 rounded-lg text-white bg-red-500 hover:bg-red-600 transition open-delete-modal"
                    data-delete-url="{{ route('admin.users.delete', $user->id) }}"
                    data-entity="{{ ucfirst($user->role) }}"
                    data-name="{{ $user->name }}"
                    data-email="{{ $user->email }}">
                Delete
            </button>
        </div>
    </div>

    {{-- 校验错误 --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 dark:bg-red-900/30 dark:border-red-700 text-red-700 dark:text-red-200 p-4">
            <div class="font-semibold mb-2">There were some problems with your input:</div>
            <ul class="list-disc ml-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 表单卡片 --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden
                bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
            <div class="text-lg font-semibold">User Details</div>
            <div class="text-sm opacity-90">Update account information</div>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- 角色 --}}
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                               text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                               focus:ring-indigo-400">
                    <option value="tutor"   {{ old('role', $user->role) === 'tutor' ? 'selected' : '' }}>Tutor</option>
                    <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>

            {{-- 姓名 --}}
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                              focus:ring-indigo-400" />
            </div>

            {{-- 邮箱 --}}
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                              focus:ring-indigo-400" />
            </div>

            {{-- 密码（可选） --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        New Password
                        <span class="text-gray-500 text-xs">(leave blank to keep existing)</span>
                    </label>
                    <input type="password" name="password"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                focus:ring-indigo-400" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                focus:ring-indigo-400" />
                </div>
            </div>

            {{-- 按钮组 --}}
            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600
                               transition">
                    Update {{ ucfirst($user->role) }}
                </button>
                <a href="{{ route('admin.users', ['type' => $user->role]) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                          text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ====== 内联：删除确认 Modal + 隐藏删除表单（升级版） ====== --}}
<form id="editHiddenDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<div id="editDeleteModal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
    {{-- overlay --}}
    <div id="editDeleteOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-200"></div>

    {{-- dialog --}}
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div id="editDeleteDialog"
             role="dialog"
             aria-modal="true"
             aria-labelledby="editDeleteTitle"
             aria-describedby="editDeleteDesc"
             tabindex="-1"
             class="w-full max-w-md scale-95 opacity-0 transition-all duration-200
                    rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl
                    bg-white/90 dark:bg-gray-900/70 backdrop-blur outline-none">
            <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-t-2xl">
                <h3 id="editDeleteTitle" class="text-lg font-semibold">Confirm Deletion</h3>
                <p id="editDeleteDesc" class="text-sm opacity-90">This action cannot be undone.</p>
            </div>

            <div class="p-6 space-y-3">
                <p class="text-gray-700 dark:text-gray-200">
                    Are you sure you want to delete this <span id="editEntityType" class="font-semibold">User</span>?
                </p>
                <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    <div><span class="font-medium">Name:</span> <span id="editTargetName">-</span></div>
                    <div><span class="font-medium">Email:</span> <span id="editTargetEmail">-</span></div>
                </div>
                <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded p-2">
                    Deleting a user is permanent. You cannot restore this account later.
                </div>
            </div>

            <div class="px-6 py-4 flex items-center justify-end gap-3">
                <button type="button" id="editCancelDelete"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                               text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>

                <button type="submit" id="editConfirmDelete"
                        class="relative px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 transition
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center gap-2">
                        <svg id="editConfirmSpinner" class="hidden animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                        </svg>
                        <span id="editConfirmText">Delete</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ====== 内联：控制 Modal 的脚本（无 onclick，改为与 users.blade.php 相同绑定） ====== --}}
<script>
(function () {
    const modal      = document.getElementById('editDeleteModal');
    const overlay    = document.getElementById('editDeleteOverlay');
    const dialog     = document.getElementById('editDeleteDialog');
    const cancelBtn  = document.getElementById('editCancelDelete');
    const confirmBtn = document.getElementById('editConfirmDelete');
    const spinner    = document.getElementById('editConfirmSpinner');
    const confirmTxt = document.getElementById('editConfirmText');
    const form       = document.getElementById('editHiddenDeleteForm');

    const entitySpan = document.getElementById('editEntityType');
    const nameSpan   = document.getElementById('editTargetName');
    const emailSpan  = document.getElementById('editTargetEmail');

    let pendingAction = null;
    let lastFocused   = null;

    const FOCUSABLE = [
        'a[href]', 'button:not([disabled])', 'textarea:not([disabled])',
        'input[type="text"]:not([disabled])', 'input[type="email"]:not([disabled])',
        'input[type="password"]:not([disabled])', 'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

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

    function trapFocus(e) {
        if (modal.classList.contains('hidden')) return;
        const focusables = dialog.querySelectorAll(FOCUSABLE);
        if (!focusables.length) return;
        const first = focusables[0];
        const last  = focusables[focusables.length - 1];
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        }
    }

    function openModal(payload) {
        lastFocused = document.activeElement;

        entitySpan.textContent = payload.entity || 'User';
        nameSpan.textContent   = payload.name   || '-';
        emailSpan.textContent  = payload.email  || '-';
        pendingAction          = payload.action || null;

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            dialog.classList.remove('opacity-0', 'scale-95');
        });

        // 聚焦到对话框
        setTimeout(() => dialog.focus({ preventScroll: true }), 0);

        document.addEventListener('keydown', onKeydown);
        document.addEventListener('keydown', trapFocus, true);
    }

    function closeModal() {
        overlay.classList.add('opacity-0');
        dialog.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            setLoading(false);
            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        }, 180);

        pendingAction = null;
        document.removeEventListener('keydown', onKeydown);
        document.removeEventListener('keydown', trapFocus, true);
    }

    function onKeydown(e) {
        if (e.key === 'Escape') closeModal();
        if (e.key === 'Enter' && !confirmBtn.disabled && !modal.classList.contains('hidden')) {
            e.preventDefault();
            submitDelete();
        }
    }

    overlay.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });
    confirmBtn.addEventListener('click', (e) => { e.preventDefault(); submitDelete(); });

    function submitDelete() {
        if (!pendingAction) return;
        setLoading(true);
        form.setAttribute('action', pendingAction);
        setTimeout(() => form.submit(), 50);
    }

    // 与 users.blade.php 一样：统一绑定 .open-delete-modal
    document.querySelectorAll('.open-delete-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                entity: btn.getAttribute('data-entity'),
                name: btn.getAttribute('data-name'),
                email: btn.getAttribute('data-email'),
                action: btn.getAttribute('data-delete-url'),
            });
        });
    });
})();
</script>
@endsection
