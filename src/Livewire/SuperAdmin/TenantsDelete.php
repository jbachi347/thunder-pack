<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;

class TenantsDelete extends Component
{
    public Tenant $tenant;
    public bool $confirmingDeletion = false;
    public string $confirmationInput = '';

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant->load(['users', 'subscriptions']);
    }

    public function confirmDeletion()
    {
        $this->confirmingDeletion = true;
    }

    public function cancelDeletion()
    {
        $this->confirmingDeletion = false;
        $this->confirmationInput = '';
    }

    public function delete()
    {
        $this->validate([
            'confirmationInput' => 'required|in:' . $this->tenant->slug,
        ], [
            'confirmationInput.in' => 'El slug no coincide. Debe escribir: ' . $this->tenant->slug,
        ]);

        // Check if tenant has active subscription
        $activeSubscription = $this->tenant->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if ($activeSubscription) {
            session()->flash('error', 'No se puede eliminar un tenant con suscripción activa. Cancela la suscripción primero.');
            return;
        }

        $name = $this->tenant->name;

        // Detach all users
        $this->tenant->users()->detach();

        // Delete tenant (cascades subscriptions and other related records)
        $this->tenant->delete();

        session()->flash('message', "Tenant '{$name}' eliminado exitosamente.");

        return redirect()->route('thunder-pack.sa.tenants.index');
    }

    public function render()
    {
        return view('thunder-pack::livewire.super-admin.tenants-delete')
            ->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
