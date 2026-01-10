<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
        'provider_payload',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'provider_payload' => 'array',
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function notifications()
    {
        return $this->hasMany(SubscriptionNotification::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', 'trialing');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isOnTrial()
    {
        return $this->status === 'trialing' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }
}
