@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Subject</h1>
        <a href="{{ route('admin.subjects.index') }}"
           class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            ← Back
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

    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden bg-white/80 dark:bg-gray-900/60 backdrop-blur">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white">
            <div class="text-lg font-semibold">Subject Details</div>
            <div class="text-sm opacity-90">Update information</div>
        </div>

        <form action="{{ route('admin.subjects.update', $subject->subject_id) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Subject ID</label>
                <input type="text" value="{{ $subject->subject_id }}" disabled
                       class="w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/70
                              text-gray-600 dark:text-gray-300 px-4 py-2" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="subject_Name" value="{{ old('subject_Name', $subject->subject_Name) }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="subject_Description" rows="4"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                 text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">{{ old('subject_Description', $subject->subject_Description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Duration (Hours)</label>
                    <input type="number" name="duration_Hours" min="0" value="{{ old('duration_Hours', $subject->duration_Hours) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fee (RM)</label>
                    <input type="number" name="subject_Fee" min="0" step="0.01" value="{{ old('subject_Fee', $subject->subject_Fee) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 transition">
                    Update
                </button>

                {{-- 删除按钮（可选在编辑页删除） --}}
                <button type="button"
                        class="px-4 py-2 rounded-lg text-white bg-red-500 hover:bg-red-600 transition open-delete-modal"
                        data-delete-url="{{ route('admin.subjects.destroy', $subject->subject_id) }}"
                        data-entity="Subject"
                        data-name="{{ $subject->subject_Name }}"
                        data-email="{{ $subject->subject_id }}">
                    Delete
                </button>

                <a href="{{ route('admin.subjects.index') }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                          text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 与 index 页面完全相同的删除弹窗（可复用相同脚本） --}}
<form id="hiddenDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

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
                               text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</button>
                <button id="confirmDelete"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 transition">Delete</button>
            </div>
        </div>
    </div>
</div>

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
