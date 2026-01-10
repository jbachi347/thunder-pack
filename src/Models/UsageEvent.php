<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageEvent extends Model
{
    const UPDATED_AT = null; // Only track creation timestamp

    protected $fillable = [
        'tenant_id',
        'resource_type',
        'amount',
        'action',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get usage for a resource type within a date range
     */
    public static function getUsage(
        int $tenantId,
        string $resourceType,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): float {
        $query = static::where('tenant_id', $tenantId)
            ->where('resource_type', $resourceType);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get monthly usage for a resource type
     */
    public static function getMonthlyUsage(
        int $tenantId,
        string $resourceType,
        ?int $year = null,
        ?int $month = null
    ): float {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        return static::getUsage($tenantId, $resourceType, $startDate, $endDate);
    }
}
