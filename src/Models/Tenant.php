<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'brand_name',
        'brand_logo_path',
        'brand_primary_color',
        'storage_quota_bytes',
        'storage_used_bytes',
        'data',
    ];

    protected $casts = [
        'storage_quota_bytes' => 'integer',
        'storage_used_bytes' => 'integer',
        'data' => 'array',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = static::generateUniqueSlug($tenant->name);
            }
        });
    }

    /**
     * Generate a unique slug from name with random suffix
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        // Check if slug exists and add random suffix if needed
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::lower(Str::random(4));
            $suffix++;
            
            // Fallback to incremental suffix after 10 attempts
            if ($suffix > 10) {
                $slug = $baseSlug . '-' . time();
                break;
            }
        }

        return $slug;
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'tenant_user')
            ->using(TenantUser::class)
            ->withPivot(['role', 'is_owner'])
            ->withTimestamps();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function whatsappPhones()
    {
        return $this->hasMany(TenantWhatsappPhone::class);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappMessageLog::class);
    }

    public function limitOverrides()
    {
        return $this->hasMany(TenantLimitOverride::class);
    }

    public function usageEvents()
    {
        return $this->hasMany(UsageEvent::class);
    }

    public function teamInvitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    // Helper methods
    public function isStorageAvailable($bytes)
    {
        return ($this->storage_used_bytes + $bytes) <= $this->storage_quota_bytes;
    }

    public function getRemainingStorageBytes()
    {
        return max(0, $this->storage_quota_bytes - $this->storage_used_bytes);
    }

    public function getStorageUsagePercentage()
    {
        if ($this->storage_quota_bytes === 0) {
            return 0;
        }
        return ($this->storage_used_bytes / $this->storage_quota_bytes) * 100;
    }

    // Staff Management Helper Methods
    public function getCurrentStaffCount(): int
    {
        return $this->users()->count();
    }

    public function getStaffLimit(): int
    {
        return $this->activeSubscription?->plan?->staff_limit ?? 1;
    }

    public function canAddStaffMember(): bool
    {
        return $this->getCurrentStaffCount() < $this->getStaffLimit();
    }

    public function getRemainingStaffSlots(): int
    {
        return max(0, $this->getStaffLimit() - $this->getCurrentStaffCount());
    }

    public function getStaffUsagePercentage(): float
    {
        $limit = $this->getStaffLimit();
        if ($limit === 0) {
            return 100;
        }
        return ($this->getCurrentStaffCount() / $limit) * 100;
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->orWhere('status', 'trialing')
            ->latest();
    }

    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    // Team Management Methods
    public function owners()
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    public function admins()
    {
        return $this->users()->whereIn('tenant_user.role', ['owner', 'admin']);
    }

    public function staffMembers()
    {
        return $this->users()->wherePivot('role', 'staff');
    }

    public function allTeamMembers()
    {
        return $this->users()->orderBy('tenant_user.role', 'desc');
    }

    public function inviteUser(string $email, string $role = 'staff', ?int $invitedBy = null): TeamInvitation
    {
        if (!$this->canAddStaffMember()) {
            throw new \Exception('No se pueden agregar más miembros. Límite del plan alcanzado.');
        }

        if ($this->users()->where('email', $email)->exists()) {
            throw new \Exception('El usuario ya pertenece a este tenant.');
        }

        return TeamInvitation::createInvitation($this->id, $email, $role, $invitedBy);
    }
}
