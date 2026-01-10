<?php

namespace ThunderPack\Models;

use ThunderPack\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantWhatsappPhone extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'phone_number',
        'instance_name',
        'is_default',
        'notification_types',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'notification_types' => 'array',
    ];

    /**
     * Validation rules for phone number format (E.164)
     */
    public static function validationRules(): array
    {
        return [
            'phone_number' => [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{7,14}$/', // E.164 format: +[country code][number] (8-15 digits)
            ],
            'instance_name' => 'nullable|string|max:255',
            'notification_types' => 'nullable|array',
            'notification_types.*' => 'string|in:subscription_activated,subscription_expiring,subscription_expired,payment_received,staff_limit_reached',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format phone number to WhatsApp format
     */
    public function getFormattedPhoneAttribute(): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $this->phone_number);
        
        // If already has @s.whatsapp.net, return as is
        if (str_contains($this->phone_number, '@s.whatsapp.net')) {
            return $this->phone_number;
        }
        
        return $cleaned . '@s.whatsapp.net';
    }

    /**
     * Get display-friendly phone number
     */
    public function getDisplayPhoneAttribute(): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $this->phone_number);
        
        // Format with + prefix if not present
        if (!str_starts_with($cleaned, '+')) {
            return '+' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Check if a notification type is enabled
     */
    public function hasNotificationType(string $type): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        if (empty($this->notification_types)) {
            return true; // If no types specified, all are enabled
        }
        
        return in_array($type, $this->notification_types);
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(WhatsappMessageLog::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForNotificationType($query, string $type)
    {
        return $query->active()->where(function ($q) use ($type) {
            $q->whereNull('notification_types')
              ->orWhereJsonContains('notification_types', $type);
        });
    }

    /**
     * Boot method to handle default phone logic
     */
    protected static function boot()
    {
        parent::boot();

        // When setting a phone as default, unset others for the same tenant
        static::saving(function ($phone) {
            if ($phone->is_default && $phone->tenant_id) {
                static::where('tenant_id', $phone->tenant_id)
                    ->where('id', '!=', $phone->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
