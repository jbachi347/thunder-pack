<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use ThunderPack\Models\Tenant;

class UsersShow extends Component
{
    public $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_super_admin = false;
    public bool $editingBasicInfo = false;
    public bool $editingPassword = false;

    // Tenant assignment
    public $selectedTenantId = '';
    public $selectedRole = 'member';
    public bool $showAddTenantForm = false;

    public function mount($user)
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        $this->user = $userClass::with(['tenants'])->findOrFail($user);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->is_super_admin = (bool) $this->user->is_super_admin;
    }

    public function toggleEditBasicInfo()
    {
        $this->editingBasicInfo = !$this->editingBasicInfo;
        if (!$this->editingBasicInfo) {
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->is_super_admin = (bool) $this->user->is_super_admin;
        }
    }

    public function saveBasicInfo()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'is_super_admin' => 'boolean',
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'is_super_admin' => $this->is_super_admin,
        ]);

        $this->editingBasicInfo = false;
        session()->flash('message', 'Información actualizada exitosamente');
    }

    public function toggleEditPassword()
    {
        $this->editingPassword = !$this->editingPassword;
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function savePassword()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->editingPassword = false;
        $this->password = '';
        $this->password_confirmation = '';
        session()->flash('message', 'Contraseña actualizada exitosamente');
    }

    public function toggleAddTenantForm()
    {
        $this->showAddTenantForm = !$this->showAddTenantForm;
        $this->selectedTenantId = '';
        $this->selectedRole = 'member';
    }

    public function addTenant()
    {
        $this->validate([
            'selectedTenantId' => 'required|exists:tenants,id',
            'selectedRole' => 'required|in:owner,admin,member',
        ]);

        // Check if user already has this tenant
        if ($this->user->tenants()->where('tenant_id', $this->selectedTenantId)->exists()) {
            session()->flash('error', 'El usuario ya está asignado a este tenant');
            return;
        }

        $this->user->tenants()->attach($this->selectedTenantId, [
            'role' => $this->selectedRole,
            'is_owner' => $this->selectedRole === 'owner',
        ]);

        $this->user->load('tenants');
        $this->showAddTenantForm = false;
        $this->selectedTenantId = '';
        $this->selectedRole = 'member';
        session()->flash('message', 'Tenant asignado exitosamente');
    }

    public function removeTenant($tenantId)
    {
        $this->user->tenants()->detach($tenantId);
        $this->user->load('tenants');
        session()->flash('message', 'Tenant removido exitosamente');
    }

    public function render()
    {
        $availableTenants = Tenant::whereNotIn('id', $this->user->tenants->pluck('id'))->get();

        return view('thunder-pack::livewire.super-admin.users-show', [
            'availableTenants' => $availableTenants,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
