<?php

namespace ThunderPack\Livewire\SuperAdmin;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantLimitOverride;
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

        // Get all possible limits
        $limits = [
            'staff_limit' => [
                'name' => 'Límite de Personal',
                'plan_value' => $plan?->staff_limit ?? 1,
                'current_usage' => $this->tenant->getCurrentStaffCount(),
                'override' => $this->tenant->limitOverrides()->where('limit_key', 'staff_limit')->first(),
            ],
            'max_whatsapp_phones' => [
                'name' => 'Teléfonos WhatsApp',
                'plan_value' => $plan?->getLimit('max_whatsapp_phones') ?? 0,
                'current_usage' => $this->tenant->whatsappPhones()->count(),
                'override' => $this->tenant->limitOverrides()->where('limit_key', 'max_whatsapp_phones')->first(),
            ],
        ];

        // Add limits from plan features if exist
        if ($plan && $plan->features) {
            foreach (['max_clients', 'max_projects', 'api_calls_per_month', 'api_calls_per_day'] as $key) {
                if (isset($plan->features[$key])) {
                    $limits[$key] = [
                        'name' => ucwords(str_replace(['_', 'max ', 'per '], [' ', '', '/ '], $key)),
                        'plan_value' => $plan->features[$key],
                        'current_usage' => PlanLimitService::getCurrentUsage($this->tenant, $key),
                        'override' => $this->tenant->limitOverrides()->where('limit_key', $key)->first(),
                    ];
                }
            }
        }

        return view('thunder-pack::livewire.super-admin.tenant-limits', [
            'limits' => $limits,
            'plan' => $plan,
            'subscription' => $subscription,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
