<nav class="bg-gray-800 p-4 text-white">
    <div class="flex justify-between">
        <div>
            <a href="/" class="text-lg font-bold">Smart Tuition</a>
        </div>
        <div>
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="px-3">Dashboard</a>
                @elseif(Auth::user()->role === 'tutor')
                    <a href="{{ route('tutor.dashboard') }}" class="px-3">Dashboard</a>
                @else
                    <a href="{{ route('student.dashboard') }}" class="px-3">Dashboard</a>
                @endif

                <a href="{{ route('profile.edit') }}" class="px-3">Profile</a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3">Logout</button>
                </form>
            @endauth
        </div>
    </div>
</nav>
