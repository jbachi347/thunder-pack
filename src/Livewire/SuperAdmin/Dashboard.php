<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;
use App\Models\User;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::whereHas('subscriptions', function($q) {
                $q->where('status', 'active');
            })->count(),
            'total_users' => User::whereHas('tenants')->count(),
            'total_storage_used' => Tenant::sum('storage_used_bytes'),
            'total_storage_quota' => Tenant::sum('storage_quota_bytes'),
        ];

        $recentTenants = Tenant::latest()
            ->with('subscriptions')
            ->limit(5)
            ->get();

        return view('thunder-pack::livewire.super-admin.dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
