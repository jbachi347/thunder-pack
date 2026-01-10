<div>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 leading-tight">Panel Super-Admin</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Tenants</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total_tenants'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Activos</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['active_tenants'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Usuarios</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total_users'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Storage Usado</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                        {{ number_format($stats['total_storage_used'] / 1024 / 1024 / 1024, 2) }} GB
                    </div>
                </div>
            </div>

            <!-- Recent Tenants -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Tenants Recientes</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentTenants as $tenant)
                        <a href="{{ route('thunder-pack.sa.tenants.show', $tenant) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $tenant->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tenant->slug }}</div>
                                </div>
                                <div class="text-right">
                                    @if($tenant->subscriptions->first())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($tenant->subscriptions->first()->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($tenant->subscriptions->first()->status === 'trialing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ ucfirst($tenant->subscriptions->first()->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay tenants registrados</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
