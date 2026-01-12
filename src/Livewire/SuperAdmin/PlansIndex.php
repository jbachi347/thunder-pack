<?php

namespace ThunderPack\Livewire\SuperAdmin;

use ThunderPack\Models\Plan;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('thunder-pack::layouts.app-sidebar-sa')]
class PlansIndex extends Component
{
    public $plans;
    public $showModal = false;
    public $editingPlanId = null;

    // Form fields
    public $code = '';
    public $name = '';
    public $monthly_price_cents = 0;
    public $currency = 'USD';
    public $staff_limit = 3;
    public $storage_quota_bytes = 5368709120; // 5GB in bytes
    public $features = [];

    protected $rules = [
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'monthly_price_cents' => 'required|integer|min:0',
        'currency' => 'required|string|max:10',
        'staff_limit' => 'required|integer|min:1',
        'storage_quota_bytes' => 'required|integer|min:0',
    ];

    public function mount()
    {
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $this->plans = Plan::withCount('subscriptions')
            ->orderBy('monthly_price_cents')
            ->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        $this->editingPlanId = $plan->id;
        $this->code = $plan->code;
        $this->name = $plan->name;
        $this->monthly_price_cents = $plan->monthly_price_cents;
        $this->currency = $plan->currency;
        $this->staff_limit = $plan->staff_limit;
        $this->storage_quota_bytes = $plan->storage_quota_bytes;
        $this->features = $plan->features ?? [];
        
        $this->showModal = true;
    }

    public function save()
    {
        $validatedRules = $this->rules;
        
        // Add unique rule for code if creating or editing different plan
        if ($this->editingPlanId) {
            $validatedRules['code'] .= '|unique:plans,code,' . $this->editingPlanId;
        } else {
            $validatedRules['code'] .= '|unique:plans,code';
        }

        $this->validate($validatedRules);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'monthly_price_cents' => $this->monthly_price_cents,
            'currency' => $this->currency,
            'staff_limit' => $this->staff_limit,
            'storage_quota_bytes' => $this->storage_quota_bytes,
            'features' => $this->features,
        ];

        if ($this->editingPlanId) {
            $plan = Plan::findOrFail($this->editingPlanId);
            $plan->update($data);
            session()->flash('message', 'Plan actualizado exitosamente.');
        } else {
            Plan::create($data);
            session()->flash('message', 'Plan creado exitosamente.');
        }

        $this->closeModal();
        $this->loadPlans();
    }

    public function delete($id)
    {
        $plan = Plan::findOrFail($id);
        
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un plan con suscripciones activas.');
            return;
        }

        $plan->delete();
        session()->flash('message', 'Plan eliminado exitosamente.');
        $this->loadPlans();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingPlanId = null;
        $this->code = '';
        $this->name = '';
        $this->monthly_price_cents = 0;
        $this->currency = 'USD';
        $this->staff_limit = 3;
        $this->storage_quota_bytes = 5368709120; // 5GB
        $this->features = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('thunder-pack::livewire.super-admin.plans-index');
    }
}
