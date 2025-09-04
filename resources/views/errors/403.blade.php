@extends('layouts.app')

@section('title', 'Unauthorized')

@section('content')
<div class="text-center mt-20">
    <h1 class="text-4xl font-bold text-red-600">403 Forbidden</h1>
    <p class="mt-4 text-gray-600">You do not have permission to access this page.</p>
    <a href="{{ url('/') }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded">
        Back to Home
    </a>
</div>
@endsection
