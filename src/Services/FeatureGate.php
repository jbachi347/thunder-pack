<?php

namespace ThunderPack\Services;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\Plan;
use Illuminate\Support\Facades\Cache;

class FeatureGate
{
    /**
     * Check if tenant has access to a feature
     */
    public static function allows(Tenant $tenant, string $feature): bool
    {
        $cacheKey = "tenant_{$tenant->id}_feature_{$feature}";

        return Cache::remember($cacheKey, 600, function () use ($tenant, $feature) {
            $subscription = $tenant->subscriptions()
                ->where('status', 'active')
                ->orWhere('status', 'trialing')
                ->first();

            if (!$subscription || !$subscription->plan) {
                return false;
            }

            return $subscription->plan->hasFeature($feature);
        });
    }

    /**
     * Check if tenant denies access to a feature
     */
    public static function denies(Tenant $tenant, string $feature): bool
    {
        return !static::allows($tenant, $feature);
    }

    /**
     * Get all enabled modules for tenant
     */
    public static function getModules(Tenant $tenant): array
    {
        $subscription = $tenant->subscriptions()
            ->where('status', 'active')
            ->orWhere('status', 'trialing')
            ->first();

        if (!$subscription || !$subscription->plan) {
            return [];
        }

        return $subscription->plan->getModules();
    }

    /**
     * Check if tenant has any of the given features
     */
    public static function allowsAny(Tenant $tenant, array $features): bool
    {
        foreach ($features as $feature) {
            if (static::allows($tenant, $feature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if tenant has all of the given features
     */
    public static function allowsAll(Tenant $tenant, array $features): bool
    {
        foreach ($features as $feature) {
            if (!static::allows($tenant, $feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear feature cache for tenant
     */
    public static function clearCache(Tenant $tenant): void
    {
        // In production, use cache tags for better performance
        Cache::flush();
    }
}
