<?php

namespace ThunderPack\Livewire\SuperAdmin;

use ThunderPack\Models\Plan;
use ThunderPack\Models\AvailableLimit;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('thunder-pack::layouts.app-sidebar-sa')]
class PlanLimits extends Component
{
    public Plan $plan;
    public $selectedLimits = []; // [limit_key => value]

    public function mount(Plan $plan)
    {
        $this->plan = $plan;
        $this->loadSelectedLimits();
    }

    public function loadSelectedLimits()
    {
        // Load current plan features
        $this->selectedLimits = $this->plan->features ?? [];
        
        // Add legacy limits if they exist
        if ($this->plan->staff_limit) {
            $this->selectedLimits['staff_limit'] = $this->plan->staff_limit;
        }
        if ($this->plan->storage_quota_bytes) {
            $this->selectedLimits['storage_quota_bytes'] = $this->plan->storage_quota_bytes;
        }
    }

    public function save()
    {
        // Build features from selected limits
        $planFeatures = [];
        foreach ($this->selectedLimits as $key => $value) {
            if (!empty($value) && $value > 0) {
                $planFeatures[$key] = (int) $value;
            }
        }
        
        // Update plan features
        $this->plan->update([
            'features' => $planFeatures,
            // Update legacy fields if they're selected
            'staff_limit' => $this->selectedLimits['staff_limit'] ?? $this->plan->staff_limit,
            'storage_quota_bytes' => $this->selectedLimits['storage_quota_bytes'] ?? $this->plan->storage_quota_bytes,
        ]);

        session()->flash('success', 'Límites del plan actualizados exitosamente.');
    }

    public function removeLimit($limitKey)
    {
        unset($this->selectedLimits[$limitKey]);
    }

    public function addLimit($limitKey)
    {
        $availableLimit = AvailableLimit::where('key', $limitKey)->first();
        if ($availableLimit) {
            $this->selectedLimits[$limitKey] = $availableLimit->default_value;
        }
    }

    public function render()
    {
        // Get all available limits grouped by category - calculate fresh on each render
        $availableLimits = AvailableLimit::active()
            ->ordered()
            ->get()
            ->groupBy('category');
        
        return view('thunder-pack::livewire.super-admin.plan-limits', [
            'availableLimits' => $availableLimits
        ]);
    }
}