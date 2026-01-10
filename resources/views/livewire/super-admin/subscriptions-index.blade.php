<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Suscripciones</h2>
            <a href="{{ route('sa.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">← Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <button wire:click="$set('statusFilter', 'active')" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Activas</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $statusCounts['active'] }}</div>
                </button>
                <button wire:click="$set('statusFilter', 'trialing')" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="text-xs text-gray-500 dark:text-gray-400">En Prueba</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $statusCounts['trial'] }}</div>
                </button>
                <button wire:click="$set('statusFilter', 'past_due')" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Vencidas</div>
                    <div class="text-xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $statusCounts['past_due'] }}</div>
                </button>
                <button wire:click="$set('statusFilter', 'canceled')" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Canceladas</div>
                    <div class="text-xl font-bold text-red-600 mt-1">{{ $statusCounts['canceled'] }}</div>
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-4 p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por tenant..." 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="flex gap-2">
                        <select wire:model.live="statusFilter" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos los estados</option>
                            <option value="active">Activas</option>
                            <option value="trialing">Prueba</option>
                            <option value="past_due">Vencidas</option>
                            <option value="canceled">Canceladas</option>
                        </select>
                        @if($statusFilter)
                            <button wire:click="$set('statusFilter', '')" class="px-3 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Limpiar</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if(session('message'))
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-md mb-4 text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Subscriptions List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($subscriptions as $subscription)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <a href="{{ route('sa.subscriptions.show', $subscription) }}" class="font-medium text-gray-900 dark:text-gray-100 text-sm hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $subscription->tenant->name }}
                                    </a>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $subscription->plan->name }}</span>
                                        <span>•</span>
                                        <span>${{ number_format($subscription->plan->monthly_price_cents / 100, 2) }}/mes</span>
                                        @if($subscription->ends_at)
                                            <span>•</span>
                                            <span>Vence: {{ $subscription->ends_at->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($subscription->status === 'active') bg-green-100 text-green-800
                                        @elseif($subscription->status === 'trialing') bg-blue-100 text-blue-800
                                        @elseif($subscription->status === 'past_due') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                    <button wire:click="quickRenew({{ $subscription->id }}, 30)" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                        Renovar 30d
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron suscripciones</div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
</div>
