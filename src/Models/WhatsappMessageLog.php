<?php

namespace ThunderPack\Models;

use ThunderPack\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessageLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'tenant_whatsapp_phone_id',
        'phone_number',
        'message',
        'status',
        'response',
        'notification_type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function whatsappPhone(): BelongsTo
    {
        return $this->belongsTo(TenantWhatsappPhone::class, 'tenant_whatsapp_phone_id');
    }

    /**
     * Scopes
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'error']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Accessors
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'error' => 'red',
            default => 'gray',
        };
    }

    public function getStatusBadgeTextAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'Enviado',
            'pending' => 'Pendiente',
            'failed' => 'Fallido',
            'error' => 'Error',
            default => 'Desconocido',
        };
    }

    /**
     * Mark message as sent
     */
    public function markAsSent(array $response = []): void
    {
        $this->update([
            'status' => 'sent',
            'response' => json_encode($response),
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $error,
        ]);
    }

    /**
     * Mark message as error
     */
    public function markAsError(string $error): void
    {
        $this->update([
            'status' => 'error',
            'response' => $error,
        ]);
    }
}
