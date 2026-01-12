<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><polygon points='439.6,0 204.9,0 55.4,256 204.9,256 76.9,512 418.2,192 247.5,192' fill='%23000'/></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- Alpine.js x-cloak support -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900" x-data="{ sidebarOpen: false }">
            <!-- Topbar Fixed (solo mobile) -->
            <header class="fixed top-0 inset-x-0 lg:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 h-14 z-30">
                <div class="flex items-center justify-between h-full">
                    <!-- Mobile menu button + Logo -->
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!sidebarOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="sidebarOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md">
                        <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
                <!-- Sidebar -->
                <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                       class="fixed lg:static top-0 bottom-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-sm flex flex-col transition-transform duration-300 lg:translate-x-0 h-full">
                    <!-- Logo -->
                    <div class="flex h-14 px-4 items-center border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <x-application-logo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                            <span class="ml-2 text-base font-semibold text-gray-800 dark:text-gray-200">{{ config('app.name') }}</span>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                        <a href="{{ route('thunder-pack.sa.dashboard') }}" 
                           class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sa.dashboard') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('thunder-pack.sa.tenants.index') }}" 
                           class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sa.tenants.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            Organizaciones
                        </a>
                        <a href="{{ route('thunder-pack.sa.subscriptions.index') }}" 
                           class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sa.subscriptions.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            Suscripciones
                        </a>
                        <a href="{{ route('thunder-pack.sa.plans.index') }}" 
                           class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sa.plans.*') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            Planes
                        </a>
                        
                        @stack('superadmin-nav')
                    </nav>

                    <!-- User Info -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700" x-data="{ open: false }">
                        <div class="relative">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition">
                                <div class="flex items-center min-w-0">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <div class="text-left min-w-0">
                                        <div class="font-medium truncate">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-purple-600 dark:text-purple-400">Super Admin</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute bottom-full left-0 right-0 mb-1 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 py-1"
                                 style="display: none;">
                                
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Perfil
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Topbar (solo desktop) -->
                    <header class="hidden lg:block bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4">
                        <div class="flex items-center justify-between h-14">
                            <!-- Title -->
                            <div class="flex-1 lg:flex-none">
                                <div class="text-sm font-medium text-purple-600 dark:text-purple-400">Panel Super-Admin</div>
                            </div>

                            <!-- Dark Mode Toggle -->
                            <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
                        <div class="max-w-7xl mx-auto">
                            @yield('slot')
                            {{ $slot ?? '' }}
                        </div>
                    </main>
                </div>
            </div>

            <!-- Overlay para móvil - Click to close sidebar -->
            <div x-cloak
                 x-show="sidebarOpen" 
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden">
            </div>
        </div>
        @livewireScripts
    </body>
</html>
