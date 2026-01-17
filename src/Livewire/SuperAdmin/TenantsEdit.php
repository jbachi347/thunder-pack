<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;
use Illuminate\Validation\Rule;

class TenantsEdit extends Component
{
    public Tenant $tenant;
    public string $name = '';
    public string $slug = '';
    public string $brand_name = '';
    public int $storage_quota_gb = 10;

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->name = $tenant->name;
        $this->slug = $tenant->slug;
        $this->brand_name = $tenant->brand_name ?? '';
        $this->storage_quota_gb = (int) round($tenant->storage_quota_bytes / 1024 / 1024 / 1024);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('tenants', 'slug')->ignore($this->tenant->id)],
            'brand_name' => 'nullable|string|max:255',
            'storage_quota_gb' => 'required|integer|min:1|max:1000',
        ]);

        // Convert GB to bytes
        $storageQuotaBytes = $validated['storage_quota_gb'] * 1024 * 1024 * 1024;

        $this->tenant->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'brand_name' => $validated['brand_name'] ?: $validated['name'],
            'storage_quota_bytes' => $storageQuotaBytes,
        ]);

        session()->flash('message', 'Tenant actualizado exitosamente');
        
        return redirect()->route('thunder-pack.sa.tenants.show', $this->tenant);
    }

    public function render()
    {
        return view('thunder-pack::livewire.super-admin.tenants-edit')
            ->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
