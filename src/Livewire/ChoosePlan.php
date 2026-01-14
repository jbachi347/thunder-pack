<?php

namespace ThunderPack\Livewire;

use Livewire\Component;
use ThunderPack\Models\Plan;
use ThunderPack\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class ChoosePlan extends Component
{
    public $billingCycle = 'monthly';
    public $selectedPlan = null;
    public $isLoading = false;

    public function mount()
    {
        // Check if user already has a subscription
        $tenant = Auth::user()->tenants()->wherePivot('is_owner', true)->first();
        
        if ($tenant && $tenant->latestSubscription()?->isActive()) {
            return redirect()->route('dashboard');
        }
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
    }

    public function checkout($planId)
    {
        $this->isLoading = true;

        try {
            $tenant = Auth::user()->tenants()->wherePivot('is_owner', true)->firstOrFail();
            $plan = Plan::findOrFail($planId);

            // Check if plan has Lemon Squeezy integration
            if (!$plan->hasLemonSqueezyIntegration()) {
                session()->flash('error', 'Este plan no está disponible para compra en línea. Contacta con soporte.');
                $this->isLoading = false;
                return;
            }

            // Create checkout URL
            $subscriptionService = app(SubscriptionService::class);
            $checkoutUrl = $subscriptionService->createCheckout($tenant, $plan, 'lemon_squeezy', $this->billingCycle);

            // Redirect to Lemon Squeezy checkout
            return redirect($checkoutUrl);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Checkout error', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'billing_cycle' => $this->billingCycle,
            ]);

            session()->flash('error', 'Error al crear el checkout. Por favor, intenta nuevamente.');
            $this->isLoading = false;
        }
    }

    public function render()
    {
        $plans = Plan::where('code', '!=', 'free')
                     ->orderBy('monthly_price_cents')
                     ->get();

        return view('thunder-pack::livewire.choose-plan', [
            'plans' => $plans,
        ])->layout('layouts.guest');
    }
}
