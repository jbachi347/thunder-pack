<?php

namespace ThunderPack\Services;

use ThunderPack\Models\Plan;
use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantLimitOverride;
use ThunderPack\Models\UsageEvent;
use Illuminate\Support\Facades\Cache;

class PlanLimitService
{
    /**
     * Check if tenant can perform an action that consumes resources
     * 
     * @throws \Exception if limit exceeded
     */
    public static function check(Tenant $tenant, string $limitKey, int $amount = 1): bool
    {
        $limit = static::getLimit($tenant, $limitKey);
        
        // null means unlimited
        if ($limit === null) {
            return true;
        }

        $currentUsage = static::getCurrentUsage($tenant, $limitKey);
        
        if ($currentUsage + $amount > $limit) {
            throw new \Exception(
                "Límite de {$limitKey} excedido. Límite: {$limit}, Uso actual: {$currentUsage}"
            );
        }

        return true;
    }

    /**
     * Check if tenant can perform action without throwing exception
     */
    public static function can(Tenant $tenant, string $limitKey, int $amount = 1): bool
    {
        try {
            return static::check($tenant, $limitKey, $amount);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the limit value for a tenant (checks overrides, then plan, then default)
     */
    public static function getLimit(Tenant $tenant, string $limitKey): mixed
    {
        // 1. Check tenant-specific overrides
        $override = TenantLimitOverride::where('tenant_id', $tenant->id)
            ->where('limit_key', $limitKey)
            ->first();

        if ($override) {
            return $override->getParsedValue();
        }

        // 2. Check plan features JSON
        $subscription = $tenant->subscriptions()
            ->where('status', 'active')
            ->orWhere('status', 'trialing')
            ->first();

        if ($subscription && $subscription->plan) {
            $planLimit = $subscription->plan->getLimit($limitKey);
            if ($planLimit !== null) {
                return $planLimit;
            }
        }

        // 3. Return null (unlimited) if not found
        return null;
    }

    /**
     * Get current usage for a limit key
     */
    public static function getCurrentUsage(Tenant $tenant, string $limitKey): int|float
    {
        // Cache usage queries for 5 minutes to avoid DB hammering
        $cacheKey = "tenant_{$tenant->id}_usage_{$limitKey}";

        return Cache::remember($cacheKey, 300, function () use ($tenant, $limitKey) {
            // Map limit keys to actual usage calculation
            return match ($limitKey) {
                'staff_limit', 'max_staff' => $tenant->getCurrentStaffCount(),
                'storage_quota_bytes', 'max_storage_bytes' => $tenant->storage_used_bytes,
                
                // For other limits, use usage_events table
                default => static::getUsageFromEvents($tenant, $limitKey),
            };
        });
    }

    /**
     * Get usage from usage_events table
     */
    protected static function getUsageFromEvents(Tenant $tenant, string $resourceType): float
    {
        // Check if this is a monthly limit
        if (str_ends_with($resourceType, '_per_month')) {
            $baseType = str_replace('_per_month', '', $resourceType);
            return UsageEvent::getMonthlyUsage($tenant->id, $baseType);
        }

        if (str_ends_with($resourceType, '_per_day')) {
            $baseType = str_replace('_per_day', '', $resourceType);
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
            return UsageEvent::getUsage($tenant->id, $baseType, $startDate, $endDate);
        }

        // For cumulative limits (total count), use all-time sum
        return UsageEvent::getUsage($tenant->id, $resourceType);
    }

    /**
     * Record usage event
     */
    public static function recordUsage(
        Tenant $tenant,
        string $resourceType,
        float $amount = 1,
        ?string $action = null,
        ?array $metadata = null
    ): UsageEvent {
        // Clear cache for this resource
        $cacheKey = "tenant_{$tenant->id}_usage_{$resourceType}";
        Cache::forget($cacheKey);

        // Also clear monthly/daily variants
        Cache::forget("tenant_{$tenant->id}_usage_{$resourceType}_per_month");
        Cache::forget("tenant_{$tenant->id}_usage_{$resourceType}_per_day");

        return UsageEvent::create([
            'tenant_id' => $tenant->id,
            'resource_type' => $resourceType,
            'amount' => $amount,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get remaining allowance for a limit
     */
    public static function getRemaining(Tenant $tenant, string $limitKey): ?float
    {
        $limit = static::getLimit($tenant, $limitKey);
        
        if ($limit === null) {
            return null; // unlimited
        }

        $usage = static::getCurrentUsage($tenant, $limitKey);
        return max(0, $limit - $usage);
    }

    /**
     * Get usage percentage (0-100)
     */
    public static function getUsagePercentage(Tenant $tenant, string $limitKey): ?float
    {
        $limit = static::getLimit($tenant, $limitKey);
        
        if ($limit === null || $limit == 0) {
            return null;
        }

        $usage = static::getCurrentUsage($tenant, $limitKey);
        return min(100, ($usage / $limit) * 100);
    }

    /**
     * Set tenant-specific limit override
     */
    public static function setOverride(
        Tenant $tenant,
        string $limitKey,
        mixed $limitValue,
        ?string $notes = null
    ): TenantLimitOverride {
        return TenantLimitOverride::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'limit_key' => $limitKey,
            ],
            [
                'limit_value' => $limitValue,
                'notes' => $notes,
            ]
        );
    }

    /**
     * Remove tenant-specific limit override
     */
    public static function removeOverride(Tenant $tenant, string $limitKey): bool
    {
        return TenantLimitOverride::where('tenant_id', $tenant->id)
            ->where('limit_key', $limitKey)
            ->delete() > 0;
    }

    /**
     * Clear all usage cache for a tenant
     */
    public static function clearCache(Tenant $tenant): void
    {
        Cache::flush(); // In production, use more targeted cache tags
    }
}
