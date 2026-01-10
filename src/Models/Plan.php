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
        'currency',
        'features',
    ];

    protected $casts = [
        'staff_limit' => 'integer',
        'storage_quota_bytes' => 'integer',
        'monthly_price_cents' => 'integer',
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
