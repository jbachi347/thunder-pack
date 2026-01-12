<?php

namespace ThunderPack\Livewire\SuperAdmin;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantLimitOverride;
use ThunderPack\Models\AvailableLimit;
use ThunderPack\Services\PlanLimitService;
use Livewire\Component;

class TenantLimits extends Component
{
    public Tenant $tenant;
    public string $limitKey = '';
    public string $limitValue = '';
    public string $notes = '';
    public bool $isUnlimited = false;

    protected $rules = [
        'limitKey' => 'required|string',
        'limitValue' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:500',
        'isUnlimited' => 'boolean',
    ];

    protected $messages = [
        'limitKey.required' => 'Debes seleccionar un tipo de límite.',
        'limitValue.numeric' => 'El valor debe ser numérico.',
        'limitValue.min' => 'El valor no puede ser negativo.',
        'notes.max' => 'Las notas no pueden exceder 500 caracteres.',
    ];

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function addOverride()
    {
        $this->validate([
            'limitKey' => 'required|string',
            'limitValue' => $this->isUnlimited ? 'nullable' : 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $value = $this->isUnlimited ? null : (int) $this->limitValue;
            
            PlanLimitService::setOverride(
                $this->tenant,
                $this->limitKey,
                $value,
                $this->notes
            );

            session()->flash('success', 'Override agregado correctamente.');
            
            // Reset form
            $this->reset(['limitKey', 'limitValue', 'notes', 'isUnlimited']);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar override: ' . $e->getMessage());
        }
    }

    public function removeOverride($overrideId)
    {
        try {
            $override = TenantLimitOverride::findOrFail($overrideId);
            
            if ($override->tenant_id !== $this->tenant->id) {
                session()->flash('error', 'Override no pertenece a este tenant.');
                return;
            }

            PlanLimitService::removeOverride($this->tenant, $override->limit_key);
            
            session()->flash('success', 'Override eliminado correctamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar override: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $subscription = $this->tenant->activeSubscription;
        $plan = $subscription?->plan;

        // Get plan-specific limits only
        $limits = [];
        $availableLimitsForDropdown = AvailableLimit::active()->ordered()->get();
        
        if ($plan) {
            // Get limits defined in the plan
            $planFeatures = $plan->features ?? [];
            
            // Add legacy plan attributes to features for backward compatibility
            if ($plan->staff_limit) {
                $planFeatures['staff_limit'] = $plan->staff_limit;
            }
            if ($plan->storage_quota_bytes) {
                $planFeatures['storage_quota_bytes'] = $plan->storage_quota_bytes;
            }
            
            // Only show limits that the plan actually defines
            foreach ($planFeatures as $key => $planValue) {
                $availableLimit = AvailableLimit::where('key', $key)->first();
                
                $limits[$key] = [
                    'name' => $availableLimit ? $availableLimit->display_name : ucwords(str_replace('_', ' ', $key)),
                    'category' => $availableLimit?->category ?? 'general',
                    'description' => $availableLimit?->description ?? '',
                    'unit' => $availableLimit?->unit ?? '',
                    'plan_value' => $planValue,
                    'current_usage' => PlanLimitService::getCurrentUsage($this->tenant, $key),
                    'override' => $this->tenant->limitOverrides()->where('limit_key', $key)->first(),
                ];
            }
        }

        return view('thunder-pack::livewire.super-admin.tenant-limits', [
            'limits' => $limits,
            'availableLimits' => $availableLimitsForDropdown,
            'plan' => $plan,
            'subscription' => $subscription,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
