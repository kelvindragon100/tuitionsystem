@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/60 shadow-xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
            <div class="text-lg font-semibold">Verify Your Email</div>
            <div class="text-sm opacity-90">Before proceeding, please check your email for a verification link.</div>
        </div>

        <div class="p-6 space-y-4">
            @if (session('status') === 'verification-link-sent')
                <div class="rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-700 dark:text-emerald-200 p-3">
                    A new verification link has been sent to your email address.
                </div>
            @endif

            <p class="text-sm text-gray-700 dark:text-gray-300">
                If you did not receive the email, click the button below to request another.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                @csrf
                <button class="px-4 py-2 rounded-lg text-white bg-indigo-500 hover:bg-indigo-600 transition">
                    Re-send verification email
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
