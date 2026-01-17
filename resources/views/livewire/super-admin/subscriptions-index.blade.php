<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Suscripciones</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gestiona las suscripciones activas e historial
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <button wire:click="$set('statusFilter', 'active')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <div class="text-xs text-gray-500 dark:text-gray-400">Activas</div>
                <div class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $statusCounts['active'] }}</div>
            </button>
            <button wire:click="$set('statusFilter', 'trialing')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <div class="text-xs text-gray-500 dark:text-gray-400">En Prueba</div>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $statusCounts['trial'] }}</div>
            </button>
            <button wire:click="$set('statusFilter', 'past_due')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <div class="text-xs text-gray-500 dark:text-gray-400">Vencidas</div>
                <div class="text-xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $statusCounts['past_due'] }}</div>
            </button>
            <button wire:click="$set('statusFilter', 'canceled')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <div class="text-xs text-gray-500 dark:text-gray-400">Canceladas</div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $statusCounts['canceled'] }}</div>
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Buscar
                    </label>
                    <input wire:model.live.debounce.300ms="search" 
                        id="search"
                        type="text" 
                        placeholder="Nombre del tenant..." 
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Estado
                    </label>
                    <div class="flex gap-2">
                        <select wire:model.live="statusFilter"
                            id="statusFilter"
                            class="flex-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                            <option value="">Todos los estados</option>
                            <option value="active">Activas</option>
                            <option value="trialing">Prueba</option>
                            <option value="past_due">Vencidas</option>
                            <option value="canceled">Canceladas</option>
                        </select>
                        @if($statusFilter)
                            <button wire:click="$set('statusFilter', '')" class="px-3 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Limpiar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tenant
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Precio
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Vencimiento
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $subscription->tenant->name }}
                                </p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $subscription->plan->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                ${{ number_format($subscription->plan->monthly_price_cents / 100, 2) }}/mes
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                @if($subscription->ends_at)
                                    {{ $subscription->ends_at->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'trialing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        'past_due' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                                        'canceled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                    $colorClass = $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('thunder-pack.sa.subscriptions.show', $subscription) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    Ver
                                </a>
                                <button wire:click="quickRenew({{ $subscription->id }}, 30)"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                    Renovar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron suscripciones
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
</div>
