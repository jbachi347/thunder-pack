<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tenants</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestiona las organizaciones del sistema
                </p>
            </div>
            <a href="{{ route('thunder-pack.sa.tenants.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Crear Tenant
            </a>
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
                        placeholder="Nombre o slug..." 
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Estado
                    </label>
                    <select wire:model.live="statusFilter"
                        id="statusFilter"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                        <option value="">Todos los estados</option>
                        <option value="active">Activos</option>
                        <option value="trialing">Prueba</option>
                        <option value="past_due">Vencidos</option>
                        <option value="canceled">Cancelados</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tenants Table -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Organización
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Usuarios
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Storage (Usado/Cuota)
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Suscripción
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Creado
                        </th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $tenant->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $tenant->slug }}
                                    </p>
                                    @if($tenant->brand_name)
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            🏷️ {{ $tenant->brand_name }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $tenant->users_count > 0 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                                    {{ $tenant->users_count }} {{ $tenant->users_count == 1 ? 'usuario' : 'usuarios' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                @php
                                    $usedGB = $tenant->storage_used_bytes / 1024 / 1024 / 1024;
                                    $quotaGB = $tenant->storage_quota_bytes / 1024 / 1024 / 1024;
                                    $percentage = $quotaGB > 0 ? ($usedGB / $quotaGB) * 100 : 0;
                                    $colorClass = $percentage > 90 ? 'text-red-600 dark:text-red-400' : ($percentage > 70 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-700 dark:text-gray-300');
                                @endphp
                                <div class="{{ $colorClass }}">
                                    <span class="font-medium">{{ number_format($usedGB, 2) }} GB</span>
                                    <span class="text-gray-400 dark:text-gray-500">/</span>
                                    <span>{{ number_format($quotaGB, 0) }} GB</span>
                                </div>
                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                                    <div class="h-1 rounded-full {{ $percentage > 90 ? 'bg-red-500' : ($percentage > 70 ? 'bg-yellow-500' : 'bg-blue-500') }}" 
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div>
                                    @livewire('thunder-pack::subscription-status-badge', ['tenant' => $tenant], key('sub-badge-'.$tenant->id))
                                    @if($tenant->subscriptions->first())
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $tenant->subscriptions->first()->plan->name }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <div>
                                    <p>{{ $tenant->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $tenant->created_at->diffForHumans() }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('thunder-pack.sa.tenants.show', $tenant) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    Ver
                                </a>
                                <a href="{{ route('thunder-pack.sa.tenants.edit', $tenant) }}"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron tenants
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tenants->links() }}
        </div>

    </div>
</div>
