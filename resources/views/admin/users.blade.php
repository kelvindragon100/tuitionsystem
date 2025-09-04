@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Tabs --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('admin.users', ['type' => 'tutor', 'search' => $search ?? '']) }}"
           class="px-4 py-2 rounded-lg transition shadow
                  {{ $type == 'tutor' 
                      ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white' 
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            Manage Tutors
        </a>
        <a href="{{ route('admin.users', ['type' => 'student', 'search' => $search ?? '']) }}"
           class="px-4 py-2 rounded-lg transition shadow
                  {{ $type == 'student' 
                      ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white' 
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            Manage Students
        </a>

        <div class="ml-auto flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            {{-- NEW: 导出 CSV（带上当前筛选条件） --}}
            <a href="{{ route('admin.users.export', ['type' => $type, 'search' => $search ?? '']) }}"
               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Export CSV
            </a>

            <span class="hidden sm:inline">•</span>
            <span>
                Showing <span class="font-semibold">{{ $users->count() }}</span> of
                <span class="font-semibold">{{ $users->total() }}</span> {{ ucfirst($type) }}{{ $users->total() > 1 ? 's' : '' }}
                @if(!empty($search)) for "<span class="font-semibold">{{ $search }}</span>" @endif
            </span>
        </div>
    </div>

    <h1 class="text-2xl font-bold mb-6">Manage {{ ucfirst($type) }}s</h1>

    {{-- 搜索框 --}}
    <form method="GET" action="{{ route('admin.users') }}" class="mb-6 flex items-center gap-2">
        <input type="hidden" name="type" value="{{ $type }}">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by name or email..." 
            value="{{ $search ?? '' }}"
            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 
                   bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 w-72 shadow-sm
                   focus:outline-none focus:ring-2 focus:ring-indigo-400"
        />
        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white 
                   rounded-lg hover:from-indigo-600 hover:to-purple-600 transition shadow">
            Search
        </button>
        @if(!empty($search))
            <a href="{{ route('admin.users', ['type' => $type]) }}"
               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Clear
            </a>
        @endif
    </form>

    {{-- 添加按钮 --}}
    <a href="{{ route('admin.users.create', ['type' => $type]) }}" 
       class="inline-block mb-4 px-5 py-2 bg-gradient-to-r from-green-500 to-teal-500 text-white 
              rounded-lg hover:from-green-600 hover:to-teal-600 transition shadow">
        + Add New {{ ucfirst($type) }}
    </a>

    {{-- 成功/错误提示 --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg shadow">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg shadow">
            {{ session('error') }}
        </div>
    @endif

    {{-- 表格 --}}
    @if($users->isEmpty())
        <div class="p-6 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">
            No {{ $type }}s found.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow">
                <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Role</th>
                        {{-- NEW: 状态列 --}}
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Created At</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($users as $user)
                        @php
                            // 这里假设使用 users 表的 banned_at（timestamp）或 is_banned（boolean）
                            // 你可以二选一：若没有 banned_at 就把它改成 is_banned。
                            $isBanned = isset($user->banned_at) ? !is_null($user->banned_at) : (bool)($user->is_banned ?? false);
                        @endphp
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <td class="py-3 px-4">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->role === 'tutor' 
                                        ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' 
                                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            {{-- NEW: 状态 --}}
                            <td class="py-3 px-4">
                                @if($isBanned)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Banned
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                        Edit
                                    </a>

                                    {{-- NEW: Ban / Unban --}}
                                    @if(!$isBanned)
                                        <form method="POST" action="{{ route('admin.users.ban', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1 bg-amber-500 text-white rounded hover:bg-amber-600 transition"
                                                    onclick="return confirm('Ban this user?')">
                                                Ban
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.unban', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 transition"
                                                    onclick="return confirm('Unban this user?')">
                                                Unban
                                            </button>
                                        </form>
                                    @endif

                                    {{-- 你原有的 Delete 改为弹窗 --}}
                                    <button type="button"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition open-delete-modal"
                                            data-delete-url="{{ route('admin.users.delete', $user->id) }}"
                                            data-entity="{{ ucfirst($user->role) }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="mt-6">
            {{ $users->appends(['search' => $search, 'type' => $type])->links('pagination::tailwind') }}
        </div>
    @endif
</div>

{{-- 隐藏的删除表单（由 Modal 触发提交） --}}
<form id="hiddenDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

{{-- 删除确认 Modal（无障碍 + 动画 + 焦点管理） --}}
<div id="deleteModal"
     class="fixed inset-0 z-[100] hidden"
     aria-hidden="true">
    {{-- overlay --}}
    <div id="deleteOverlay"
         class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-200"></div>

    {{-- dialog wrapper --}}
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div id="deleteDialog"
             role="dialog"
             aria-modal="true"
             aria-labelledby="deleteTitle"
             aria-describedby="deleteDesc"
             class="w-full max-w-md scale-95 opacity-0 transition-all duration-200
                    rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl
                    bg-white/90 dark:bg-gray-900/70 backdrop-blur outline-none">

            {{-- 头部 --}}
            <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-t-2xl">
                <h3 id="deleteTitle" class="text-lg font-semibold">Confirm Deletion</h3>
                <p id="deleteDesc" class="text-sm opacity-90">This action cannot be undone.</p>
            </div>

            {{-- 内容 --}}
            <div class="p-6 space-y-3">
                <p class="text-gray-700 dark:text-gray-200">
                    Are you sure you want to delete this
                    <span id="entityType" class="font-semibold">User</span>?
                </p>
                <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    <div><span class="font-medium">Name:</span> <span id="targetName">-</span></div>
                    <div><span class="font-medium">Email:</span> <span id="targetEmail">-</span></div>
                </div>
                {{-- 轻微风险提示（可选） --}}
                <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded p-2">
                    Deleting a user is permanent. You cannot restore this account later.
                </div>
            </div>

            {{-- 按钮区 --}}
            <div class="px-6 py-4 flex items-center justify-end gap-3">
                <button id="cancelDelete"
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


{{-- 脚本：控制 Modal --}}
<script>
(function () {
    const modal      = document.getElementById('deleteModal');
    const overlay    = document.getElementById('deleteOverlay');
    const dialog     = document.getElementById('deleteDialog');
    const cancelBtn  = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    const spinner    = document.getElementById('confirmSpinner');
    const confirmTxt = document.getElementById('confirmText');
    const form       = document.getElementById('hiddenDeleteForm');

    const entitySpan = document.getElementById('entityType');
    const nameSpan   = document.getElementById('targetName');
    const emailSpan  = document.getElementById('targetEmail');

    let pendingAction = null;
    let lastFocused   = null;

    // 可聚焦元素选择器（焦点陷阱）
    const FOCUSABLE = [
        'a[href]', 'button:not([disabled])', 'textarea:not([disabled])',
        'input[type="text"]:not([disabled])', 'input[type="email"]:not([disabled])',
        'input[type="password"]:not([disabled])', 'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    function trapFocus(e) {
        if (!modal || modal.classList.contains('hidden')) return;
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

    function openModal(payload) {
        // 记录上一个焦点（关闭后恢复）
        lastFocused = document.activeElement;

        // 填充信息
        entitySpan.textContent = payload.entity || 'User';
        nameSpan.textContent   = payload.name   || '-';
        emailSpan.textContent  = payload.email  || '-';
        pendingAction          = payload.action || null;

        // 显示 + 动画
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            dialog.classList.remove('opacity-0', 'scale-95');
        });

        // 焦点进入对话框
        setTimeout(() => {
            dialog.setAttribute('tabindex', '-1');
            dialog.focus({ preventScroll: true });
        }, 0);

        // 监听
        document.addEventListener('keydown', onKeydown);
        document.addEventListener('keydown', trapFocus, true);
    }

    function closeModal() {
        overlay.classList.add('opacity-0');
        dialog.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            setLoading(false);
            // 恢复焦点
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
    }

    // 事件委托：打开 Modal
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-delete-modal');
        if (!btn) return;

        openModal({
            entity: btn.getAttribute('data-entity'),
            name:   btn.getAttribute('data-name'),
            email:  btn.getAttribute('data-email'),
            action: btn.getAttribute('data-delete-url'),
        });
    });

    // 关闭：遮罩/取消
    overlay.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });

    // 确认删除：防重复提交 + Loading
    confirmBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!pendingAction) return;
        setLoading(true);

        // 设置 action 后提交隐藏表单
        form.setAttribute('action', pendingAction);
        // 轻微延迟给到 Loading 动画（可选）
        setTimeout(() => form.submit(), 50);
    });

    // 兜底：全局可用（如果其它页面想直接调用）
    window.openDeleteModal  = openModal;
    window.closeDeleteModal = closeModal;
})();
</script>

@endsection
