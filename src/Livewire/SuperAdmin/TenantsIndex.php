<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use ThunderPack\Models\Tenant;

class TenantsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tenant::query()
            ->withCount(['users'])
            ->with('subscriptions');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->whereHas('subscriptions', function($q) {
                $q->where('status', $this->statusFilter)
                  ->latest('created_at')
                  ->limit(1);
            });
        }

        $tenants = $query->latest()->paginate(20);

        return view('thunder-pack::livewire.super-admin.tenants-index', [
            'tenants' => $tenants,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
