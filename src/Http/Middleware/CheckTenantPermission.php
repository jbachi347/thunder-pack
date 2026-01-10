<?php

namespace ThunderPack\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantPermission
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga el rol adecuado para la acción en el tenant actual.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredRole = null): Response
    {
        if (!auth()->check()) {
            abort(401, 'No autenticado.');
        }

        $tenantId = session('current_tenant_id');
        
        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $user = auth()->user();

        // Super-admin bypass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar que el usuario pertenezca al tenant
        $userRole = $user->getRoleInTenant($tenantId);
        
        if (!$userRole) {
            abort(403, 'No tienes acceso a este tenant.');
        }

        // Si se especifica un rol requerido, verificarlo
        if ($requiredRole) {
            $hasPermission = match($requiredRole) {
                'owner' => $user->isOwnerOfTenant($tenantId),
                'admin' => $user->isAdminOfTenant($tenantId),
                'staff' => $user->isStaffOfTenant($tenantId),
                default => false,
            };

            if (!$hasPermission) {
                abort(403, 'No tienes permisos suficientes para realizar esta acción.');
            }
        }

        return $next($request);
    }
}
