<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Elige tu plan
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">
                Selecciona el plan que mejor se adapte a tus necesidades
            </p>
        </div>

        <!-- Billing Cycle Toggle -->
        <div class="flex justify-center mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-1 border border-gray-200 dark:border-gray-700">
                <button 
                    wire:click="$set('billingCycle', 'monthly')"
                    class="px-6 py-2 rounded-md text-sm font-medium transition-colors {{ $billingCycle === 'monthly' ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Mensual
                </button>
                <button 
                    wire:click="$set('billingCycle', 'yearly')"
                    class="px-6 py-2 rounded-md text-sm font-medium transition-colors {{ $billingCycle === 'yearly' ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Anual
                    <span class="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded">Ahorra 20%</span>
                </button>
            </div>
        </div>

        <!-- Error Message -->
        @if (session()->has('error'))
            <div class="mb-6 max-w-3xl mx-auto">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            @foreach($plans as $plan)
                <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6 flex flex-col {{ $selectedPlan === $plan->id ? 'ring-2 ring-indigo-500' : '' }}">
                    <!-- Plan Header -->
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $plan->name }}
                        </h3>
                        
                        <!-- Price -->
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">
                                @if($billingCycle === 'yearly' && $plan->yearly_price_cents)
                                    ${{ number_format($plan->yearly_price_cents / 100, 2) }}
                                @else
                                    ${{ number_format($plan->monthly_price_cents / 100, 2) }}
                                @endif
                            </span>
                            <span class="ml-2 text-gray-600 dark:text-gray-400">
                                / {{ $billingCycle === 'yearly' ? 'año' : 'mes' }}
                            </span>
                        </div>

                        @if($billingCycle === 'yearly' && $plan->yearly_price_cents)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                ${{ number_format(($plan->yearly_price_cents / 12) / 100, 2) }} por mes
                            </p>
                        @endif
                    </div>

                    <!-- Features -->
                    <div class="flex-grow mb-6">
                        <ul class="space-y-3">
                            @if($plan->features && is_array($plan->features))
                                @if(isset($plan->features['staff_limit']))
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $plan->features['staff_limit'] }} {{ $plan->features['staff_limit'] === 1 ? 'usuario' : 'usuarios' }}
                                        </span>
                                    </li>
                                @endif

                                @if(isset($plan->features['storage_quota_mb']))
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ number_format($plan->features['storage_quota_mb'] / 1024, 1) }} GB almacenamiento
                                        </span>
                                    </li>
                                @endif

                                @if(isset($plan->features['modules']) && is_array($plan->features['modules']))
                                    @foreach($plan->features['modules'] as $module)
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ ucfirst($module) }}
                                            </span>
                                        </li>
                                    @endforeach
                                @endif
                            @endif
                        </ul>
                    </div>

                    <!-- CTA Button -->
                    @if($plan->hasLemonSqueezyIntegration())
                        <button 
                            wire:click="checkout({{ $plan->id }})"
                            wire:loading.attr="disabled"
                            wire:target="checkout({{ $plan->id }})"
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150">
                            <span wire:loading.remove wire:target="checkout({{ $plan->id }})">
                                Suscribirse
                            </span>
                            <span wire:loading wire:target="checkout({{ $plan->id }})">
                                Procesando...
                            </span>
                        </button>
                    @else
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                            Contactar soporte
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Back Link -->
        <div class="text-center mt-8">
            <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                ← Volver al dashboard
            </a>
        </div>
    </div>
</div>
