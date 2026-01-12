<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
    <!-- Modal Component -->
    @livewire('thunder-pack::create-tenant-with-plan')

    <div class="p-6">
        <!-- Messages -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
                {{ session('message') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 rounded-md text-sm">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="text-center mb-6">
            <x-application-logo class="h-10 w-auto mx-auto mb-3 fill-current text-gray-800 dark:text-gray-200" />
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Selecciona una organización</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Elige la organización con la que deseas trabajar</p>
        </div>
        
        @if($pendingInvitations->count() > 0)
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                        </svg>
                        Invitaciones Pendientes
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100">
                        {{ $pendingInvitations->count() }}
                    </span>
                </div>
                
                <div class="space-y-2">
                    @foreach($pendingInvitations as $invitation)
                        <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $invitation->tenant->name }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ ucfirst($invitation->role) }}
                                        </span>
                                        <span class="inline-flex items-center text-gray-600 dark:text-gray-400">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Expira {{ $invitation->expires_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('thunder-pack.invitations.accept', $invitation->token) }}" 
                                   class="ml-3 inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-xs font-medium rounded-md transition whitespace-nowrap">
                                    Ver Invitación
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($tenants->count() === 0)
            <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <svg class="w-10 h-10 text-gray-400 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p class="text-gray-600 dark:text-gray-300 text-sm font-medium">No perteneces a ninguna organización</p>
                <p class="text-gray-500 dark:text-gray-500 text-xs mt-1">Crea tu primera organización para comenzar</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($tenants as $tenant)
                    <button 
                        wire:click="selectTenant({{ $tenant->id }})"
                        wire:loading.attr="disabled"
                        wire:target="selectTenant"
                        class="w-full text-left px-3 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-all group disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-700 dark:group-hover:text-indigo-400">{{ $tenant->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $tenant->slug }}</div>
                            </div>
                            <div class="flex items-center">
                                <div wire:loading wire:target="selectTenant({{ $tenant->id }})" class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        <!-- Botón Crear Nuevo Tenant -->
        <div class="mt-4">
            <button 
                wire:click="$dispatch('open-create-tenant-modal')"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Crear Nueva Organización
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
            <button 
                wire:click="logout" 
                wire:loading.attr="disabled"
                wire:target="logout"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="logout">Cerrar sesión</span>
                <span wire:loading wire:target="logout">Cerrando sesión...</span>
            </button>
        </div>
    </div>
</div>
