@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Manage Users -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-indigo-500 to-purple-500 dark:from-indigo-700 dark:to-purple-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Manage Users</h2>
            <p class="mb-4 opacity-90">Add, edit, and remove users from the system.</p>
            <a href="{{ route('admin.users') }}" class="inline-block bg-white text-indigo-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                Go to Users
            </a>
        </div>

        <!-- Subjects -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-emerald-500 to-teal-500 dark:from-emerald-700 dark:to-teal-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Manage Subjects</h2>
            <p class="mb-4 opacity-90">Create, update, and delete subjects for students.</p>
            <a href="{{ route('admin.subjects.index') }}" class="inline-block bg-white text-emerald-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                Go to Subjects
            </a>
        </div>

        <!-- Settings -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-pink-500 to-rose-500 dark:from-pink-700 dark:to-rose-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">System Settings</h2>
            <p class="mb-4 opacity-90">Configure application preferences and security.</p>
            <a href="#" class="inline-block bg-white text-pink-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                Open Settings
            </a>
        </div>
    </div>
</div>
@endsection
