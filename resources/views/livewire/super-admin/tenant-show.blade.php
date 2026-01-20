<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gestión de organización</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('thunder-pack.sa.tenants.edit', $tenant) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('thunder-pack.sa.tenants.limits', $tenant) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold uppercase rounded-md transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Límites
                </a>
                <a href="{{ route('thunder-pack.sa.tenants.delete', $tenant) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold uppercase rounded-md transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Eliminar
                </a>
                <a href="{{ route('thunder-pack.sa.tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">← Volver</a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-200 px-4 py-3 rounded-md text-sm" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-200 px-4 py-3 rounded-md text-sm" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px">
                        <button wire:click="switchTab('info')" 
                                class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'info' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                            Información
                        </button>
                        <button wire:click="switchTab('users')" 
                                class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'users' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                            Usuarios
                            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-full">
                                {{ $tenant->users->count() }}
                            </span>
                        </button>
                        <button wire:click="switchTab('subscriptions')" 
                                class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'subscriptions' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                            Suscripciones
                        </button>
                        <button wire:click="switchTab('whatsapp')" 
                                class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'whatsapp' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                            WhatsApp
                            @if($tenant->whatsappPhones->where('is_active', true)->count() > 0)
                                <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-green-500 rounded-full">
                                    {{ $tenant->whatsappPhones->where('is_active', true)->count() }}
                                </span>
                            @endif
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    @if($activeTab === 'info')
                        @include('thunder-pack::livewire.super-admin.partials.tenant-info')
                    @elseif($activeTab === 'users')
                        @include('thunder-pack::livewire.super-admin.partials.tenant-users')
                    @elseif($activeTab === 'subscriptions')
                        @include('thunder-pack::livewire.super-admin.partials.tenant-subscriptions')
                    @elseif($activeTab === 'whatsapp')
                        @include('thunder-pack::livewire.super-admin.partials.tenant-whatsapp')
                    @endif
                </div>
            </div>

    <!-- Phone Form Modal -->
    @if($showPhoneForm)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="resetPhoneForm"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ $editingPhoneId ? 'Editar Teléfono' : 'Agregar Teléfono' }}
                    </h3>
                    
                    <form wire:submit.prevent="savePhone" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Número de Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="phoneNumber" 
                                   placeholder="+50312345678"
                                   class="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('phoneNumber') border-red-500 @enderror">
                            @error('phoneNumber') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500">Formato E.164: +[código país][número]</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre de Instancia (Opcional)
                            </label>
                            <input type="text" wire:model="instanceName" 
                                   placeholder="custody_main"
                                   class="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500">Instancia en Evolution API (dejar vacío para usar default)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipos de Notificación
                            </label>
                            <div class="space-y-2">
                                @foreach($notificationTypes as $key => $label)
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="selectedNotificationTypes" value="{{ $key }}"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Dejar todos desmarcados para recibir todas las notificaciones</p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="isDefault"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Teléfono predeterminado</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" wire:model="isActive"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                            <button type="button" wire:click="resetPhoneForm"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                {{ $editingPhoneId ? 'Actualizar' : 'Agregar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Test Message Modal -->
    @if($showTestForm)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="resetTestForm"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Enviar Mensaje de Prueba
                    </h3>
                    
                    <form wire:submit.prevent="sendTestMessage" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="testPhoneId" 
                                    class="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('testPhoneId') border-red-500 @enderror">
                                <option value="">Seleccionar teléfono...</option>
                                @foreach($tenant->whatsappPhones->where('is_active', true) as $phone)
                                    <option value="{{ $phone->id }}">
                                        {{ $phone->display_phone }} 
                                        @if($phone->is_default) (Predeterminado) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('testPhoneId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Mensaje <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="testMessage" rows="6"
                                      class="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('testMessage') border-red-500 @enderror"></textarea>
                            @error('testMessage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                            <button type="button" wire:click="resetTestForm"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                                📤 Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Subscription Form Modal -->
    @if($showSubscriptionForm)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="closeSubscriptionForm"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Crear Suscripción Manual
                    </h3>
                    
                    <form wire:submit.prevent="createSubscription" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Plan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="selectedPlanId"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2 @error('selectedPlanId') border-red-500 @enderror">
                                <option value="">Seleccionar plan...</option>
                                @foreach(\ThunderPack\Models\Plan::all() as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} - ${{ number_format($plan->price_monthly, 2) }}/mes</option>
                                @endforeach
                            </select>
                            @error('selectedPlanId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Días de suscripción <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="subscriptionDays" min="1" max="365"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2 @error('subscriptionDays') border-red-500 @enderror">
                            @error('subscriptionDays') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Duración de la suscripción (1-365 días)</p>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="isTrial"
                                       class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Es período de prueba</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si está marcado, se establecerá como trial_ends_at</p>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                            <button type="button" wire:click="closeSubscriptionForm"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                Crear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
