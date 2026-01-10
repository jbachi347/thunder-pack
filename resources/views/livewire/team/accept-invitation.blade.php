<div class="max-w-md w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="px-6 py-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-center border-b border-gray-200 dark:border-gray-700">
            <svg class="w-16 h-16 mx-auto mb-4 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Invitación de Equipo</h1>
        </div>

        <div class="px-6 py-8">
            @if($error)
                <!-- Error State -->
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Invitación No Válida
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $error }}
                    </p>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition">
                        Ir al Dashboard
                    </a>
                </div>

            @elseif($accepted)
                <!-- Success State -->
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        ¡Invitación Aceptada!
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Te has unido al equipo exitosamente. Redirigiendo...
                    </p>
                </div>

            @elseif($invitation)
                <!-- Invitation Details -->
                <div class="space-y-6">
                    <div class="text-center">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Has sido invitado a
                        </h2>
                        <p class="text-2xl font-bold text-blue-500 dark:text-blue-400">
                            {{ $invitation->tenant->name }}
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Correo:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invitation->email }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Rol:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200">
                                {{ $invitation->role }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Expira:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invitation->expires_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if(session()->has('error'))
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    @endif

                    @auth
                        <!-- User is logged in -->
                        @if(Auth::user()->email === $invitation->email)
                            <button 
                                wire:click="acceptInvitation"
                                class="w-full px-4 py-3 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>Aceptar Invitación</span>
                                <span wire:loading>Aceptando...</span>
                            </button>
                        @else
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    Esta invitación es para <strong>{{ $invitation->email }}</strong>, pero has iniciado sesión como <strong>{{ Auth::user()->email }}</strong>.
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition">
                                    Cerrar Sesión y Usar Otra Cuenta
                                </button>
                            </form>
                        @endif
                    @else
                        <!-- User not logged in -->
                        <div class="space-y-3">
                            <a href="{{ route('login') }}" class="block w-full px-4 py-3 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-center text-sm font-medium rounded-md transition">
                                Iniciar Sesión para Aceptar
                            </a>
                            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                                ¿No tienes cuenta? 
                                <a href="{{ route('register') }}" class="text-blue-500 dark:text-blue-400 hover:underline font-medium">
                                    Regístrate aquí
                                </a>
                            </p>
                        </div>
                    @endauth
                </div>
            @endif
        </div>
    </div>

    <!-- Back to home link -->
    <div class="mt-6 text-center">
        <a href="/" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            ← Volver al inicio
        </a>
    </div>
</div>
