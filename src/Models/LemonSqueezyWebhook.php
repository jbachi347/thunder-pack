<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class LemonSqueezyWebhook extends Model
{
    protected $table = 'lemon_squeezy_webhooks';

    protected $fillable = [
        'event_name',
        'event_id',
        'signature',
        'payload',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];
}
