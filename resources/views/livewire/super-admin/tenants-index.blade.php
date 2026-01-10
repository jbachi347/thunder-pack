<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tenants</h2>
            <a href="{{ route('sa.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">← Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-4 p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o slug..." 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos los estados</option>
                            <option value="active">Activos</option>
                            <option value="trialing">Prueba</option>
                            <option value="past_due">Vencidos</option>
                            <option value="canceled">Cancelados</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tenants List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($tenants as $tenant)
                        <a href="{{ route('sa.tenants.show', $tenant) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $tenant->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tenant->slug }}</div>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $tenant->users_count }} usuarios</span>
                                        <span>•</span>
                                        <span>{{ number_format($tenant->storage_used_bytes / 1024 / 1024, 2) }} MB usado</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($tenant->subscriptions->first())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($tenant->subscriptions->first()->status === 'active') bg-green-100 text-green-800
                                            @elseif($tenant->subscriptions->first()->status === 'trialing') bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($tenant->subscriptions->first()->status) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            Sin suscripción
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron tenants</div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</div>
