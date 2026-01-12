<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('thunder-pack.sa.tenants.show', $tenant) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestión de Límites
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $tenant->name }} • Plan: {{ $plan?->name ?? 'Sin plan' }}
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

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Limits Table (2/3 width) -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Límites Actuales
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Límite
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Uso Actual
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Override
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    %
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($limits as $key => $limit)
                                @php
                                    $effectiveLimit = $limit['override'] ? $limit['override']->getParsedValue() : $limit['plan_value'];
                                    $currentUsage = is_numeric($limit['current_usage']) ? $limit['current_usage'] : 0;
                                    $effectiveLimit = is_numeric($effectiveLimit) ? $effectiveLimit : 0;
                                    $percentage = $effectiveLimit > 0 ? ($currentUsage / $effectiveLimit * 100) : 0;
                                    $isUnlimited = $limit['override'] && $limit['override']->getParsedValue() === null;
                                    
                                    // Handle plan_value that might be an array
                                    $planValue = $limit['plan_value'] ?? '-';
                                    if (is_array($planValue)) {
                                        $planValue = json_encode($planValue);
                                    }
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $limit['name'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $key }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $planValue }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $currentUsage }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($limit['override'])
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isUnlimited ? 'bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-200' : 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200' }}">
                                                    {{ $isUnlimited ? '∞ Ilimitado' : $limit['override']->getParsedValue() }}
                                                </span>
                                            </div>
                                            @if($limit['override']->notes)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1" title="{{ $limit['override']->notes }}">
                                                    {{ Str::limit($limit['override']->notes, 30) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!$isUnlimited)
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                                    <div 
                                                        class="h-2 rounded-full {{ $percentage >= 100 ? 'bg-red-600' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-green-600') }}"
                                                        style="width: {{ min($percentage, 100) }}%"
                                                    ></div>
                                                </div>
                                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ number_format($percentage, 0) }}%
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-600">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($limit['override'])
                                            <button 
                                                wire:click="removeOverride({{ $limit['override']->id }})"
                                                wire:confirm="¿Eliminar este override?"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Eliminar
                                            </button>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay límites definidos para este tenant
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Override Form (1/3 width) -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Agregar Override
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Personaliza los límites para este tenant
                    </p>
                </div>

                <form wire:submit.prevent="addOverride" class="p-6 space-y-4">
                    <!-- Limit Type Select -->
                    <div>
                        <label for="limitKey" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo de Límite
                        </label>
                        <select 
                            id="limitKey"
                            wire:model="limitKey"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            required
                        >
                            <option value="">Seleccionar...</option>
                            @php
                                $currentCategory = null;
                            @endphp
                            @foreach($availableLimits as $limit)
                                @if($currentCategory !== $limit->category)
                                    @if($currentCategory !== null)
                                        </optgroup>
                                    @endif
                                    <optgroup label="{{ ucfirst($limit->category) }}">
                                    @php $currentCategory = $limit->category; @endphp
                                @endif
                                <option value="{{ $limit->key }}" title="{{ $limit->description }}">
                                    {{ $limit->display_name }}
                                </option>
                            @endforeach
                            @if($currentCategory !== null)
                                </optgroup>
                            @endif
                        </select>
                        @error('limitKey')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unlimited Checkbox -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="isUnlimited"
                            wire:model.live="isUnlimited"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                        >
                        <label for="isUnlimited" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Ilimitado
                        </label>
                    </div>

                    <!-- Limit Value Input -->
                    @if(!$isUnlimited)
                        <div>
                            <label for="limitValue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Valor del Límite
                            </label>
                            <input 
                                type="number" 
                                id="limitValue"
                                wire:model="limitValue"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Ej: 100"
                                required
                            >
                            @error('limitValue')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Notes Textarea -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas (Opcional)
                        </label>
                        <textarea 
                            id="notes"
                            wire:model="notes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="Cliente VIP, contrato especial, etc."
                        ></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Message -->
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                        <p class="text-xs text-blue-800 dark:text-blue-200">
                            ℹ️ El override reemplazará el valor del plan para este tenant específicamente.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition disabled:opacity-50"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Agregar Override</span>
                        <span wire:loading>Agregando...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
