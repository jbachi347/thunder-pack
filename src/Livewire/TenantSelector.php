<?php

namespace ThunderPack\Livewire;

use ThunderPack\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantSelector extends Component
{
    public $tenants;
    public $pendingInvitations;

    protected $listeners = ['tenant-created' => 'loadData'];

    public function mount()
    {
        $this->loadData();
        
        // Si es super-admin, redirigir al panel SA
        if (Auth::user()->is_super_admin) {
            return $this->redirect(route('thunder-pack.sa.dashboard'));
        }
        
        // Si no tiene tenants, mostrar mensaje
        if ($this->tenants->isEmpty()) {
            session()->flash('warning', 'No tienes acceso a ningún tenant. Contacta al administrador.');
        }
    }

    public function loadData()
    {
        $this->tenants = Auth::user()->tenants;
        
        // Obtener IDs de tenants a los que ya pertenece
        $userTenantIds = $this->tenants->pluck('id')->toArray();
        
        // Cargar invitaciones pendientes para el usuario
        // Excluir invitaciones de tenants a los que ya pertenece
        $this->pendingInvitations = TeamInvitation::where('email', Auth::user()->email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->whereNotIn('tenant_id', $userTenantIds)
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectTenant($tenantId)
    {
        try {
            \Log::info('TenantSelector::selectTenant called', ['tenant_id' => $tenantId, 'user_id' => Auth::id()]);
            
            $tenant = Auth::user()->tenants()->find($tenantId);

            if ($tenant) {
                \Log::info('Tenant found, setting current tenant', ['tenant' => $tenant->toArray()]);
                Auth::user()->setCurrentTenant($tenant);
                
                \Log::info('Session after setting tenant', ['current_tenant_id' => session('current_tenant_id')]);
                
                session()->flash('message', 'Tenant seleccionado correctamente: ' . $tenant->name);
                return $this->redirect(route('dashboard'));
            } else {
                \Log::warning('Tenant not found or no access', ['tenant_id' => $tenantId, 'user_tenants' => Auth::user()->tenants->pluck('id')->toArray()]);
                session()->flash('error', 'Tenant no encontrado o sin acceso.');
            }
        } catch (\Exception $e) {
            \Log::error('Error in TenantSelector::selectTenant', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Error al seleccionar tenant: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return $this->redirect('/');
    }

    public function render()
    {
        return view('thunder-pack::livewire.tenant-selector')
            ->layout('layouts.guest-centered');
    }
}
