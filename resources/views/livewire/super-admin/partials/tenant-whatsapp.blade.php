<div class="space-y-6">
    <!-- Configuration Status -->
    @if(!$whatsappConfigured)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Servicio WhatsApp no configurado</h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Configura las credenciales de Evolution API en el archivo .env para habilitar las notificaciones por WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    @if(!empty($whatsappStats))
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Mensajes</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $whatsappStats['total'] }}</dd>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <dt class="text-sm font-medium text-green-600 dark:text-green-400">Enviados</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900 dark:text-green-100">{{ $whatsappStats['sent'] }}</dd>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <dt class="text-sm font-medium text-red-600 dark:text-red-400">Fallidos</dt>
                <dd class="mt-1 text-2xl font-semibold text-red-900 dark:text-red-100">{{ $whatsappStats['failed'] }}</dd>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <dt class="text-sm font-medium text-blue-600 dark:text-blue-400">Tasa Éxito</dt>
                <dd class="mt-1 text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ $whatsappStats['success_rate'] }}%</dd>
            </div>
        </div>
    @endif

    <!-- Phone Numbers -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Teléfonos Configurados</h3>
            <div class="flex space-x-2">
                <button wire:click="openTestForm" 
                        @if($tenant->whatsappPhones->where('is_active', true)->isEmpty()) disabled @endif
                        class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    📤 Enviar Prueba
                </button>
                <button wire:click="addPhone" 
                        class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    + Agregar Teléfono
                </button>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($tenant->whatsappPhones as $phone)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $phone->display_phone }}</span>
                                
                                @if($phone->is_default)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Predeterminado
                                    </span>
                                @endif
                                
                                @if($phone->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                            
                            @if($phone->instance_name)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Instancia: <span class="font-mono">{{ $phone->instance_name }}</span>
                                </div>
                            @endif
                            
                            @if(!empty($phone->notification_types))
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Notificaciones: 
                                    @foreach($phone->notification_types as $type)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 mr-1">
                                            {{ $notificationTypes[$type] ?? $type }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Notificaciones: Todas habilitadas
                                </div>
                            @endif
                            
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                Agregado: {{ $phone->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        
                        <div class="ml-4 flex items-center space-x-2">
                            <button wire:click="openTestForm({{ $phone->id }})" 
                                    @if(!$phone->is_active || !$whatsappConfigured) disabled @endif
                                    class="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Enviar mensaje de prueba">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                            
                            @if(!$phone->is_default)
                                <button wire:click="setAsDefault({{ $phone->id }})" 
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded"
                                        title="Marcar como predeterminado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </button>
                            @endif
                            
                            <button wire:click="togglePhoneActive({{ $phone->id }})" 
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded"
                                    title="{{ $phone->is_active ? 'Desactivar' : 'Activar' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($phone->is_active)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    @endif
                                </svg>
                            </button>
                            
                            <button wire:click="editPhone({{ $phone->id }})" 
                                    class="p-1.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                                    title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            
                            <button wire:click="deletePhone({{ $phone->id }})" 
                                    wire:confirm="¿Estás seguro de eliminar este teléfono?"
                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                    title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Sin teléfonos configurados</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza agregando un número de WhatsApp para recibir notificaciones.</p>
                    <div class="mt-4">
                        <button wire:click="addPhone" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                            + Agregar Teléfono
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Message Logs -->
    @if($whatsappLogs->isNotEmpty())
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Historial de Mensajes (Últimos 20)</h3>
            
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mensaje</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($whatsappLogs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $log->phone_number }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($log->notification_type)
                                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                {{ $notificationTypes[$log->notification_type] ?? $log->notification_type }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-md truncate">
                                        {{ Str::limit($log->message, 60) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($log->status === 'sent') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($log->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ $log->status_badge_text }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
