<?php

namespace ThunderPack\Livewire\Team;

use ThunderPack\Models\Tenant;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public Tenant $tenant;
    public bool $showInviteModal = false;

    public function mount()
    {
        $this->tenant = Tenant::findOrFail(session('current_tenant_id'));
        
        // Check if user can manage team
        if (!Auth::user()->canManageTeamInTenant($this->tenant->id)) {
            abort(403, 'No tienes permiso para gestionar el equipo.');
        }
    }

    public function openInviteModal()
    {
        if (!$this->tenant->canAddStaffMember()) {
            session()->flash('error', 'Has alcanzado el límite de miembros de tu plan. Actualiza tu plan para agregar más miembros.');
            return;
        }

        $this->showInviteModal = true;
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
    }

    public function removeTeamMember($userId)
    {
        $user = User::findOrFail($userId);
        
        // Prevent removing owner
        if ($user->isOwnerOfTenant($this->tenant->id)) {
            session()->flash('error', 'No puedes eliminar al propietario de la organización.');
            return;
        }

        // Check permissions
        if (!Auth::user()->canManageTeamInTenant($this->tenant->id)) {
            session()->flash('error', 'No tienes permiso para eliminar miembros.');
            return;
        }

        $this->tenant->users()->detach($userId);
        
        session()->flash('success', 'Miembro eliminado del equipo correctamente.');
    }

    public function render()
    {
        $teamMembers = $this->tenant->allTeamMembers()->paginate(10);
        $pendingInvitations = $this->tenant->teamInvitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('thunder-pack::livewire.team.index', [
            'teamMembers' => $teamMembers,
            'pendingInvitations' => $pendingInvitations,
            'staffUsagePercent' => $this->tenant->getStaffUsagePercentage(),
            'currentStaffCount' => $this->tenant->getCurrentStaffCount(),
            'staffLimit' => $this->tenant->getStaffLimit(),
            'remainingSlots' => $this->tenant->getRemainingStaffSlots(),
        ])->layout('layouts.app-sidebar');
    }
}
