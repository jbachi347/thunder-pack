<?php

namespace ThunderPack\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Super-admins bypass tenant requirement
            if ($user->is_super_admin) {
                return $next($request);
            }
            
            $tenantId = session('current_tenant_id');

            if ($tenantId) {
                $tenant = $user->tenants()->find($tenantId);
                if (!$tenant) {
                    // The user does not have access to this tenant, so we clear session and redirect.
                    session()->forget('current_tenant_id');
                    return redirect()->route('thunder-pack.tenant.select')->with('error', 'Acceso no autorizado al tenant.');
                }
                // Set the current tenant for the user.
                $user->setCurrentTenant($tenant);
            } else {
                // No tenant set - redirect to tenant selection if not already there
                if (!$request->routeIs('thunder-pack.tenant.select')) {
                    return redirect()->route('thunder-pack.tenant.select')->with('info', 'Por favor selecciona un tenant para continuar.');
                }
            }
        }

        return $next($request);
    }
}
