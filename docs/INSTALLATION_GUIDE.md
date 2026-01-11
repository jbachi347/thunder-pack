# Thunder Pack - Guía de Instalación Completa

Guía paso a paso para instalar Thunder Pack en un proyecto Laravel nuevo con el tema completo.

## 1. Requisitos Previos

- Laravel 12+
- PHP 8.2+
- Composer instalado
- npm instalado
- Base de datos configurada (SQLite, MySQL, etc.)

## 2. Instalación de Thunder Pack

### 2.1. Agregar Repositorio Local

Editar `composer.json` del proyecto:

```json
{
    "require": {
        "bachisoft/thunder-pack": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../thunder-pack",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

### 2.2. Instalar Paquete

```bash
composer update bachisoft/thunder-pack
```

### 2.3. Publicar Assets

```bash
php artisan vendor:publish --provider="ThunderPack\ThunderPackServiceProvider"
```

Esto copiará:
- `config/thunder-pack.php` - Configuración del paquete
- `database/migrations/*` - Migraciones de tablas
- `resources/views/vendor/thunder-pack/*` - Vistas Livewire
- `docs/thunder-pack/*` - Documentación

## 3. Configurar Variables de Entorno

Agregar al archivo `.env`:

```env
# Thunder Pack Configuration
THUNDER_PACK_ROUTES_ENABLED=true
THUNDER_PACK_ROUTES_PREFIX=
THUNDER_PACK_SA_PREFIX=sa
THUNDER_PACK_WHATSAPP_ENABLED=false
THUNDER_PACK_TEAM_INVITATIONS_ENABLED=false
THUNDER_PACK_SA_PANEL_ENABLED=true
THUNDER_PACK_USAGE_TRACKING_ENABLED=true
THUNDER_PACK_EXPIRING_THRESHOLD_DAYS=7
THUNDER_PACK_GRACE_PERIOD_DAYS=7

# WhatsApp Evolution API Configuration (Opcional)
WHATSAPP_EVOLUTION_ENABLED=false
WHATSAPP_EVOLUTION_API_URL=
WHATSAPP_EVOLUTION_API_KEY=
WHATSAPP_EVOLUTION_DEFAULT_INSTANCE=default
```

### 3.1. Configurar WhatsApp en services.php

Agregar al archivo `config/services.php`:

```php
'whatsapp' => [
    'enabled' => env('WHATSAPP_EVOLUTION_ENABLED', false),
    'url' => env('WHATSAPP_EVOLUTION_API_URL'),
    'key' => env('WHATSAPP_EVOLUTION_API_KEY'),
    'default_instance' => env('WHATSAPP_EVOLUTION_DEFAULT_INSTANCE', 'default'),
],
```

**Nota**: Si no usas WhatsApp, deja `WHATSAPP_EVOLUTION_ENABLED=false` y el servicio no mostrará advertencias.

## 4. Instalar Laravel Breeze (Autenticación)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Opciones recomendadas:
- Stack: `blade` (Blade with Alpine)
- Dark mode: `yes`
- Testing: `PHPUnit`

## 5. Migrar Base de Datos

```bash
# Agregar campo is_super_admin a users
php artisan make:migration add_is_super_admin_to_users_table
```

Editar la migración creada:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_super_admin')->default(false)->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('is_super_admin');
    });
}
```

Ejecutar migraciones:

```bash
php artisan migrate
```

## 6. Configurar Modelo User

Editar `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ThunderPack\Traits\HasTenants;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasTenants;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }
}
```

**Importante**: El trait `HasTenants` provee:
- `tenants()` - Relación con tenants
- `setCurrentTenant()` - Guardar tenant en sesión
- `currentTenant()` - Obtener tenant actual
- `hasAccessToTenant()` - Verificar acceso
- `isSuperAdmin()` - Verificar si es super admin
- `getRoleInTenant()`, `isOwnerOfTenant()`, etc.

## 7. Crear Middlewares

### 7.1. TenantMiddleware

`app/Http/Middleware/TenantMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Super admins bypass tenant requirement
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has current tenant set
        if (!session()->has('current_tenant_id')) {
            return redirect()->route('thunder-pack.tenant.select');
        }

        // Verify user has access to current tenant
        $currentTenantId = session('current_tenant_id');
        if ($user && !$user->hasAccessToTenant($currentTenantId)) {
            session()->forget('current_tenant_id');
            return redirect()->route('thunder-pack.tenant.select')
                ->with('error', 'No tienes acceso a ese tenant.');
        }

        return $next($request);
    }
}
```

### 7.2. CheckSubscriptionStatus

`app/Http/Middleware/CheckSubscriptionStatus.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ThunderPack\Facades\SubscriptionService;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Super admins bypass subscription check
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Get current tenant from session
        $tenantId = session('current_tenant_id');
        if (!$tenantId) {
            return redirect()->route('thunder-pack.tenant.select');
        }

        // Get tenant model
        $tenant = \ThunderPack\Models\Tenant::find($tenantId);
        if (!$tenant) {
            return redirect()->route('thunder-pack.tenant.select');
        }

        // Check subscription status
        if (!SubscriptionService::isSubscriptionActive($tenant)) {
            return redirect()->route('thunder-pack.subscription.expired');
        }

        return $next($request);
    }
}
```

### 7.3. EnsureSuperAdmin

`app/Http/Middleware/EnsureSuperAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado. Solo super administradores.');
        }

        return $next($request);
    }
}
```

### 7.4. CheckTenantPermission

`app/Http/Middleware/CheckTenantPermission.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantPermission
{
    public function handle(Request $request, Closure $next, string $permission = 'admin'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Super admins bypass permission check
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $tenantId = session('current_tenant_id');
        
        if ($permission === 'admin' && !$user->isAdminOfTenant($tenantId)) {
            abort(403, 'Requiere permisos de administrador en este tenant.');
        }

        if ($permission === 'owner' && !$user->isOwnerOfTenant($tenantId)) {
            abort(403, 'Requiere ser propietario del tenant.');
        }

        return $next($request);
    }
}
```

## 8. Registrar Middlewares

Editar `bootstrap/app.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'tenant.permission' => \App\Http\Middleware\CheckTenantPermission::class,
            'subscription' => \App\Http\Middleware\CheckSubscriptionStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

## 9. Configurar Rutas

Editar `routes/web.php`:

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('welcome');
});

// Dashboard principal (requiere tenant y subscription activa)
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Profile routes (requieren tenant activo)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Aquí agregas tus rutas específicas de la aplicación
});

require __DIR__.'/auth.php';
```

## 10. Copiar Theme de Thunder-Theme (Opcional)

Si quieres usar el tema visual completo de thunder-theme:

```bash
# Desde PowerShell en el directorio del proyecto

# Copiar vistas
Copy-Item "..\thunder-theme\resources\views\welcome.blade.php" "resources\views\welcome.blade.php" -Force
Copy-Item "..\thunder-theme\resources\views\layouts\*" "resources\views\layouts\" -Force -Recurse
Copy-Item "..\thunder-theme\resources\views\auth" "resources\views\auth" -Force -Recurse
Copy-Item "..\thunder-theme\resources\views\profile" "resources\views\profile" -Force -Recurse
Copy-Item "..\thunder-theme\resources\views\profile\partials\*" "resources\views\profile\partials\" -Force
Copy-Item "..\thunder-theme\resources\views\dashboard.blade.php" "resources\views\dashboard.blade.php" -Force

# Copiar configuración Tailwind
Copy-Item "..\thunder-theme\tailwind.config.js" "tailwind.config.js" -Force
Copy-Item "..\thunder-theme\postcss.config.js" "postcss.config.js" -Force

# Copiar assets CSS/JS
Copy-Item "..\thunder-theme\resources\css\app.css" "resources\css\app.css" -Force
Copy-Item "..\thunder-theme\resources\js\app.js" "resources\js\app.js" -Force
```

**Importante**: Los partials de profile incluyen diseño minimalista con:
- Inputs compactos (`text-sm`, `px-3 py-2`)
- Headers sin bordes pesados
- Espaciado reducido (`space-y-4` en lugar de `space-y-6`)
- Textos en español y tipografía consistente

Compilar assets:

```bash
npm install
npm run build
```

## 11. Copiar y Configurar Seeders

### 11.1. Copiar Seeders

```bash
Copy-Item "..\thunder-theme\database\seeders\*" "database\seeders\" -Force
```

### 11.2. Actualizar Namespaces

Editar cada seeder copiado y cambiar los imports:

**PlanSeeder.php**:
```php
use ThunderPack\Models\Plan;
```

**TenantSeeder.php**:
```php
use ThunderPack\Models\Tenant;
```

**UserSeeder.php**:
```php
use ThunderPack\Models\Tenant;
use App\Models\User; // Este sí es de App
```

**SubscriptionSeeder.php**:
```php
use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;
use ThunderPack\Models\Tenant;
```

### 11.3. Ejecutar Seeders

```bash
php artisan db:seed
```

O desde cero:

```bash
php artisan migrate:fresh --seed
```

## 12. Usuarios de Prueba

Los seeders crean estos usuarios:

| Email | Password | Rol | Tenant |
|-------|----------|-----|--------|
| admin@thunder.test | password | Super Admin | - |
| demo@thunder.test | password | Owner | Thunder Demo Company |
| staff@thunder.test | password | Staff | Thunder Demo Company |

## 13. Verificación Final

### 13.1. Limpiar Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 13.2. Probar Funcionalidad

1. **Login**: Ingresa con `demo@thunder.test / password`
2. **Selector de Tenant**: Deberías ver `/tenant/select` para elegir empresa
3. **Dashboard**: Después de seleccionar tenant, accede a `/dashboard`
4. **Super Admin**: Login con `admin@thunder.test` → accede a `/sa/dashboard`

## 14. Rutas Disponibles de Thunder Pack

Thunder Pack registra automáticamente estas rutas:

### Rutas Públicas/Auth
- `/tenant/select` - Selector de tenant
- `/subscription/expired` - Página de suscripción expirada

### Rutas con Middleware `['auth', 'tenant']`
- `/team` - Gestión de equipo
- `/team/invitations` - Invitaciones pendientes

### Rutas Super Admin `['auth', 'superadmin']`
- `/sa/dashboard` - Dashboard de super admin
- `/sa/tenants` - Gestión de tenants
- `/sa/subscriptions` - Gestión de suscripciones
- `/sa/subscriptions/{id}` - Detalle de suscripción

## 15. Facades Disponibles

Thunder Pack expone estos facades:

```php
use ThunderPack\Facades\SubscriptionService;
use ThunderPack\Facades\PlanLimitService;
use ThunderPack\Facades\FeatureGate;

// Ejemplo: Verificar suscripción
if (SubscriptionService::isSubscriptionActive($tenant)) {
    // ...
}

// Ejemplo: Verificar límites
PlanLimitService::check($tenant, 'installations', 5); // Lanza excepción si excede

// Ejemplo: Verificar features
if (FeatureGate::hasFeature($tenant, 'api_access')) {
    // ...
}
```

## 16. Personalización del Plan

Los planes se definen en `database/seeders/PlanSeeder.php`. Cada plan tiene:

```php
'features' => [
    // Límites numéricos
    'max_installations' => 5,
    'max_clients' => 250,
    'max_projects' => 50,
    
    // Feature flags (boolean)
    'api_access' => true,
    'custom_branding' => false,
    
    // Features específicas del proyecto
    'backup_retention_days' => 30,
]
```

## 17. Troubleshooting

### Error: "Call to undefined method setCurrentTenant()"
- Asegúrate de que `User` model use el trait `HasTenants`

### Error: "Tenant middleware not found"
- Verifica que los middlewares estén registrados en `bootstrap/app.php`

### Las rutas de thunder-pack no funcionan
- Confirma que `THUNDER_PACK_ROUTES_ENABLED=true` en `.env`
- Ejecuta `php artisan route:clear`

### No se ven los estilos
- Ejecuta `npm run build`
- Verifica que `@vite(['resources/css/app.css', 'resources/js/app.js'])` esté en los layouts

## 18. Próximos Pasos

Para implementar sistema de licencias desktop:

1. **Agregar campo UUID a Subscriptions** (ver `LICENSING_SYSTEM.md`)
2. **Crear modelos Installation y BackupLog** en el proyecto
3. **Crear API endpoints** en `routes/api.php`
4. **Implementar servicios de licencias** en `app/Services/Licensing/`

---

**Documentación actualizada**: 2026-01-10
**Thunder Pack Version**: dev-main
**Laravel Version**: 12.x
