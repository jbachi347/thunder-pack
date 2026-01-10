<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeamInvitation extends Model
{
    protected $fillable = [
        'tenant_id',
        'email',
        'role',
        'token',
        'expires_at',
        'invited_by',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'invited_by');
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                    ->whereNull('accepted_at');
    }

    // Métodos helper
    public function isValid(): bool
    {
        return $this->expires_at > now() && is_null($this->accepted_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    // Métodos estáticos
    public static function createInvitation(
        int $tenantId,
        string $email,
        string $role = 'staff',
        ?int $invitedBy = null,
        int $expirationDays = 7
    ): self {
        // Cancelar invitaciones previas pendientes
        static::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->update(['accepted_at' => now()]); // Marcar como "cancelada"

        return static::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'role' => $role,
            'token' => Str::random(64),
            'expires_at' => now()->addDays($expirationDays),
            'invited_by' => $invitedBy,
        ]);
    }

    public function sendInvitation(): void
    {
        // Note: This method requires a Mail class from the host application
        // Mail::to($this->email)->send(new TeamInvitationMail($this));
    }

    public function accept($user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Verificar límites del plan
        if (!$this->tenant->canAddStaffMember()) {
            return false;
        }

        // Agregar usuario al tenant
        $this->tenant->users()->attach($user->id, [
            'role' => $this->role,
            'is_owner' => $this->role === 'owner',
        ]);

        // Marcar invitación como aceptada
        $this->update(['accepted_at' => now()]);

        // Clear cache for staff limit usage
        \ThunderPack\Services\PlanLimitService::clearCache($this->tenant);

        // Check and notify if limit thresholds reached
        \ThunderPack\Services\LimitNotificationService::checkAndNotifyStaffLimit($this->tenant);

        return true;
    }
}
