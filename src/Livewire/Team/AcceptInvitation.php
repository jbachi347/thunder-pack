<?php

namespace ThunderPack\Livewire\Team;

use ThunderPack\Models\TeamInvitation;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AcceptInvitation extends Component
{
    public string $token;
    public ?TeamInvitation $invitation = null;
    public ?string $error = null;
    public bool $accepted = false;

    public function mount($token)
    {
        $this->token = $token;
        
        // Find invitation
        $this->invitation = TeamInvitation::where('token', $token)->first();

        // Validate invitation
        if (!$this->invitation) {
            $this->error = 'Invitación no encontrada o inválida.';
            return;
        }

        if (!$this->invitation->isValid()) {
            if ($this->invitation->isExpired()) {
                $this->error = 'Esta invitación ha expirado.';
            } elseif ($this->invitation->isAccepted()) {
                $this->error = 'Esta invitación ya fue aceptada.';
            } else {
                $this->error = 'Invitación inválida.';
            }
        }
    }

    public function acceptInvitation()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Inicia sesión para aceptar la invitación.');
        }

        if (!$this->invitation || !$this->invitation->isValid()) {
            session()->flash('error', $this->error ?? 'Invitación inválida.');
            return;
        }

        $user = Auth::user();

        // Check if user email matches invitation
        if ($user->email !== $this->invitation->email) {
            session()->flash('error', 'Esta invitación no está dirigida a tu correo electrónico.');
            return;
        }

        // Try to accept invitation
        $success = $this->invitation->accept($user);

        if ($success) {
            $this->accepted = true;
            
            // Set current tenant
            session(['current_tenant_id' => $this->invitation->tenant_id]);
            
            session()->flash('success', '¡Te has unido al equipo exitosamente!');
            
            // Redirect to dashboard after a short delay
            $this->dispatch('invitation-accepted');
            return redirect()->route('dashboard');
        } else {
            // Failed - probably limit reached
            if (!$this->invitation->tenant->canAddStaffMember()) {
                session()->flash('error', 'El equipo ha alcanzado su límite de miembros. Contacta al administrador.');
            } else {
                session()->flash('error', 'No se pudo aceptar la invitación. Por favor intenta nuevamente.');
            }
        }
    }

    public function render()
    {
        return view('thunder-pack::livewire.team.accept-invitation')
            ->layout('layouts.guest-centered');
    }
}
