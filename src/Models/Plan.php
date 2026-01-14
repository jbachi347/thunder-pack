<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'staff_limit',
        'storage_quota_bytes',
        'monthly_price_cents',
        'yearly_price_cents',
        'currency',
        'lemon_monthly_variant_id',
        'lemon_yearly_variant_id',
        'features',
    ];

    protected $casts = [
        'staff_limit' => 'integer',
        'storage_quota_bytes' => 'integer',
        'monthly_price_cents' => 'integer',
        'yearly_price_cents' => 'integer',
        'features' => 'array',
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Accessors
    public function getMonthlyPriceAttribute()
    {
        return $this->monthly_price_cents / 100;
    }

    public function getYearlyPriceAttribute()
    {
        return $this->yearly_price_cents ? $this->yearly_price_cents / 100 : null;
    }

    /**
     * Get Lemon Squeezy variant ID for a billing cycle
     */
    public function getLemonVariantId(string $billingCycle): ?string
    {
        return $billingCycle === 'yearly' 
            ? $this->lemon_yearly_variant_id 
            : $this->lemon_monthly_variant_id;
    }

    /**
     * Check if plan has Lemon Squeezy integration configured
     */
    public function hasLemonSqueezyIntegration(): bool
    {
        return !empty($this->lemon_monthly_variant_id) || !empty($this->lemon_yearly_variant_id);
    }

    /**
     * Get a limit value from features JSON or fallback to legacy columns
     */
    public function getLimit(string $key, mixed $default = null): mixed
    {
        // Check features JSON first
        if (is_array($this->features) && isset($this->features[$key])) {
            return $this->features[$key];
        }

        // Fallback to legacy columns for backward compatibility
        $legacyMapping = [
            'staff_limit' => $this->staff_limit,
            'max_staff' => $this->staff_limit,
            'storage_quota_bytes' => $this->storage_quota_bytes,
            'max_storage_bytes' => $this->storage_quota_bytes,
        ];

        return $legacyMapping[$key] ?? $default;
    }

    /**
     * Check if a feature is enabled
     */
    public function hasFeature(string $feature): bool
    {
        if (!is_array($this->features)) {
            return false;
        }

        // Check in modules array
        if (isset($this->features['modules']) && is_array($this->features['modules'])) {
            if (in_array($feature, $this->features['modules'])) {
                return true;
            }
        }

        // Check as boolean flag
        return ($this->features[$feature] ?? false) === true;
    }

    /**
     * Get all available modules
     */
    public function getModules(): array
    {
        return $this->features['modules'] ?? [];
    }
}
