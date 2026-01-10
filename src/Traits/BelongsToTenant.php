<?php

namespace ThunderPack\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait and apply tenant scope.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // No aplicar scope si es super-admin
            if (auth()->check() && auth()->user()->is_super_admin) {
                return;
            }

            // No aplicar scope si no hay tenant en sesión
            if (!session('current_tenant_id')) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.tenant_id', session('current_tenant_id'));
        });
    }
}
