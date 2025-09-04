@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Tutor Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- My Classes -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-sky-500 to-blue-500 dark:from-sky-700 dark:to-blue-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">My Classes</h2>
            <p class="mb-4 opacity-90">View and manage your assigned classes and schedules.</p>
            <a href="#" class="inline-block bg-white text-sky-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                View Classes
            </a>
        </div>

        <!-- Attendance -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-lime-500 to-green-500 dark:from-lime-700 dark:to-green-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Attendance</h2>
            <p class="mb-4 opacity-90">Mark and review attendance records of students.</p>
            <a href="#" class="inline-block bg-white text-lime-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                Manage Attendance
            </a>
        </div>

        <!-- Materials -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-purple-500 to-indigo-500 dark:from-purple-700 dark:to-indigo-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Materials</h2>
            <p class="mb-4 opacity-90">Upload and manage teaching materials for your students.</p>
            <a href="{{ route('tutor.materials.index') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                Manage Materials
            </a>
        </div>
    </div>
</div>
@endsection
