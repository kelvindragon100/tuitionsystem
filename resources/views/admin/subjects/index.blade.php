@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Manage Subjects</h1>

        <a href="{{ route('admin.subjects.create') }}"
           class="px-4 py-2 rounded-lg text-white shadow
                  bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 transition">
            + Add Subject
        </a>
    </div>

    {{-- 提示 --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg shadow">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg shadow">{{ session('error') }}</div>
    @endif

    {{-- 搜索 --}}
    <form method="GET" action="{{ route('admin.subjects.index') }}" class="mb-6 flex items-center gap-2">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by ID or Name..."
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                      text-gray-800 dark:text-gray-200 w-72 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        <button type="submit"
                class="px-4 py-2 rounded-lg text-white shadow
                       bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 transition">
            Search
        </button>
        @if(!empty($search))
            <a href="{{ route('admin.subjects.index') }}"
               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Clear
            </a>
        @endif
    </form>

    {{-- 表格 --}}
    @if($subjects->isEmpty())
        <div class="p-6 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">No subjects found.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow">
                <thead class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">Subject ID</th>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Duration (Hours)</th>
                        <th class="py-3 px-4 text-left">Fee (RM)</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($subjects as $subject)
                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <td class="py-3 px-4 font-mono">{{ $subject->subject_id }}</td>
                            <td class="py-3 px-4">{{ $subject->subject_Name }}</td>
                            <td class="py-3 px-4">{{ $subject->duration_Hours }}</td>
                            <td class="py-3 px-4">{{ number_format($subject->subject_Fee, 2) }}</td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.subjects.edit', $subject->subject_id) }}"
                                       class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Edit</a>

                                    <button type="button"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition open-delete-modal"
                                            data-delete-url="{{ route('admin.subjects.destroy', $subject->subject_id) }}"
                                            data-entity="Subject"
                                            data-name="{{ $subject->subject_Name }}"
                                            data-email="{{ $subject->subject_id }}">
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
            {{ $subjects->appends(['search' => $search])->links('pagination::tailwind') }}
        </div>
    @endif
</div>

{{-- 隐藏删除表单 --}}
<form id="hiddenDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

{{-- 删除确认 Modal（与 Users 页同风格） --}}
<div id="deleteModal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
    <div id="deleteOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div id="deleteDialog"
             class="w-full max-w-md scale-95 opacity-0 transition-all duration-200
                    rounded-2xl border border-gray-200 dark:border-gray-800 shadow-2xl
                    bg-white/90 dark:bg-gray-900/70 backdrop-blur">
            <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-t-2xl">
                <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                <p class="text-sm opacity-90">This action cannot be undone.</p>
            </div>
            <div class="p-6 space-y-3">
                <p class="text-gray-700 dark:text-gray-200">
                    Delete this <span id="entityType" class="font-semibold">Subject</span>?
                </p>
                <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    <div><span class="font-medium">Subject:</span> <span id="targetName">-</span></div>
                    <div><span class="font-medium">ID:</span> <span id="targetEmail">-</span></div>
                </div>
            </div>
            <div class="px-6 py-4 flex items-center justify-end gap-3">
                <button id="cancelDelete"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                               text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button id="confirmDelete"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 transition">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 控制 Modal 的脚本（同 Users 页逻辑） --}}
<script>
(function () {
    const modal   = document.getElementById('deleteModal');
    const overlay = document.getElementById('deleteOverlay');
    const dialog  = document.getElementById('deleteDialog');
    const cancel  = document.getElementById('cancelDelete');
    const confirm = document.getElementById('confirmDelete');
    const form    = document.getElementById('hiddenDeleteForm');

    const entitySpan = document.getElementById('entityType');
    const nameSpan   = document.getElementById('targetName');
    const emailSpan  = document.getElementById('targetEmail');

    let pendingAction = null;

    function openModal(payload) {
        entitySpan.textContent = payload.entity || 'Subject';
        nameSpan.textContent   = payload.name   || '-';
        emailSpan.textContent  = payload.email  || '-';
        pendingAction          = payload.action || null;

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            dialog.classList.remove('opacity-0', 'scale-95');
        });
        document.addEventListener('keydown', onEsc);
    }
    function closeModal() {
        overlay.classList.add('opacity-0');
        dialog.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 180);
        document.removeEventListener('keydown', onEsc);
    }
    function onEsc(e){ if(e.key === 'Escape') closeModal(); }

    overlay.addEventListener('click', closeModal);
    cancel.addEventListener('click', (e)=>{ e.preventDefault(); closeModal(); });

    confirm.addEventListener('click', (e)=>{
        e.preventDefault();
        if(!pendingAction) return;
        form.setAttribute('action', pendingAction);
        form.submit();
    });

    document.querySelectorAll('.open-delete-modal').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            openModal({
                entity: btn.getAttribute('data-entity'),
                name:   btn.getAttribute('data-name'),
                email:  btn.getAttribute('data-email'),
                action: btn.getAttribute('data-delete-url'),
            });
        });
    });
})();
</script>
@endsection
