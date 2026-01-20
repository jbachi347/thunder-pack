<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Suscripción #{{ $subscription->id }}</h2>
            <a href="{{ route('thunder-pack.sa.subscriptions.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">← Volver a Suscripciones</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- Messages -->
            @if(session('message'))
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $subscription->tenant->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $subscription->tenant->slug }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        @if($subscription->status === 'active') bg-green-100 text-green-800
                        @elseif($subscription->status === 'trialing') bg-blue-100 text-blue-800
                        @elseif($subscription->status === 'past_due') bg-orange-100 text-orange-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                        <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $subscription->plan->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Precio</dt>
                        <dd class="text-gray-900 dark:text-gray-100 font-medium">${{ number_format($subscription->plan->monthly_price_cents / 100, 2) }}/mes</dd>
                    </div>
                    @if($subscription->trial_ends_at)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Fin de Prueba</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $subscription->trial_ends_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($subscription->ends_at)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Fecha de Vencimiento</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $subscription->ends_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($subscription->next_billing_date)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Próxima Facturación</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $subscription->next_billing_date->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Proveedor</dt>
                        <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ ucfirst($subscription->provider) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Acciones Rápidas</h3>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="renewManual(30)" class="inline-flex items-center px-3 py-1.5 bg-green-600 dark:bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-500 focus:bg-green-700 dark:focus:bg-green-500 active:bg-green-900 dark:active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Renovar 30 días
                    </button>
                    <button wire:click="renewManual(60)" class="inline-flex items-center px-3 py-1.5 bg-green-600 dark:bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-500 focus:bg-green-700 dark:focus:bg-green-500 active:bg-green-900 dark:active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Renovar 60 días
                    </button>
                    <button wire:click="activateManual" class="inline-flex items-center px-3 py-1.5 bg-blue-600 dark:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-500 focus:bg-blue-700 dark:focus:bg-blue-500 active:bg-blue-900 dark:active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Activar
                    </button>
                    <button wire:click="setPastDue" class="inline-flex items-center px-3 py-1.5 bg-orange-600 dark:bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-orange-700 dark:hover:bg-orange-500 focus:bg-orange-700 dark:focus:bg-orange-500 active:bg-orange-900 dark:active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Marcar Vencida
                    </button>
                    <button wire:click="cancel" class="inline-flex items-center px-3 py-1.5 bg-red-600 dark:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-500 focus:bg-red-700 dark:focus:bg-red-500 active:bg-red-900 dark:active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Establecer Fecha Manual -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Establecer Fecha de Renovación Manual</h3>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label for="manualNextBillingDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Próxima Renovación
                        </label>
                        <input type="datetime-local" 
                            id="manualNextBillingDate"
                            wire:model="manualNextBillingDate" 
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                        @error('manualNextBillingDate') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                    <button wire:click="setNextBillingDate" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Establecer Fecha
                    </button>
                </div>
            </div>

            <!-- Registrar Pago Manual -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Registrar Pago Manual</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label for="paymentAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Monto
                        </label>
                        <input type="number" 
                            step="0.01"
                            id="paymentAmount"
                            wire:model="paymentAmount" 
                            placeholder="0.00"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                        @error('paymentAmount') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div>
                        <label for="paymentCurrency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Moneda
                        </label>
                        <select id="paymentCurrency"
                            wire:model="paymentCurrency" 
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="DOP">DOP</option>
                        </select>
                    </div>
                    <div>
                        <label for="paymentNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas (opcional)
                        </label>
                        <input type="text" 
                            id="paymentNotes"
                            wire:model="paymentNotes" 
                            placeholder="Transferencia bancaria..."
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                    </div>
                </div>
                <div class="mt-3">
                    <button wire:click="recordPayment" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Registrar Pago
                    </button>
                </div>
            </div>

            <!-- Payment Events -->
            @if($paymentEvents->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Historial de Pagos</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($paymentEvents as $event)
                            <div class="p-4 text-sm">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $event->event_type }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $event->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="text-right">
                                        @if($event->amount_cents)
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                ${{ number_format($event->amount_cents / 100, 2) }} {{ $event->currency }}
                                            </div>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                                            @if($event->status === 'success') bg-green-100 text-green-800
                                            @elseif($event->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
