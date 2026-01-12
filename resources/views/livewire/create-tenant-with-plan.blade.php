<div>
    <!-- Modal backdrop -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="createTenant">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Crear Nuevo Tenant
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Nombre del Tenant -->
                                    <div>
                                        <label for="tenant-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nombre del Tenant
                                        </label>
                                        <input 
                                            type="text" 
                                            id="tenant-name"
                                            wire:model="name" 
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2"
                                            placeholder="Mi Empresa">
                                        @error('name') 
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Selección de Plan -->
                                    <div>
                                        <label for="plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Selecciona un Plan
                                        </label>
                                        <select 
                                            id="plan"
                                            wire:model="plan_id" 
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
                                            <option value="">Seleccionar plan...</option>
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">
                                                    {{ $plan->name }} - ${{ number_format($plan->monthly_price, 2) }}/mes
                                                    @if($plan->staff_limit)
                                                        ({{ $plan->staff_limit }} usuarios)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plan_id') 
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <!-- Información del Trial -->
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                                        <p class="text-xs text-blue-800 dark:text-blue-300">
                                            <strong>Trial gratuito de 7 días</strong><br>
                                            Tu tenant se creará con acceso completo por 7 días sin cargo.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 w-full sm:w-auto justify-center">
                            Crear Tenant
                        </button>
                        <button 
                            type="button" 
                            wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150 w-full sm:w-auto justify-center mt-2 sm:mt-0">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
