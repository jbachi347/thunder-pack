<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionNotification extends Model
{
    protected $fillable = [
        'subscription_id',
        'type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
