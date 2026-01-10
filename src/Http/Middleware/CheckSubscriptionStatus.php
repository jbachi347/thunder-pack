<?php

namespace ThunderPack\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use ThunderPack\Models\Tenant;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si es super-admin, bypass
        if (Auth::user()?->is_super_admin) {
            return $next($request);
        }

        // Si no hay tenant en sesión, continuar (TenantMiddleware se encargará)
        $tenantId = session('current_tenant_id');
        if (!$tenantId) {
            return $next($request);
        }

        // Obtener tenant con su última suscripción (independiente del status)
        $tenant = Tenant::with('latestSubscription')->find($tenantId);
        
        if (!$tenant) {
            return $next($request);
        }

        $subscription = $tenant->latestSubscription;

        // Si no tiene suscripción, redirigir
        if (!$subscription) {
            return redirect()->route('subscription.expired')
                ->with('error', 'No tienes una suscripción activa. Contacta con soporte.');
        }

        // Si la suscripción está vencida o cancelada, redirigir
        if (in_array($subscription->status, ['past_due', 'canceled'])) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Tu suscripción ha vencido. Por favor renueva tu plan para continuar.');
        }

        // Verificar también si ends_at ya pasó (aunque el status sea 'active')
        // Esto cubre el caso donde el job aún no ha ejecutado
        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Tu suscripción ha vencido. Por favor renueva tu plan para continuar.');
        }

        return $next($request);
    }
}
