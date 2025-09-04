<!DOCTYPE html>
<html lang="en" class="transition-colors duration-300">
<head>
    <meta charset="UTF-8">
    <title>Smart Tuition Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 transition-colors duration-300">

@php
    function activeClass($route) {
        // 支持通配（例如 admin.subjects.*）
        return request()->routeIs($route)
            ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md'
            : 'hover:bg-gradient-to-r hover:from-indigo-400 hover:to-purple-400 hover:text-white';
    }
@endphp

<div class="flex h-screen">

    {{-- Sidebar --}}
    <aside id="sidebar"
        class="w-64 rounded-r-xl shadow-xl 
               bg-gradient-to-b from-white/30 via-gray-50/20 to-gray-100/10
               dark:from-gray-800/40 dark:via-gray-900/30 dark:to-black/20
               backdrop-blur-xl border-r border-gray-200/50 dark:border-gray-700/50
               text-gray-800 dark:text-gray-200
               transform transition-transform duration-300 ease-in-out 
               md:translate-x-0 -translate-x-64 fixed md:relative h-full z-50">
        <div class="p-4 flex items-center justify-between border-b border-gray-200/50 dark:border-gray-600/50">
            <div class="text-lg font-bold flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6h4M8 6v6H4m8 0l4 4m0 0l-4 4m4-4H8"/>
                </svg>
                <span>Smart Tuition</span>
            </div>
            {{-- Dark Mode Toggle --}}
            <button id="toggleDark" class="p-2 rounded hover:bg-gray-200/40 dark:hover:bg-gray-700/40 transition">
                <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
                </svg>
                <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3v1m0 16v1m8.66-13.66l-.707.707M4.05 19.95l-.707.707M21 12h1M2 12H1m16.95 4.95l.707.707M4.05 4.05l.707.707M12 5a7 7 0 100 14 7 7 0 000-14z"/>
                </svg>
            </button>
        </div>

        {{-- Sidebar Links --}}
        <nav class="mt-6 space-y-1">
            @auth
                {{-- Admin Menu --}}
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('admin.dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users') }}"
                       class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('admin.users') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A3 3 0 016 17h12a3 3 0 01.879 5.804M15 11a3 3 0 00-6 0v1h6v-1z"/>
                        </svg>
                        Manage Users
                    </a>

                    {{-- 新增：Subjects --}}
                    <a href="{{ route('admin.subjects.index') }}"
                       class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('admin.subjects.*') }}">
                        {{-- 书本/科目图标 --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6l-2 1-2-1-2 1-2-1v12l2 1 2-1 2 1 2-1 2 1 2-1 2 1 2-1V6l-2 1-2-1-2 1-2-1z"/>
                        </svg>
                        Subjects
                    </a>
                @endif

                {{-- Tutor Menu --}}
                @if(Auth::user()->role === 'tutor')
                    <a href="{{ route('tutor.dashboard') }}"
                       class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('tutor.dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l9-5-9-5-9 5 9 5zm0 7v-7"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="#"
                       class="flex items-center px-4 py-2 rounded-md transition hover:bg-gradient-to-r hover:from-green-400 hover:to-teal-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        My Classes
                    </a>
                @endif

                {{-- Student Menu --}}
                @if(Auth::user()->role === 'student')
                    <a href="{{ route('student.dashboard') }}"
                       class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('student.dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 20h9M3 20h9M5 4h14M5 8h14M5 12h14M5 16h14"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="#"
                       class="flex items-center px-4 py-2 rounded-md transition hover:bg-gradient-to-r hover:from-pink-400 hover:to-red-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l9-5-9-5-9 5 9 5zm0 7v-7"/>
                        </svg>
                        Enrolled Courses
                    </a>
                @endif

                {{-- Common Menu --}}
                <a href="{{ route('account.edit') }}"
                class="flex items-center px-4 py-2 rounded-md transition {{ activeClass('account.edit') }}">
                    {{-- 账号 icon（圆形头像） --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14c3.866 0 7 3.134 7 7H5c0-3.866 3.134-7 7-7zM12 12a5 5 0 100-10 5 5 0 000 10z"/>
                    </svg>
                    Account Profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-4 py-2 rounded-md flex items-center transition hover:bg-gradient-to-r hover:from-red-500 hover:to-orange-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-16v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            @endauth
        </nav>
    </aside>


    <!-- Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden z-40 transition-opacity duration-300"></div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col">
        <header class="flex items-center justify-between bg-white dark:bg-gray-800 shadow px-6 py-4 transition-colors duration-300">
            <button id="menuBtn" class="md:hidden text-gray-800 dark:text-gray-200 focus:outline-none">☰</button>
            <div class="flex items-center space-x-4">
                <span>{{ Auth::user()->name ?? 'Guest' }}</span>
            </div>
        </header>

        <main class="p-6 flex-1 overflow-y-auto transition-colors duration-300">
            @yield('content')
        </main>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const menuBtn = document.getElementById('menuBtn');

    menuBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-64');
        overlay.classList.toggle('hidden');
    });

    overlay?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-64');
        overlay.classList.add('hidden');
    });

    const toggleDark = document.getElementById('toggleDark');
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');

    if (localStorage.getItem('theme') === 'dark' || 
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        moonIcon.classList.add('hidden');
        sunIcon.classList.remove('hidden');
    }

    toggleDark?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
            moonIcon.classList.add('hidden');
            sunIcon.classList.remove('hidden');
        } else {
            localStorage.setItem('theme', 'light');
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        }
    });
</script>
</body>
</html>
