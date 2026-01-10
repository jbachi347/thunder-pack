<div class="space-y-3">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Suscripciones</h3>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @forelse($tenant->subscriptions as $subscription)
            <a href="{{ route('thunder-pack.sa.subscriptions.show', $subscription) }}" 
               class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $subscription->plan->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 space-y-1">
                            @if($subscription->trial_ends_at && $subscription->status === 'trialing')
                                <div>Prueba termina: {{ $subscription->trial_ends_at->format('d/m/Y') }}</div>
                            @endif
                            @if($subscription->ends_at)
                                <div>Vence: {{ $subscription->ends_at->format('d/m/Y H:i') }}</div>
                            @endif
                            <div>Proveedor: {{ ucfirst($subscription->provider) }}</div>
                        </div>
                    </div>
                    <div class="ml-4 flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($subscription->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($subscription->status === 'trialing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($subscription->status === 'past_due') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @endif">
                            {{ ucfirst($subscription->status) }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="mt-2">Sin suscripciones registradas</p>
            </div>
        @endforelse
    </div>
</div>
