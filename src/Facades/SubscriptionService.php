<?php

namespace ThunderPack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ThunderPack\Models\Subscription activateManual(\ThunderPack\Models\Tenant $tenant, \ThunderPack\Models\Plan $plan, int $days = 30, string $paymentMethod = 'manual')
 * @method static bool isSubscriptionActive(\ThunderPack\Models\Tenant $tenant)
 * @method static int|null getDaysUntilExpiration(\ThunderPack\Models\Tenant $tenant)
 * @method static void cancelSubscription(\ThunderPack\Models\Tenant $tenant)
 *
 * @see \ThunderPack\Services\SubscriptionService
 */
class SubscriptionService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThunderPack\Services\SubscriptionService::class;
    }
}
