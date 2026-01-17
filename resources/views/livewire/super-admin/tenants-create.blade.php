<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('thunder-pack.sa.tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Volver a Tenants
            </a>
            <h1 class="mt-2 text-xl font-semibold text-gray-900 dark:text-gray-100">Crear Nuevo Tenant</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Crea una nueva organización o empresa en el sistema
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <form wire:submit="save" class="space-y-6">
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        id="name"
                        wire:model.live="name"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                        placeholder="Acme Corporation">
                    @error('name') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        id="slug"
                        wire:model="slug"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                        placeholder="acme-corporation">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Identificador único en URL. Se genera automáticamente del nombre.
                    </p>
                    @error('slug') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Brand Name -->
                <div>
                    <label for="brand_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre de Marca
                    </label>
                    <input type="text" 
                        id="brand_name"
                        wire:model="brand_name"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                        placeholder="Acme Corp">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Nombre corto para mostrar en la interfaz. Opcional, por defecto usa el nombre.
                    </p>
                    @error('brand_name') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Storage Quota -->
                <div>
                    <label for="storage_quota_gb" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Cuota de Almacenamiento (GB) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                        id="storage_quota_gb"
                        wire:model="storage_quota_gb"
                        min="1"
                        max="1000"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                        placeholder="10">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Cantidad de almacenamiento asignado en Gigabytes (1-1000 GB).
                    </p>
                    @error('storage_quota_gb') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('thunder-pack.sa.tenants.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancelar
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Tenant
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
