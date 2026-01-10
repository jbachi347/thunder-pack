<?php

namespace ThunderPack\Livewire\SuperAdmin;

use ThunderPack\Models\PaymentEvent;
use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Services\SubscriptionService;
use Livewire\Component;

class SubscriptionShow extends Component
{
    public Subscription $subscription;
    public $plans = [];
    
    // Para modal de cambio de plan
    public $selectedPlanId;
    
    // Para modal de pago manual
    public $paymentAmount;
    public $paymentCurrency = 'USD';
    public $paymentNotes = '';

    public function mount(Subscription $subscription)
    {
        $this->subscription = $subscription->load(['tenant', 'plan']);
        $this->plans = Plan::all();
        $this->selectedPlanId = $this->subscription->plan_id;
    }

    public function renewManual(int $days)
    {
        try {
            $service = app(SubscriptionService::class);
            $service->renewManual($this->subscription->tenant, $days);

            session()->flash('message', "Suscripción renovada por {$days} días correctamente.");
            
            // Recargar subscription
            $this->subscription = $this->subscription->fresh(['tenant', 'plan']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al renovar: ' . $e->getMessage());
        }
    }

    public function activateManual()
    {
        try {
            $service = app(SubscriptionService::class);
            $service->activateManual($this->subscription->tenant, $this->subscription->plan, 30);

            session()->flash('message', 'Suscripción activada correctamente.');
            
            $this->subscription = $this->subscription->fresh(['tenant', 'plan']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al activar: ' . $e->getMessage());
        }
    }

    public function setPastDue()
    {
        try {
            $service = app(SubscriptionService::class);
            $service->setPastDue($this->subscription->tenant);

            session()->flash('message', 'Suscripción marcada como vencida.');
            
            $this->subscription = $this->subscription->fresh(['tenant', 'plan']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al marcar como vencida: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        try {
            $service = app(SubscriptionService::class);
            $service->cancel($this->subscription->tenant);

            session()->flash('message', 'Suscripción cancelada correctamente.');
            
            $this->subscription = $this->subscription->fresh(['tenant', 'plan']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }

    public function changePlan()
    {
        $this->validate([
            'selectedPlanId' => 'required|exists:plans,id',
        ]);

        try {
            $newPlan = Plan::findOrFail($this->selectedPlanId);
            $service = app(SubscriptionService::class);
            $service->changePlan($this->subscription->tenant, $newPlan, true);

            session()->flash('message', 'Plan cambiado correctamente.');
            
            $this->subscription = $this->subscription->fresh(['tenant', 'plan']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar plan: ' . $e->getMessage());
        }
    }

    public function recordPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentCurrency' => 'required|string|max:3',
            'paymentNotes' => 'nullable|string|max:500',
        ], [
            'paymentAmount.required' => 'El monto es obligatorio',
            'paymentAmount.numeric' => 'El monto debe ser un número',
            'paymentAmount.min' => 'El monto debe ser mayor a 0',
            'paymentCurrency.required' => 'La moneda es obligatoria',
        ]);

        try {
            $service = app(SubscriptionService::class);
            $amountCents = (int)($this->paymentAmount * 100);
            
            $service->recordManualPayment(
                $this->subscription->tenant,
                $amountCents,
                $this->paymentCurrency,
                'manual.payment',
                [
                    'notes' => $this->paymentNotes,
                    'subscription_id' => $this->subscription->id,
                ]
            );

            session()->flash('message', 'Pago manual registrado correctamente.');
            
            // Resetear campos
            $this->paymentAmount = null;
            $this->paymentNotes = '';
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar pago: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Cargar últimos 20 payment events del tenant
        $paymentEvents = PaymentEvent::forTenant($this->subscription->tenant_id)
            ->latest()
            ->limit(20)
            ->get();

        return view('thunder-pack::livewire.super-admin.subscription-show', [
            'paymentEvents' => $paymentEvents,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
