<?php

namespace ThunderPack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool allows(\ThunderPack\Models\Tenant $tenant, string $feature)
 * @method static bool allowsAny(\ThunderPack\Models\Tenant $tenant, array $features)
 * @method static mixed get(\ThunderPack\Models\Tenant $tenant, string $key, mixed $default = null)
 * @method static bool has(string $feature)
 * @method static bool can(string $limitKey, int $amount = 1)
 *
 * @see \ThunderPack\Services\FeatureGate
 */
class FeatureGate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThunderPack\Services\FeatureGate::class;
    }
}
