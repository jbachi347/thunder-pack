<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('thunder-pack.sa.plans.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Límites del Plan
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $plan->name }} • {{ $plan->code }}
                </p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Current Plan Limits -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Límites Configurados
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Límites activos para este plan
                </p>
            </div>
            
            <div class="p-6">
                @if(count($selectedLimits) > 0)
                    <div class="space-y-3">
                        @foreach($selectedLimits as $key => $value)
                            @php
                                $availableLimit = \ThunderPack\Models\AvailableLimit::where('key', $key)->first();
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $availableLimit ? $availableLimit->display_name : ucwords(str_replace('_', ' ', $key)) }}
                                    </div>
                                    @if($availableLimit && $availableLimit->description)
                                        <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                            {{ $availableLimit->description }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 ml-3">
                                    <input type="number" 
                                        wire:model="selectedLimits.{{ $key }}"
                                        min="0"
                                        class="w-24 text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-1 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded text-right px-2 py-1">
                                    @if($availableLimit && $availableLimit->unit)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $availableLimit->unit }}
                                        </span>
                                    @endif
                                    <button type="button" 
                                        wire:click="removeLimit('{{ $key }}')"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button type="button" 
                            wire:click="save"
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Guardar Límites
                        </button>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Sin límites configurados</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Agrega límites desde el catálogo disponible.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Available Limits Catalog -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Catálogo de Límites
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Límites disponibles para agregar a este plan
                </p>
            </div>
            
            <div class="p-6 max-h-96 overflow-y-auto">
                @if($availableLimits && $availableLimits->count() > 0)
                    @foreach($availableLimits as $category => $limits)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">
                                {{ ucfirst($category) }}
                            </h4>
                            <div class="space-y-2">
                                @foreach($limits as $limit)
                                    @if(!array_key_exists($limit->key, $selectedLimits))
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $limit->display_name }}
                                                </div>
                                                @if($limit->description)
                                                    <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                                        {{ $limit->description }}
                                                    </div>
                                                @endif
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Valor por defecto: {{ $limit->default_value }} {{ $limit->unit }}
                                                </div>
                                            </div>
                                            <button type="button" 
                                                wire:click="addLimit('{{ $limit->key }}')"
                                                class="ml-3 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay límites disponibles en el catálogo.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>