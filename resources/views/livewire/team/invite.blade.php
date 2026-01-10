<div>
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
            Invitar Miembro
        </h3>
    </div>

    <form wire:submit.prevent="sendInvitation">
        <div class="px-6 py-4 space-y-4">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Correo Electrónico
                </label>
                <input 
                    type="email" 
                    id="email"
                    wire:model="email"
                    class="w-full text-sm px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    placeholder="usuario@ejemplo.com"
                    required
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Select -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Rol
                </label>
                <select 
                    id="role"
                    wire:model="role"
                    class="w-full text-sm px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    required
                >
                    <option value="staff">Staff - Acceso básico</option>
                    <option value="admin">Admin - Gestión de equipo y recursos</option>
                    <option value="owner">Owner - Control total (incluyendo facturación)</option>
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Message -->
            <div class="text-xs text-gray-600 dark:text-gray-400">
                Se enviará un correo de invitación. Expira en 7 días.
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
            <button 
                type="button"
                wire:click="$dispatch('invitation-sent')"
                class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition"
            >
                Cancelar
            </button>
            <button 
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition disabled:opacity-50"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Enviar</span>
                <span wire:loading>Enviando...</span>
            </button>
        </div>
    </form>
</div>
