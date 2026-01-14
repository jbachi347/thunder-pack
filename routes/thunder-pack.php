<?php

use Illuminate\Support\Facades\Route;
use ThunderPack\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Thunder Pack Routes
|--------------------------------------------------------------------------
|
| These routes are automatically loaded by the ThunderPackServiceProvider.
| They handle tenant selection, subscription management, team invitations,
| and the Super Admin panel.
|
*/

// Public Routes (no auth required)
Route::get('/invitations/accept/{token}', \ThunderPack\Livewire\Team\AcceptInvitation::class)
    ->name('thunder-pack.invitations.accept');

// NOTE: Webhook route registered separately in ServiceProvider to avoid middleware conflicts

// Auth Required Routes
Route::middleware('auth')->group(function () {
    
    // Tenant Selector
    Route::get('/tenant/select', \ThunderPack\Livewire\TenantSelector::class)
        ->name('thunder-pack.tenant.select');
    
    // Subscription Expired Page
    Route::get('/subscription/expired', \ThunderPack\Livewire\SubscriptionExpired::class)
        ->name('thunder-pack.subscription.expired');
    
    // Plan Selection (requires tenant)
    Route::middleware(['tenant'])->group(function () {
        Route::get('/plans', \ThunderPack\Livewire\ChoosePlan::class)
            ->name('thunder-pack.plans.choose');
    });
    
    // Team Management (requires tenant and active subscription)
    Route::middleware(['tenant', 'subscription'])->group(function () {
        Route::get('/team', \ThunderPack\Livewire\Team\Index::class)
            ->name('thunder-pack.team.index');
    });
});

// Super Admin Panel Routes
if (config('thunder-pack.features.super_admin_panel', true)) {
    $prefix = config('thunder-pack.routes.super_admin_prefix', 'sa');
    
    Route::middleware(['auth', 'superadmin'])
        ->prefix($prefix)
        ->name('thunder-pack.sa.')
        ->group(function () {
            
            // Dashboard
            Route::get('/dashboard', \ThunderPack\Livewire\SuperAdmin\Dashboard::class)
                ->name('dashboard');
            
            // Tenants Management
            Route::get('/tenants', \ThunderPack\Livewire\SuperAdmin\TenantsIndex::class)
                ->name('tenants.index');
            
            Route::get('/tenants/{tenant}', \ThunderPack\Livewire\SuperAdmin\TenantShow::class)
                ->name('tenants.show');
            
            Route::get('/tenants/{tenant}/limits', \ThunderPack\Livewire\SuperAdmin\TenantLimits::class)
                ->name('tenants.limits');
            
            // Subscriptions Management
            Route::get('/subscriptions', \ThunderPack\Livewire\SuperAdmin\SubscriptionsIndex::class)
                ->name('subscriptions.index');
            
            Route::get('/subscriptions/{subscription}', \ThunderPack\Livewire\SuperAdmin\SubscriptionShow::class)
                ->name('subscriptions.show');
            
            // Plans Management
            Route::get('/plans', \ThunderPack\Livewire\SuperAdmin\PlansIndex::class)
                ->name('plans.index');
            Route::get('/plans/{plan}/limits', \ThunderPack\Livewire\SuperAdmin\PlanLimits::class)
                ->name('plans.limits');
        });
}
