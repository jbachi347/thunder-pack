<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class UsersIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $superAdminFilter = '';

    protected $queryString = ['search', 'roleFilter', 'superAdminFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingSuperAdminFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        
        $query = $userClass::query()
            ->withCount('tenants')
            ->with('tenants:id,name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->superAdminFilter !== '') {
            $query->where('is_super_admin', (bool) $this->superAdminFilter);
        }

        if ($this->roleFilter) {
            $query->whereHas('tenants', function($q) {
                $q->where('tenant_user.role', $this->roleFilter);
            });
        }

        $users = $query->latest()->paginate(20);

        return view('thunder-pack::livewire.super-admin.users-index', [
            'users' => $users,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
