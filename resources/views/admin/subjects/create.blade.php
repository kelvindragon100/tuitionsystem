@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Add Subject</h1>
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
            <div class="text-sm opacity-90">Create a new subject</div>
        </div>

        <form action="{{ route('admin.subjects.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="subject_Name" value="{{ old('subject_Name') }}" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="subject_Description" rows="4"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                 text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                          placeholder="Optional">{{ old('subject_Description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Duration (Hours)</label>
                    <input type="number" name="duration_Hours" min="0" value="{{ old('duration_Hours', 0) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fee (RM)</label>
                    <input type="number" name="subject_Fee" min="0" step="0.01" value="{{ old('subject_Fee', 0) }}" required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800
                                  text-gray-800 dark:text-gray-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-white shadow
                               bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 transition">
                    Create
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
@endsection
