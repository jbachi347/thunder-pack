<?php

namespace ThunderPack\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth()->check() && auth()->user()->is_super_admin, 403, 'Acceso denegado. Solo super-administradores.');
        
        return $next($request);
    }
}
