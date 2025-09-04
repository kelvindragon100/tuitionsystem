@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Add New {{ ucfirst($type ?? 'tutor') }}</h1>
        <a href="{{ route('admin.users', ['type' => $type ?? 'tutor']) }}"
           class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            ← Back to list
        </a>
    </div>

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

    {{-- 成功提示（含复制密码） --}}
    @if (session('success'))
        @php $msg = session('success'); @endphp
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 dark:bg-green-900/30 dark:border-green-700 text-green-700 dark:text-green-200 p-4 flex items-start justify-between gap-3">
            <div class="space-y-1">
                <div class="font-semibold">Success</div>
                <div class="text-sm">{!! nl2br(e($msg)) !!}</div>
            </div>
            @if (\Illuminate\Support\Str::contains($msg, 'New password:'))
                @php preg_match('/New password:\s*([^\s]+)/i', $msg, $m); $pwd = $m[1] ?? ''; @endphp
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

    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden
                bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-teal-500 text-white">
            <div class="text-lg font-semibold">User Details</div>
            <div class="text-sm opacity-90">Create a new account</div>
        </div>

        <form action="{{ route('admin.users.create') }}" method="POST" class="p-6 space-y-5" id="createUserForm">
            @csrf

            {{-- 角色 --}}
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                               text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                               focus:ring-teal-400">
                    <option value="tutor"   {{ old('role', $type) === 'tutor' ? 'selected' : '' }}>Tutor</option>
                    <option value="student" {{ old('role', $type) === 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                              focus:ring-teal-400" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                              focus:ring-teal-400" />
            </div>

            {{-- 自动生成密码开关（默认勾选） --}}
            <div class="flex items-center justify-between gap-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input id="autoGenerate" type="checkbox" name="auto_generate" value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600"
                           {{ old('auto_generate', '1') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-200">
                        Auto-generate strong password (recommended)
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            A secure password will be generated. It’ll be shown after creation and you can copy it.
                        </span>
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input id="sendEmail" type="checkbox" name="send_email" value="1"
                           class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600"
                           {{ old('send_email') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-200">
                        Send email to user
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            We’ll email the new password to this user (if mail is configured).
                        </span>
                    </span>
                </label>
            </div>

            {{-- 密码区（默认被禁用，只有取消自动生成时才可填） --}}
            <div id="passwordBlock" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input id="passwordInput" type="password" name="password"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                  focus:ring-teal-400" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm Password</label>
                    <input id="passwordConfirmInput" type="password" name="password_confirmation"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2
                                  focus:ring-teal-400" />
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600
                               transition">
                    Create {{ ucfirst(old('role', $type)) }}
                </button>
                <a href="{{ route('admin.users', ['type' => $type ?? 'tutor']) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                          text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 开关：自动生成密码时禁用密码输入框 + 去除 required（默认勾选） --}}
<script>
(function () {
    const auto = document.getElementById('autoGenerate');
    const pass = document.getElementById('passwordInput');
    const pass2 = document.getElementById('passwordConfirmInput');
    const block = document.getElementById('passwordBlock');

    function sync() {
        const on = auto.checked;
        [pass, pass2].forEach(el => {
            el.disabled = on;
            el.required = !on;
            if (on) el.value = '';
        });
        block.style.opacity = on ? '0.5' : '1';
    }

    auto.addEventListener('change', sync);
    // 初始同步（默认勾选；也兼容表单验证失败回填的情况）
    sync();
})();
</script>
@endsection
