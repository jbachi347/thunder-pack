<?php

namespace ThunderPack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool can(\ThunderPack\Models\Tenant $tenant, string $limitKey, int $amount = 1)
 * @method static int|null getLimit(\ThunderPack\Models\Tenant $tenant, string $limitKey)
 * @method static int getCurrentUsage(\ThunderPack\Models\Tenant $tenant, string $limitKey, string $period = 'month')
 * @method static void recordUsage(\ThunderPack\Models\Tenant $tenant, string $resourceKey, int $amount = 1)
 * @method static void clearCache(\ThunderPack\Models\Tenant $tenant)
 *
 * @see \ThunderPack\Services\PlanLimitService
 */
class PlanLimitService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThunderPack\Services\PlanLimitService::class;
    }
}
