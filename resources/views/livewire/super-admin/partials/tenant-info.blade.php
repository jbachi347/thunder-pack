<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Información del Tenant</h3>
    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Slug</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $tenant->slug }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Usuarios</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $tenant->users_count }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Storage Usado</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">
                {{ number_format($tenant->storage_used_bytes / 1024 / 1024, 2) }} MB / 
                {{ number_format($tenant->storage_quota_bytes / 1024 / 1024 / 1024, 2) }} GB
            </dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Uso de Storage</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">
                <div class="flex items-center space-x-2">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($storagePercentage, 100) }}%"></div>
                    </div>
                    <span>{{ number_format($storagePercentage, 1) }}%</span>
                </div>
            </dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Marca</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $tenant->brand_name ?? 'No configurado' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Creado</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $tenant->created_at->format('d/m/Y') }}</dd>
        </div>
    </dl>
</div>
