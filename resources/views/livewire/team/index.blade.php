<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Equipo</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $currentStaffCount }} de {{ $staffLimit }} miembros
                </p>
            </div>
            
            @if($staffLimit > 1)
                <button 
                    wire:click="openInviteModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
                    @if($remainingSlots === 0) disabled @endif
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Invitar
                </button>
            @endif
        </div>

        <!-- Usage Stats Card -->
        @if($staffLimit > 1)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Uso del límite</span>
                    <span class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ number_format($staffUsagePercent, 0) }}%</span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div 
                        class="h-full rounded-full transition-all duration-500 {{ $staffUsagePercent >= 100 ? 'bg-red-600' : ($staffUsagePercent >= 80 ? 'bg-yellow-500' : 'bg-green-600') }}"
                        style="width: {{ min($staffUsagePercent, 100) }}%"
                    ></div>
                </div>
                
                <!-- Warning Messages -->
                @if($remainingSlots === 0)
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                        Límite alcanzado. <a href="#" class="underline font-medium">Actualizar plan</a>
                    </p>
                @elseif($staffUsagePercent >= 80)
                    <p class="mt-2 text-xs text-yellow-600 dark:text-yellow-400">
                        Te quedan {{ $remainingSlots }} {{ $remainingSlots === 1 ? 'espacio' : 'espacios' }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Pending Invitations -->
    @if($pendingInvitations->count() > 0)
        <div class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Invitaciones Pendientes</h3>
            <div class="space-y-2">
                @foreach($pendingInvitations as $invitation)
                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-900 dark:text-gray-100">{{ $invitation->email }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                                Pendiente
                            </span>
                        </div>
                        <button 
                            wire:click="cancelInvitation({{ $invitation->id }})"
                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Cancelar
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Team Members Table -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Miembros ({{ $currentStaffCount }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Rol
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Ingreso
                        </th>
                        @if(Auth::user()->canManageTeamInTenant($tenant->id))
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($teamMembers as $member)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-600 dark:text-gray-300 font-medium text-xs">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $member->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $member->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize
                                    {{ $member->pivot->role === 'owner' ? 'bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-200' : '' }}
                                    {{ $member->pivot->role === 'admin' ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200' : '' }}
                                    {{ $member->pivot->role === 'staff' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' : '' }}
                                ">
                                    {{ $member->pivot->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->pivot->created_at->format('d M Y') }}
                            </td>
                            @if(Auth::user()->canManageTeamInTenant($tenant->id))
                                <td class="px-4 py-3 whitespace-nowrap text-right text-xs font-medium">
                                    @if(!$member->isOwnerOfTenant($tenant->id))
                                        <button 
                                            wire:click="removeTeamMember({{ $member->id }})"
                                            wire:confirm="¿Eliminar a este miembro?"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Eliminar
                                        </button>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-600">Propietario</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay miembros en el equipo
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teamMembers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $teamMembers->links() }}
            </div>
        @endif
    </div>

    <!-- Invite Modal -->
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="invite-modal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div 
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                    wire:click="closeInviteModal"
                ></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <livewire:thunder-pack::team.invite :key="'invite-'.now()" />
                </div>
            </div>
        </div>
    @endif
</div>
