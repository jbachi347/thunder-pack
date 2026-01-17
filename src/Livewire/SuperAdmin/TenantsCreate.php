<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;
use Illuminate\Validation\Rule;

class TenantsCreate extends Component
{
    public string $name = '';
    public string $slug = '';
    public string $brand_name = '';
    public int $storage_quota_gb = 10;

    public function mount()
    {
        // Set default storage quota from config or 10GB
        $this->storage_quota_gb = 10;
    }

    public function updatedName()
    {
        // Auto-generate slug from name
        if (empty($this->slug)) {
            $this->slug = \Illuminate\Support\Str::slug($this->name);
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('tenants', 'slug')],
            'brand_name' => 'nullable|string|max:255',
            'storage_quota_gb' => 'required|integer|min:1|max:1000',
        ]);

        // Convert GB to bytes
        $storageQuotaBytes = $validated['storage_quota_gb'] * 1024 * 1024 * 1024;

        Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'brand_name' => $validated['brand_name'] ?? $validated['name'],
            'storage_quota_bytes' => $storageQuotaBytes,
            'storage_used_bytes' => 0,
        ]);

        session()->flash('message', 'Tenant creado exitosamente');
        
        return redirect()->route('thunder-pack.sa.tenants.index');
    }

    public function render()
    {
        return view('thunder-pack::livewire.super-admin.tenants-create')
            ->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
