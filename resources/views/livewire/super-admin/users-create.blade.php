<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('thunder-pack.sa.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Volver a Usuarios
            </a>
            <h1 class="mt-2 text-xl font-semibold text-gray-900 dark:text-gray-100">Crear Nuevo Usuario</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Crea un usuario y asígnalo a uno o más tenants
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <form wire:submit="save" class="space-y-6">
                
                <!-- Basic Info Section -->
                <div class="space-y-4">
                    <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100">Información Básica</h2>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                            id="name"
                            wire:model="name"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                            placeholder="Juan Pérez">
                        @error('name') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                            id="email"
                            wire:model="email"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                            placeholder="juan@example.com">
                        @error('email') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                            id="password"
                            wire:model="password"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mínimo 8 caracteres
                        </p>
                        @error('password') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirmar Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                            id="password_confirmation"
                            wire:model="password_confirmation"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                    </div>

                    <!-- Super Admin -->
                    <div class="flex items-center pt-2">
                        <input type="checkbox" 
                            id="is_super_admin"
                            wire:model="is_super_admin"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-900">
                        <label for="is_super_admin" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Marcar como Super Admin
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Asignación a Tenants</h2>

                    <!-- Default Role -->
                    <div class="mb-4">
                        <label for="defaultRole" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Rol por Defecto <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="defaultRole"
                            id="defaultRole"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Rol que tendrá en los tenants seleccionados
                        </p>
                    </div>

                    <!-- Tenants Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Seleccionar Tenants (opcional)
                        </label>
                        <div class="max-h-60 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3 bg-gray-50 dark:bg-gray-900">
                            @if($tenants->count() > 0)
                                @foreach($tenants as $tenant)
                                    <div class="flex items-center py-2">
                                        <input type="checkbox" 
                                            id="tenant_{{ $tenant->id }}"
                                            wire:model="selectedTenants"
                                            value="{{ $tenant->id }}"
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-900">
                                        <label for="tenant_{{ $tenant->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $tenant->name }}
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    No hay tenants disponibles
                                </p>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Puedes asignar el usuario a tenants después de crearlo
                        </p>
                        @error('selectedTenants') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('thunder-pack.sa.users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancelar
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Usuario
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
