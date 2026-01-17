<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Eliminar Tenant</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Esta acción no se puede deshacer
            </p>
        </div>

        <!-- Warning Card -->
        <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-base font-medium text-red-800 dark:text-red-200">⚠️ Advertencia: Eliminación Permanente</h3>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                        Estás a punto de eliminar el tenant <strong>{{ $tenant->name }}</strong> ({{ $tenant->slug }}).
                    </p>
                </div>
            </div>
        </div>

        <!-- Tenant Info -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Información del Tenant</h2>
            
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Nombre:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Slug:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->slug }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Usuarios asociados:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->users->count() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Suscripciones:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->subscriptions->count() }}</dd>
                </div>
                @php
                    $activeSubscription = $tenant->subscriptions()->whereIn('status', ['active', 'trialing'])->first();
                @endphp
                @if($activeSubscription)
                    <div class="flex justify-between">
                        <dt class="text-sm text-red-600 dark:text-red-400">Estado suscripción:</dt>
                        <dd class="text-sm font-medium text-red-600 dark:text-red-400">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                {{ ucfirst($activeSubscription->status) }}
                            </span>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Impact Warning -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Lo que se eliminará:</h3>
            <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                <li>{{ $tenant->users->count() }} relaciones de usuario serán eliminadas</li>
                <li>{{ $tenant->subscriptions->count() }} suscripciones asociadas</li>
                <li>Todos los límites personalizados del tenant</li>
                <li>Historial de eventos de pago</li>
                <li>Configuración de WhatsApp (si existe)</li>
            </ul>
        </div>

        @if(!$confirmingDeletion)
            <!-- Initial Buttons -->
            <div class="flex justify-between">
                <a href="{{ route('thunder-pack.sa.tenants.show', $tenant) }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button wire:click="confirmDeletion" type="button"
                    @if($activeSubscription) disabled @endif
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                    @if($activeSubscription)
                        No se puede eliminar (Suscripción activa)
                    @else
                        Continuar con eliminación
                    @endif
                </button>
            </div>
        @else
            <!-- Confirmation Form -->
            <form wire:submit="delete" class="space-y-4">
                <div>
                    <label for="confirmationInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Para confirmar, escribe el slug del tenant: <span class="font-mono font-bold text-red-600 dark:text-red-400">{{ $tenant->slug }}</span>
                    </label>
                    <input wire:model="confirmationInput" 
                        id="confirmationInput"
                        type="text" 
                        placeholder="Escribe el slug aquí..."
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 dark:focus:border-red-600 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-600 rounded-md text-sm px-3 py-2">
                    @error('confirmationInput')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between">
                    <button wire:click="cancelDeletion" type="button"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Eliminar permanentemente
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>
