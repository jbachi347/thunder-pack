<?php

namespace ThunderPack\Livewire\Team;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\TeamInvitation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Invite extends Component
{
    public Tenant $tenant;
    public string $email = '';
    public string $role = 'staff';

    protected $rules = [
        'email' => 'required|email',
        'role' => 'required|in:staff,admin,owner',
    ];

    protected $messages = [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Ingresa un correo electrónico válido.',
        'role.required' => 'El rol es obligatorio.',
        'role.in' => 'Rol inválido.',
    ];

    public function mount()
    {
        $this->tenant = Tenant::findOrFail(session('current_tenant_id'));
    }

    public function sendInvitation()
    {
        // Check permissions
        if (!Auth::user()->canManageTeamInTenant($this->tenant->id)) {
            session()->flash('error', 'No tienes permiso para invitar miembros.');
            return;
        }

        $this->validate();

        try {
            // This method includes limit checking and throws exception if limit reached
            $invitation = $this->tenant->inviteUser(
                $this->email,
                $this->role,
                Auth::id()
            );

            // Send invitation email
            $invitation->sendInvitation();

            session()->flash('success', "Invitación enviada a {$this->email} correctamente.");
            
            // Reset form
            $this->reset(['email', 'role']);
            $this->role = 'staff';
            
            // Dispatch event to parent component ONLY on success
            $this->dispatch('invitation-sent');
            
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            // Don't dispatch event on error, keep modal open
        }
    }

    public function render()
    {
        return view('thunder-pack::livewire.team.invite');
    }
}
