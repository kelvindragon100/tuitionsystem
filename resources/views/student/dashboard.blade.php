@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Student Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Enrolled Courses -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-cyan-500 to-teal-500 dark:from-cyan-700 dark:to-teal-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Enrolled Courses</h2>
            <p class="mb-4 opacity-90">Check your enrolled courses and progress.</p>
            <a href="#" class="inline-block bg-white text-cyan-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                View Courses
            </a>
        </div>

        <!-- Payment History -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-fuchsia-500 to-purple-500 dark:from-fuchsia-700 dark:to-purple-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Payment History</h2>
            <p class="mb-4 opacity-90">Review your payment records and receipts.</p>
            <a href="#" class="inline-block bg-white text-fuchsia-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                View Payments
            </a>
        </div>

        <!-- Announcements -->
        <div class="p-6 rounded-xl shadow-lg bg-gradient-to-r from-yellow-500 to-amber-500 dark:from-yellow-700 dark:to-amber-700 text-white hover:scale-[1.02] transition">
            <h2 class="text-xl font-semibold mb-2">Announcements</h2>
            <p class="mb-4 opacity-90">Stay updated with the latest announcements.</p>
            <a href="#" class="inline-block bg-white text-yellow-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100">
                View Announcements
            </a>
        </div>
    </div>
</div>
@endsection
