# 📦 Guía de Instalación Thunder Pack

**Thunder Pack** es un paquete Laravel completo para construir aplicaciones SaaS multi-tenant con gestión de suscripciones, límites flexibles, equipos, y notificaciones WhatsApp.

---

## 📋 Requisitos

### Requisitos del Sistema

- **PHP**: 8.2 o superior
- **Laravel**: 12.x o superior
- **Livewire**: 3.6.4 o superior
- **Composer**: 2.x
- **Base de datos**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

### Extensiones PHP Requeridas

```
- BCMath
- Ctype
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
```

### Dependencias Laravel

- Laravel Breeze o similar (autenticación)
- Livewire 3.x (se instala automáticamente con el paquete)

---

## 🚀 Instalación

### Paso 1: Instalar vía Composer

```bash
composer require thunder-pack/thunder-pack
```

### Paso 2: Ejecutar el Comando de Instalación

Thunder Pack incluye un comando interactivo de instalación que guía el proceso completo:

```bash
php artisan thunder-pack:install
```

El comando te preguntará:

1. **¿Publicar migraciones?** (Recomendado: Sí)
2. **¿Publicar configuración?** (Recomendado: Sí)
3. **¿Publicar vistas?** (Opcional: Solo si quieres personalizar)
4. **¿Ejecutar migraciones?** (Recomendado: Sí)
5. **¿Seedear planes por defecto?** (Recomendado: Sí)
6. **¿Crear super admin?** (Opcional: Puedes hacerlo después)

#### Opciones del Comando

```bash
# Instalación forzada (sobrescribe archivos existentes)
php artisan thunder-pack:install --force

# Saltar migraciones
php artisan thunder-pack:install --skip-migrations

# Saltar seeding de planes
php artisan thunder-pack:install --skip-seed
```

### Paso 3: Verificar Instalación

Después de ejecutar el comando, verifica que los siguientes archivos existan:

```
config/thunder-pack.php                    # Configuración del paquete
database/migrations/2026_*_*.php           # Migraciones del paquete
```

---

## ⚙️ Configuración

### 1. Configurar Modelo User

Agrega el trait `HasTenants` a tu modelo `User`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use ThunderPack\Traits\HasTenants;

class User extends Authenticatable
{
    use HasTenants;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin', // Agregar este campo
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean', // Agregar este cast
    ];
}
```

### 2. Agregar Columna `is_super_admin` a Users

Si aún no tienes esta columna en tu tabla `users`, crea una migración:

```bash
php artisan make:migration add_is_super_admin_to_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
```

Ejecuta la migración:

```bash
php artisan migrate
```

### 3. Variables de Entorno

Configura tu archivo `.env`:

```env
# Thunder Pack - Configuración General
THUNDER_PACK_ROUTES_ENABLED=true
THUNDER_PACK_SA_PREFIX=sa

# Thunder Pack - Features
THUNDER_PACK_WHATSAPP_ENABLED=false
THUNDER_PACK_TEAM_INVITATIONS_ENABLED=true
THUNDER_PACK_SA_PANEL_ENABLED=true
THUNDER_PACK_USAGE_TRACKING_ENABLED=true

# Thunder Pack - Caché (en segundos)
THUNDER_PACK_CACHE_LIMITS_TTL=300       # 5 minutos
THUNDER_PACK_CACHE_FEATURES_TTL=600     # 10 minutos

# Thunder Pack - Suscripciones
THUNDER_PACK_EXPIRING_THRESHOLD_DAYS=7
THUNDER_PACK_DEFAULT_TRIAL_DAYS=0
THUNDER_PACK_GRACE_PERIOD_DAYS=3

# WhatsApp Evolution API (Opcional)
WHATSAPP_EVOLUTION_ENABLED=false
WHATSAPP_EVOLUTION_API_URL=https://tu-api.com
WHATSAPP_EVOLUTION_API_KEY=tu_api_key
WHATSAPP_EVOLUTION_DEFAULT_INSTANCE=default
```

### 4. Configurar Colas (Recomendado)

Thunder Pack usa colas para envío de notificaciones. Configura tu queue driver en `.env`:

```env
QUEUE_CONNECTION=database
```

Ejecuta el worker de colas:

```bash
php artisan queue:work
```

O para desarrollo, usa:

```bash
php artisan queue:listen
```

---

## 🗄️ Base de Datos

### Tablas Creadas

El paquete crea las siguientes tablas:

#### Core Multi-Tenancy
- `tenants` - Organizaciones/empresas de los clientes
- `tenant_user` - Relación usuarios ↔ tenants (pivot)
- `team_invitations` - Invitaciones pendientes a tenants

#### Suscripciones
- `plans` - Planes de suscripción con features JSON
- `subscriptions` - Suscripciones activas de tenants
- `payment_events` - Historial de pagos
- `subscription_notifications` - Notificaciones enviadas

#### Sistema de Límites
- `tenant_limit_overrides` - Límites personalizados por tenant
- `usage_events` - Historial de consumo de recursos

#### WhatsApp (Opcional)
- `tenant_whatsapp_phones` - Números WhatsApp por tenant
- `whatsapp_message_logs` - Historial de mensajes enviados

### Seedear Planes por Defecto

Si saltaste el seeding durante la instalación, puedes ejecutarlo ahora:

```bash
php artisan db:seed --class=ThunderPack\\Database\\Seeders\\PlanSeeder
```

Esto crea 4 planes por defecto:
- **Free**: Plan gratuito básico
- **Starter**: Plan inicial con límites bajos
- **Professional**: Plan intermedio para negocios
- **Enterprise**: Plan empresarial con límites altos

---

## 🛣️ Configuración de Rutas

### Rutas de Tenants

Aplica los middleware en tus rutas protegidas:

```php
// routes/web.php

use Illuminate\Support\Facades\Route;

// Rutas de Tenants (requieren autenticación + tenant + suscripción activa)
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Selector de tenant
    Route::get('/tenant/select', function () {
        return view('tenant-select');
    })->name('tenant.select');

    // Más rutas de tu aplicación...
});

// Rutas de Super Admin (solo usuarios con is_super_admin = true)
Route::middleware(['auth', 'superadmin'])->prefix('sa')->name('sa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('super-admin.dashboard');
    })->name('dashboard');

    // Thunder Pack ya registra sus propias rutas SA automáticamente
    // Puedes agregar rutas custom aquí
});
```

### Orden de Middleware (IMPORTANTE)

El orden de los middleware es crítico:

1. **`auth`** - Usuario debe estar autenticado
2. **`tenant`** - Validar/seleccionar tenant
3. **`subscription`** - Verificar suscripción activa

```php
// ✅ CORRECTO
Route::middleware(['auth', 'tenant', 'subscription'])->group(...)

// ❌ INCORRECTO (orden incorrecto)
Route::middleware(['tenant', 'auth', 'subscription'])->group(...)
```

### Rutas Incluidas del Paquete

Thunder Pack registra automáticamente estas rutas:

#### Rutas de Tenants
- `GET /tenant/select` - Selector de tenant
- Componentes Livewire para equipos

#### Rutas de Super Admin (prefijo `/sa`)
- `GET /sa/dashboard` - Dashboard principal
- `GET /sa/tenants` - Lista de tenants
- `GET /sa/tenants/{tenant}` - Detalles de tenant
- `GET /sa/subscriptions` - Lista de suscripciones
- `GET /sa/subscriptions/{subscription}` - Detalles de suscripción

---

## 👤 Crear Super Admin

### Método 1: Durante la Instalación

El comando `thunder-pack:install` ofrece crear un super admin interactivamente.

### Método 2: Vía Tinker

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Admin Principal',
    'email' => 'admin@tuapp.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now(),
    'is_super_admin' => true,
]);
```

### Método 3: Actualizar Usuario Existente

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'tu@email.com')->first();
$user->is_super_admin = true;
$user->save();
```

---

## 🏢 Crear Primer Tenant

### Vía Tinker

```bash
php artisan tinker
```

```php
use ThunderPack\Models\Tenant;
use ThunderPack\Models\Plan;
use ThunderPack\Models\Subscription;

// 1. Crear tenant
$tenant = Tenant::create([
    'name' => 'Mi Empresa',
    'slug' => 'mi-empresa',
    'email' => 'contacto@miempresa.com',
]);

// 2. Obtener plan (por ejemplo, el Free)
$plan = Plan::where('code', 'free')->first();

// 3. Crear suscripción
$subscription = Subscription::create([
    'tenant_id' => $tenant->id,
    'plan_id' => $plan->id,
    'status' => 'active',
    'starts_at' => now(),
    'expires_at' => now()->addYear(),
]);

// 4. Asociar usuario al tenant
$user = App\Models\User::first();
$tenant->users()->attach($user->id, ['role' => 'owner']);

// 5. Establecer tenant en sesión
session(['current_tenant_id' => $tenant->id]);
```

### Vía Super Admin Panel

1. Inicia sesión como super admin
2. Navega a `/sa/tenants`
3. Clic en "Nuevo Tenant"
4. Completa el formulario
5. Asigna un plan y crea la suscripción

---

## 🧪 Probar la Instalación

### 1. Verificar Middleware

Crea una ruta de prueba:

```php
Route::middleware(['auth', 'tenant', 'subscription'])->get('/test', function () {
    $tenant = ThunderPack\Models\Tenant::find(session('current_tenant_id'));
    
    return response()->json([
        'tenant' => $tenant->name,
        'plan' => $tenant->activeSubscription->plan->name,
        'subscription_status' => $tenant->activeSubscription->status,
    ]);
});
```

Accede a `/test` y deberías ver los datos del tenant.

### 2. Probar Límites

```php
use ThunderPack\Services\PlanLimitService;

Route::get('/test-limits', function () {
    $tenant = ThunderPack\Models\Tenant::find(session('current_tenant_id'));
    
    return [
        'max_clients' => PlanLimitService::getLimit($tenant, 'max_clients'),
        'current_usage' => PlanLimitService::getCurrentUsage($tenant, 'max_clients'),
        'can_add_one' => PlanLimitService::can($tenant, 'max_clients', 1),
    ];
})->middleware(['auth', 'tenant']);
```

### 3. Probar Features

```php
use ThunderPack\Services\FeatureGate;

Route::get('/test-features', function () {
    $tenant = ThunderPack\Models\Tenant::find(session('current_tenant_id'));
    
    return [
        'has_whatsapp' => FeatureGate::allows($tenant, 'whatsapp'),
        'has_api' => FeatureGate::allows($tenant, 'api_access'),
        'modules' => FeatureGate::getModules($tenant),
    ];
})->middleware(['auth', 'tenant']);
```

### 4. Probar Panel Super Admin

1. Inicia sesión como super admin
2. Navega a `/sa/dashboard`
3. Deberías ver:
   - Total de tenants
   - Suscripciones activas
   - Ingresos totales
   - Lista de tenants recientes

### 5. Probar Selector de Tenant

1. Inicia sesión como usuario normal
2. Navega a `/tenant/select`
3. Deberías ver los tenants a los que tienes acceso
4. Selecciona uno y deberías ser redirigido al dashboard

---

## 🔧 Comandos Útiles

### Verificar Límites de Staff

```bash
php artisan limits:check-staff
```

Revisa todos los tenants y notifica si alguno ha excedido el límite de personal.

### Probar Integración WhatsApp

```bash
php artisan whatsapp:test {phone_id}
```

Envía un mensaje de prueba a un teléfono WhatsApp configurado.

### Limpiar Caché

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🐛 Troubleshooting

### Error: "Tenant middleware redirecting infinitely"

**Causa**: No tienes tenants asociados a tu usuario.

**Solución**:
```php
// En tinker
$user = App\Models\User::first();
$tenant = ThunderPack\Models\Tenant::first();
$tenant->users()->attach($user->id, ['role' => 'owner']);
```

### Error: "Subscription middleware blocking access"

**Causa**: El tenant no tiene suscripción activa.

**Solución**:
```php
// En tinker
use ThunderPack\Services\SubscriptionService;

$tenant = ThunderPack\Models\Tenant::first();
$plan = ThunderPack\Models\Plan::where('code', 'free')->first();

SubscriptionService::activateManual($tenant, $plan, 365);
```

### Error: "Class 'Livewire\\Livewire' not found"

**Causa**: Livewire no está instalado.

**Solución**:
```bash
composer require livewire/livewire:^3.6
```

### Error: "Session data missing after page reload"

**Causa**: Configuración de sesión incorrecta.

**Solución**: En `.env`, verifica:
```env
SESSION_DRIVER=database  # o file, redis
SESSION_LIFETIME=120
```

Ejecuta:
```bash
php artisan session:table
php artisan migrate
```

### Error: "Views not found"

**Causa**: Las vistas del paquete no están cargadas.

**Solución**:
```bash
php artisan view:clear
php artisan config:clear

# Si el problema persiste, publica las vistas:
php artisan vendor:publish --tag=thunder-pack-views
```

### WhatsApp no envía mensajes

**Soluciones**:

1. Verificar configuración en `.env`:
```env
WHATSAPP_EVOLUTION_ENABLED=true
WHATSAPP_EVOLUTION_API_URL=https://tu-api.com
WHATSAPP_EVOLUTION_API_KEY=tu_api_key
```

2. Limpiar cache:
```bash
php artisan config:clear
```

3. Verificar que la cola esté corriendo:
```bash
php artisan queue:work
```

4. Revisar logs:
```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Configuración Avanzada

### Personalizar Modelos

Puedes extender los modelos del paquete en `config/thunder-pack.php`:

```php
'models' => [
    'tenant' => \App\Models\Tenant::class,
    'plan' => \ThunderPack\Models\Plan::class,
    'subscription' => \ThunderPack\Models\Subscription::class,
    // ... otros modelos
],
```

Luego, crea tu propio modelo:

```php
<?php

namespace App\Models;

use ThunderPack\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    // Agrega métodos o relaciones personalizadas
    public function customField()
    {
        return $this->hasMany(CustomField::class);
    }
}
```

### Personalizar Límites por Defecto

En `config/thunder-pack.php`:

```php
'default_limits' => [
    'staff_limit' => 1,
    'max_clients' => 100,
    'max_projects' => 10,
    'storage_quota_mb' => 500,
    'api_calls_per_month' => 5000,
],
```

### Personalizar Roles de Equipo

En `config/thunder-pack.php`:

```php
'team' => [
    'roles' => [
        'owner' => 'Propietario',
        'admin' => 'Administrador',
        'staff' => 'Personal',
        'viewer' => 'Observador', // Agregar rol custom
    ],
    'permissions' => [
        'viewer' => [
            'manage_team' => false,
            'manage_billing' => false,
            'manage_settings' => false,
            'view_reports' => true,
        ],
    ],
],
```

### Configurar Prefijo de Tablas

Si necesitas un prefijo para las tablas del paquete:

```env
THUNDER_PACK_TABLE_PREFIX=tp_
```

Esto creará tablas como: `tp_tenants`, `tp_plans`, etc.

---

## 📚 Próximos Pasos

### 1. Explora la Documentación

- **[FLEXIBLE_LIMITS_SYSTEM.md](FLEXIBLE_LIMITS_SYSTEM.md)** - Sistema completo de límites
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Referencia rápida de APIs
- **[WHATSAPP_NOTIFICATIONS.md](WHATSAPP_NOTIFICATIONS.md)** - Integración WhatsApp
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Resumen de implementación

### 2. Integra en tu Aplicación

- Agrega validaciones de límites en tus controladores
- Implementa feature gates en tu UI
- Crea dashboards de uso para tus usuarios
- Configura notificaciones WhatsApp (opcional)

### 3. Personaliza la UI

Si publicaste las vistas, puedes personalizarlas en:

```
resources/views/vendor/thunder-pack/
```

### 4. Configura Notificaciones

- Email: Personaliza plantillas en `resources/views/vendor/thunder-pack/emails/`
- WhatsApp: Configura Evolution API y agrega números de teléfono

### 5. Implementa tu Lógica de Negocio

Usa los servicios del paquete en tus controladores:

```php
use ThunderPack\Services\{PlanLimitService, FeatureGate, SubscriptionService};

class ClientController extends Controller
{
    public function store(Request $request)
    {
        $tenant = Tenant::find(session('current_tenant_id'));
        
        // Validar límite
        PlanLimitService::check($tenant, 'max_clients', 1);
        
        // Crear cliente
        $client = $tenant->clients()->create($request->all());
        
        // Registrar uso
        PlanLimitService::recordUsage($tenant, 'clients', 1, 'create');
        
        return redirect()->route('clients.index');
    }
}
```

---

## 🎯 Ejemplos de Uso

### Verificar Límites en Controlador

```php
use ThunderPack\Services\PlanLimitService;

public function create()
{
    $tenant = Tenant::find(session('current_tenant_id'));
    
    if (!PlanLimitService::can($tenant, 'max_projects', 1)) {
        return redirect()->back()
            ->with('error', 'Has alcanzado el límite de proyectos. Actualiza tu plan.');
    }
    
    return view('projects.create');
}
```

### Feature Gate en Blade

```blade
@hasFeature('whatsapp')
    <div class="whatsapp-section">
        <h3>Integración WhatsApp</h3>
        <!-- Contenido del módulo -->
    </div>
@else
    <div class="upgrade-banner">
        <p>Actualiza tu plan para acceder a WhatsApp</p>
        <a href="{{ route('pricing') }}" class="btn">Ver Planes</a>
    </div>
@endhasFeature
```

### Dashboard de Uso

```php
use ThunderPack\Services\PlanLimitService;

public function dashboard()
{
    $tenant = Tenant::find(session('current_tenant_id'));
    
    $usage = [
        'clients' => [
            'limit' => PlanLimitService::getLimit($tenant, 'max_clients'),
            'current' => PlanLimitService::getCurrentUsage($tenant, 'max_clients'),
            'percentage' => PlanLimitService::getUsagePercentage($tenant, 'max_clients'),
        ],
        'projects' => [
            'limit' => PlanLimitService::getLimit($tenant, 'max_projects'),
            'current' => PlanLimitService::getCurrentUsage($tenant, 'max_projects'),
            'percentage' => PlanLimitService::getUsagePercentage($tenant, 'max_projects'),
        ],
    ];
    
    return view('dashboard', compact('usage'));
}
```

---

## 🔐 Seguridad

### Consideraciones de Seguridad

1. **No almacenar claves API en código**: Usa siempre variables de entorno
2. **Validar tenant ownership**: El middleware `tenant` lo hace automáticamente
3. **Super Admin bypass**: Los super admins pueden ver todos los datos
4. **Rate limiting**: Considera agregar throttle a rutas públicas

### Recomendaciones

```php
// Validar que el recurso pertenece al tenant actual
$project = Project::where('tenant_id', session('current_tenant_id'))
    ->findOrFail($id);

// Usar policy para autorización
Gate::define('update-project', function ($user, $project) {
    return $user->tenants()
        ->where('tenant_id', $project->tenant_id)
        ->exists();
});
```

---

## 📞 Soporte y Recursos

### Documentación

- **GitHub**: [thunder-pack/thunder-pack](https://github.com/thunder-pack/thunder-pack)
- **Documentación completa**: `/docs` en el paquete

### Reportar Issues

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue en GitHub con:
   - Versión de Thunder Pack
   - Versión de Laravel y PHP
   - Pasos para reproducir
   - Error completo (si aplica)

### Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Changelog

### v1.0.0 (2026-01-10)

**Características Iniciales:**
- ✅ Sistema multi-tenant con gestión de equipos
- ✅ Gestión de suscripciones con múltiples planes
- ✅ Sistema de límites flexible (JSON-based)
- ✅ Feature gates para control de funcionalidades
- ✅ Panel Super Admin completo
- ✅ Integración WhatsApp Evolution API
- ✅ Middleware para tenant y subscription
- ✅ Blade directives (@hasFeature, @canUseResource)
- ✅ Notificaciones email y WhatsApp
- ✅ Historial de uso y eventos
- ✅ Overrides personalizados por tenant

---

## 📄 Licencia

Thunder Pack es software de código abierto licenciado bajo [MIT license](LICENSE).

---

**¡Instalación completa!** 🎉

Para comenzar a usar Thunder Pack en tu aplicación, revisa la documentación adicional en:
- [FLEXIBLE_LIMITS_SYSTEM.md](FLEXIBLE_LIMITS_SYSTEM.md)
- [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- [WHATSAPP_NOTIFICATIONS.md](WHATSAPP_NOTIFICATIONS.md)
