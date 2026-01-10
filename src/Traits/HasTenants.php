<?php

namespace ThunderPack\Traits;

use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTenants
{
    /**
     * Get all tenants this user has access to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->using(TenantUser::class)
            ->withPivot(['role', 'is_owner'])
            ->withTimestamps();
    }

    /**
     * Get the current tenant from session.
     */
    public function currentTenant(): ?Tenant
    {
        if (session()->has('current_tenant_id')) {
            return $this->tenants()->where('tenant_id', session('current_tenant_id'))->first();
        }

        return null;
    }

    /**
     * Set the current tenant in session.
     */
    public function setCurrentTenant(Tenant $tenant): void
    {
        session(['current_tenant_id' => $tenant->id]);
    }

    /**
     * Check if user has access to a given tenant.
     */
    public function hasAccessToTenant($tenant): bool
    {
        $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        return $this->tenants()->where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Get user's role in a specific tenant.
     */
    public function getRoleInTenant(?int $tenantId = null): ?string
    {
        $tenantId = $tenantId ?? session('current_tenant_id');
        
        if (!$tenantId) {
            return null;
        }

        $pivot = $this->tenants()->where('tenant_id', $tenantId)->first()?->pivot;
        return $pivot?->role;
    }

    /**
     * Check if user is owner of a tenant.
     */
    public function isOwnerOfTenant(?int $tenantId = null): bool
    {
        return $this->getRoleInTenant($tenantId) === 'owner';
    }

    /**
     * Check if user is admin of a tenant (owner or admin role).
     */
    public function isAdminOfTenant(?int $tenantId = null): bool
    {
        $role = $this->getRoleInTenant($tenantId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user is staff member of a tenant.
     */
    public function isStaffOfTenant(?int $tenantId = null): bool
    {
        return $this->getRoleInTenant($tenantId) === 'staff';
    }

    /**
     * Check if user can manage team in a tenant.
     */
    public function canManageTeamInTenant(?int $tenantId = null): bool
    {
        return $this->isAdminOfTenant($tenantId);
    }

    /**
     * Check if user can manage billing in a tenant.
     */
    public function canManageBillingInTenant(?int $tenantId = null): bool
    {
        return $this->isOwnerOfTenant($tenantId);
    }
}
