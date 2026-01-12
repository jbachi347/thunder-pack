<?php

namespace ThunderPack\Livewire;

use ThunderPack\Models\Tenant;
use Livewire\Component;

class SubscriptionStatusBadge extends Component
{
    public $tenant;
    public $subscription;
    public $status;
    public $label;
    public $color;
    public $tooltipText;

    public function mount(?Tenant $tenant = null)
    {
        $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
        $this->tenant = $tenant ?? $tenantClass::find(session('current_tenant_id'));
        
        if ($this->tenant) {
            $this->subscription = $this->tenant->latestSubscription;
            $this->determineStatus();
        }
    }

    protected function determineStatus()
    {
        if (!$this->subscription) {
            $this->status = 'no_subscription';
            $this->label = 'Sin suscripción';
            $this->color = 'gray';
            $this->tooltipText = 'No hay suscripción activa';
            return;
        }

        // Trial activo
        if ($this->subscription->trial_ends_at && $this->subscription->trial_ends_at->isFuture()) {
            $this->status = 'trial';
            $this->label = 'Período de prueba';
            $this->color = 'blue';
            $this->tooltipText = 'Trial hasta ' . $this->subscription->trial_ends_at->format('d/m/Y');
            return;
        }

        // Estados basados en status
        switch ($this->subscription->status) {
            case 'active':
                $this->status = 'active';
                $this->label = 'Activa';
                $this->color = 'green';
                $this->tooltipText = $this->subscription->ends_at 
                    ? 'Renovación: ' . $this->subscription->ends_at->format('d/m/Y')
                    : 'Suscripción activa';
                break;

            case 'past_due':
                $this->status = 'past_due';
                $this->label = 'Vencida';
                $this->color = 'yellow';
                $this->tooltipText = 'Venció el ' . $this->subscription->ends_at->format('d/m/Y');
                break;

            case 'canceled':
                $this->status = 'canceled';
                $this->label = 'Cancelada';
                $this->color = 'red';
                $this->tooltipText = 'Suscripción cancelada';
                break;

            case 'suspended':
                $this->status = 'suspended';
                $this->label = 'Suspendida';
                $this->color = 'orange';
                $this->tooltipText = 'Acceso suspendido';
                break;

            default:
                $this->status = 'unknown';
                $this->label = 'Estado desconocido';
                $this->color = 'gray';
                $this->tooltipText = 'Estado: ' . $this->subscription->status;
        }
    }

    public function render()
    {
        return view('thunder-pack::livewire.subscription-status-badge');
    }
}
