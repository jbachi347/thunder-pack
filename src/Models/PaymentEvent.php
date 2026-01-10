<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    protected $table = 'payment_events';

    protected $fillable = [
        'tenant_id',
        'provider',
        'event_type',
        'provider_event_id',
        'amount_cents',
        'currency',
        'status',
        'payload',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'amount_cents' => 'integer',
        'payload' => 'array',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Scopes
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
