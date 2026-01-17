<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('thunder-pack.sa.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Volver a Usuarios
            </a>
            <div class="mt-2 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $user->email }}
                    </p>
                </div>
                @if($user->is_super_admin)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                        Super Admin
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Basic Info & Password -->
            <div class="space-y-6">
                
                <!-- Basic Info Card -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100">Información Básica</h2>
                        @if(!$editingBasicInfo)
                            <button wire:click="toggleEditBasicInfo"
                                class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Editar
                            </button>
                        @endif
                    </div>

                    @if($editingBasicInfo)
                        <form wire:submit="saveBasicInfo" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nombre
                                </label>
                                <input type="text" 
                                    wire:model="name"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                @error('name') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email
                                </label>
                                <input type="email" 
                                    wire:model="email"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                @error('email') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                    id="is_super_admin"
                                    wire:model="is_super_admin"
                                    class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-900">
                                <label for="is_super_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Super Admin
                                </label>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Guardar
                                </button>
                                <button type="button"
                                    wire:click="toggleEditBasicInfo"
                                    class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @else
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Registrado</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                <!-- Password Card -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100">Contraseña</h2>
                        @if(!$editingPassword)
                            <button wire:click="toggleEditPassword"
                                class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Cambiar
                            </button>
                        @endif
                    </div>

                    @if($editingPassword)
                        <form wire:submit="savePassword" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nueva Contraseña
                                </label>
                                <input type="password" 
                                    wire:model="password"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                @error('password') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Confirmar Contraseña
                                </label>
                                <input type="password" 
                                    wire:model="password_confirmation"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Actualizar
                                </button>
                                <button type="button"
                                    wire:click="toggleEditPassword"
                                    class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ••••••••
                        </p>
                    @endif
                </div>

            </div>

            <!-- Right Column - Tenants -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100">Tenants Asignados ({{ $user->tenants->count() }})</h2>
                        @if(!$showAddTenantForm)
                            <button wire:click="toggleAddTenantForm"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Asignar Tenant
                            </button>
                        @endif
                    </div>

                    @if($showAddTenantForm)
                        <form wire:submit="addTenant" class="mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tenant
                                    </label>
                                    <select wire:model="selectedTenantId"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                        <option value="">Seleccionar tenant...</option>
                                        @foreach($availableTenants as $tenant)
                                            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedTenantId') 
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Rol
                                    </label>
                                    <select wire:model="selectedRole"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                        <option value="owner">Owner</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Asignar
                                </button>
                                <button type="button"
                                    wire:click="toggleAddTenantForm"
                                    class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Tenants List -->
                    @if($user->tenants->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->tenants as $tenant)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $tenant->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Rol: <span class="font-medium">{{ ucfirst($tenant->pivot->role) }}</span>
                                            @if($tenant->pivot->is_owner)
                                                <span class="ml-1 text-purple-600 dark:text-purple-400">(Owner)</span>
                                            @endif
                                        </p>
                                    </div>
                                    <button wire:click="removeTenant({{ $tenant->id }})"
                                        onclick="return confirm('¿Remover este tenant del usuario?')"
                                        class="text-sm text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Remover
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                            Este usuario no tiene tenants asignados
                        </p>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
