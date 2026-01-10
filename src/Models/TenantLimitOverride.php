<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLimitOverride extends Model
{
    protected $fillable = [
        'tenant_id',
        'limit_key',
        'limit_value',
        'notes',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parsed limit value (handles null for unlimited)
     */
    public function getParsedValue(): mixed
    {
        if ($this->limit_value === null) {
            return null; // unlimited
        }

        // Try to parse as number
        if (is_numeric($this->limit_value)) {
            return (int) $this->limit_value;
        }

        // Try to parse as boolean
        if (in_array(strtolower($this->limit_value), ['true', 'false'])) {
            return strtolower($this->limit_value) === 'true';
        }

        return $this->limit_value;
    }
}
