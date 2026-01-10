<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use ThunderPack\Models\Subscription;
use ThunderPack\Services\SubscriptionService;

class SubscriptionsIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    protected $queryString = ['statusFilter', 'search'];

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function quickRenew(int $subscriptionId, int $days = 30)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $service = app(SubscriptionService::class);
            $service->renewManual($subscription->tenant, $days);

            session()->flash('message', "Suscripción renovada por {$days} días correctamente.");
        } catch (\Exception $e) {
            session()->flash('error', 'Error al renovar: ' . $e->getMessage());
        }
    }

    public function quickSetPastDue(int $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);
            $service = app(SubscriptionService::class);
            $service->setPastDue($subscription->tenant);

            session()->flash('message', 'Suscripción marcada como vencida.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Subscription::query()
            ->with(['tenant', 'plan']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->whereHas('tenant', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        $subscriptions = $query->latest()->paginate(20);

        $statusCounts = [
            'active' => Subscription::where('status', 'active')->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
            'canceled' => Subscription::where('status', 'canceled')->count(),
            'trial' => Subscription::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())
                ->count(),
        ];

        return view('thunder-pack::livewire.super-admin.subscriptions-index', [
            'subscriptions' => $subscriptions,
            'statusCounts' => $statusCounts,
        ])->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
