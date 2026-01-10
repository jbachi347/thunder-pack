<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Pivot
{
    protected $table = 'tenant_user';
    
    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'is_owner',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    public $timestamps = true;

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Helper methods para roles
    public function isOwner(): bool
    {
        return $this->role === 'owner' || $this->is_owner;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function canManageTeam(): bool
    {
        return $this->isAdmin();
    }

    public function canManageBilling(): bool
    {
        return $this->isOwner();
    }

    public function canCreateRequests(): bool
    {
        return true; // Todos los roles pueden crear requests
    }

    public function canManageClients(): bool
    {
        return $this->isAdmin();
    }

    public function canManageTemplates(): bool
    {
        return $this->isAdmin();
    }

    // Scope para obtener solo owners
    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    // Scope para obtener solo admins (incluye owners)
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['owner', 'admin']);
    }

    // Scope para obtener solo staff
    public function scopeStaffOnly($query)
    {
        return $query->where('role', 'staff');
    }
}
