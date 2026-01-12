<div x-data="{ showTooltip: false }" class="inline-flex relative">
    <span 
        @mouseenter="showTooltip = true" 
        @mouseleave="showTooltip = false"
        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
            @if($color === 'green') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
            @elseif($color === 'blue') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
            @elseif($color === 'yellow') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
            @elseif($color === 'orange') bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300
            @elseif($color === 'red') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
            @else bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300
            @endif
        ">
        <!-- Status Icon -->
        @if($status === 'trial')
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
        @elseif($status === 'active')
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        @elseif($status === 'past_due')
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        @elseif($status === 'canceled' || $status === 'suspended')
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        @endif
        {{ $label }}
    </span>

    <!-- Tooltip -->
    <div 
        x-show="showTooltip"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded shadow-lg whitespace-nowrap bottom-full left-1/2 transform -translate-x-1/2 mb-2"
        style="display: none;"
    >
        {{ $tooltipText }}
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
            <div class="border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
        </div>
    </div>
</div>
