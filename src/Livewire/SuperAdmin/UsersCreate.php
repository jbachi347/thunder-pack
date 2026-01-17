<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use ThunderPack\Models\Tenant;

class UsersCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_super_admin = false;
    
    // Tenant assignment
    public array $selectedTenants = [];
    public string $defaultRole = 'member';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_super_admin' => 'boolean',
            'selectedTenants' => 'array',
            'selectedTenants.*' => 'exists:tenants,id',
            'defaultRole' => 'required|in:owner,admin,member',
        ]);

        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        
        $user = $userClass::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_super_admin' => $validated['is_super_admin'],
        ]);

        // Attach selected tenants
        if (!empty($validated['selectedTenants'])) {
            foreach ($validated['selectedTenants'] as $tenantId) {
                $user->tenants()->attach($tenantId, [
                    'role' => $validated['defaultRole'],
                    'is_owner' => $validated['defaultRole'] === 'owner',
                ]);
            }
        }

        session()->flash('message', 'Usuario creado exitosamente');
        
        return redirect()->route('thunder-pack.sa.users.show', $user);
    }

    public function render()
    {
        $tenants = Tenant::orderBy('name')->get();

        return view('thunder-pack::livewire.super-admin.users-create', [
            'tenants' => $tenants,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
