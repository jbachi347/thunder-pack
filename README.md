# Thunder Pack

[![Latest Version](https://img.shields.io/packagist/v/bachisoft/thunder-pack.svg)](https://packagist.org/packages/bachisoft/thunder-pack)
[![Total Downloads](https://img.shields.io/packagist/dt/bachisoft/thunder-pack.svg)](https://packagist.org/packages/bachisoft/thunder-pack)
[![License](https://img.shields.io/packagist/l/bachisoft/thunder-pack.svg)](https://packagist.org/packages/bachisoft/thunder-pack)

Paquete completo de Laravel para construir aplicaciones SaaS multi-tenant con gestión de suscripciones, sistema flexible de límites, integración con WhatsApp, y panel de Super Admin.

## 🚀 Características

### Multi-Tenancy
- **Tenancy basado en sesión**: Sin bases de datos separadas por tenant
- **Trait BelongsToTenant**: Aislamiento automático de datos por tenant
- **Selector de tenant**: Interfaz para cambiar entre tenants
- **Bypass de super admin**: Los administradores pueden acceder a todos los tenants

### Sistema de Suscripciones
- Gestión completa del ciclo de vida de suscripciones
- Activación manual y renovación
- Seguimiento de eventos de pago
- Notificaciones por email y WhatsApp
- Alertas de expiración (7 días antes)
- Estados: activa, prueba, vencida, cancelada

### Sistema Flexible de Límites
- **Límites numéricos**: `max_clients`, `max_projects`, `api_calls_per_month`
- **Feature flags booleanos**: `custom_branding`, `api_access`
- **Habilitación de módulos**: `whatsapp`, `reports`, `analytics`
- **Rate limiting**: Por día, por mes
- **Overrides personalizados**: Para clientes VIP
- **Tracking de uso**: Historial completo de consumo

### Directivas Blade
```blade
@hasFeature('custom_branding')
    <!-- Mostrar contenido personalizado -->
@endhasFeature

@canUseResource('max_clients', 5)
    <!-- Permitir crear 5 clientes más -->
@endcanUseResource

@hasAnyFeature(['api_access', 'webhooks'])
    <!-- Tiene al menos una característica -->
@endhasAnyFeature
```

### Integración con WhatsApp
- API Evolution integrada
- Múltiples teléfonos por tenant
- Notificaciones automáticas
- Sistema de colas con reintentos (3 intentos: 1m, 3m, 10m)
- Registro de mensajes y estadísticas
- Comando de prueba incluido

### Panel de Super Admin
- Dashboard con métricas clave
- **Gestión completa de tenants** (crear, editar, ver, límites personalizados)
- **Gestión completa de usuarios** (crear, editar, asignar tenants, cambiar roles)
- Gestión de suscripciones con renovación rápida
- Gestión de planes con límites configurables
- Configuración de WhatsApp por tenant
- Historial de uso y eventos
- Interfaz de tablas compactas y minimalistas

### Gestión de Equipos
- Invitaciones por email
- Control de acceso basado en roles
- Límites de staff por plan
- Notificaciones automáticas

## 📋 Requisitos

- PHP 8.2 o superior
- Laravel 12.0 o superior
- Livewire 3.6.4 o superior

## 📦 Instalación

### 1. Instalar vía Composer

```bash
composer require bachisoft/thunder-pack
```

### 2. Ejecutar el comando de instalación

```bash
php artisan thunder-pack:install
```

Este comando:
- Publica las migraciones
- Publica la configuración
- Publica las vistas (opcional)
- Ejecuta las migraciones
- Seed los planes por defecto
- Crea el primer super admin

### 3. Configurar el modelo User

Agrega el trait `HasTenants` a tu modelo User:

```php
use ThunderPack\Traits\HasTenants;

class User extends Authenticatable
{
    use HasTenants;
    
    // ... resto del modelo
}
```

### 4. Registrar middleware (Laravel 12)

El paquete registra automáticamente los middleware con aliases:
- `tenant` - Validación de acceso al tenant
- `subscription` - Verificación de suscripción activa
- `superadmin` - Restricción a super admins
- `tenant.permission` - Control de permisos dentro del tenant

Aplícalos en tus rutas:

```php
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::middleware(['auth', 'superadmin'])->prefix('sa')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index']);
});
```

### 5. Configurar WhatsApp (Opcional)

Agrega las credenciales en tu `.env`:

```env
WHATSAPP_EVOLUTION_ENABLED=true
WHATSAPP_EVOLUTION_API_URL=https://api.evolutionapi.com
WHATSAPP_EVOLUTION_API_KEY=tu_api_key_aqui
WHATSAPP_EVOLUTION_DEFAULT_INSTANCE=default
```

## 🔧 Configuración

Publica el archivo de configuración:

```bash
php artisan vendor:publish --tag=thunder-pack-config
```

El archivo `config/thunder-pack.php` permite personalizar:

```php
return [
    'table_prefix' => '', // Prefijo para tablas
    
    'models' => [
        'tenant' => \ThunderPack\Models\Tenant::class,
        'plan' => \ThunderPack\Models\Plan::class,
        // Sobrescribe modelos según necesites
    ],
    
    'routes' => [
        'enabled' => true,
        'prefix' => '',
        'middleware' => ['web', 'auth'],
        'super_admin_prefix' => 'sa',
    ],
    
    'features' => [
        'whatsapp' => true,
        'team_invitations' => true,
        'super_admin_panel' => true,
    ],
    
    'cache' => [
        'ttl' => [
            'limits' => 300,    // 5 minutos
            'features' => 600,  // 10 minutos
        ],
    ],
];
```

## 📖 Uso

### Verificar características

```php
use ThunderPack\Facades\FeatureGate;

// Verificar si el tenant tiene una característica
if (FeatureGate::has('api_access')) {
    // Permitir acceso a la API
}

// Verificar límite numérico
if (FeatureGate::can('max_clients', 10)) {
    // Puede crear 10 clientes más
}

// Obtener valor de límite
$maxProjects = FeatureGate::get('max_projects', 5); // 5 es el valor por defecto
```

### Registrar uso de recursos

```php
use ThunderPack\Facades\PlanLimitService;

// Registrar uso de recurso
PlanLimitService::recordUsage('api_calls', 1);

// Obtener uso actual
$usage = PlanLimitService::getCurrentUsage('api_calls', 'month');
```

### Gestionar suscripciones

```php
use ThunderPack\Facades\SubscriptionService;

// Activar suscripción manualmente
SubscriptionService::activateManual($tenant, $plan, $days = 30);

// Verificar estado
if (SubscriptionService::isSubscriptionActive($tenant)) {
    // Suscripción activa
}

// Obtener días restantes
$daysLeft = SubscriptionService::getDaysUntilExpiration($tenant);
```

### Enviar notificaciones WhatsApp

```php
use ThunderPack\Services\WhatsAppService;
use ThunderPack\Jobs\SendWhatsAppNotificationJob;

// Enviar vía Job (recomendado)
SendWhatsAppNotificationJob::dispatch(
    $tenant,
    'subscription_activated',
    ['plan_name' => 'Pro']
);

// O directamente
$whatsapp = app(WhatsAppService::class);
$whatsapp->sendNotification($tenant, 'payment_received', ['amount' => 500]);
```

## 🎨 Vistas Personalizables

Publica las vistas para personalizarlas:

```bash
php artisan vendor:publish --tag=thunder-pack-views
```

Las vistas se copiarán a `resources/views/vendor/thunder-pack/`

## 🧪 Testing

```bash
composer test
```

## 📚 Documentación Adicional

- [Sistema de Límites Flexibles](docs/FLEXIBLE_LIMITS_SYSTEM.md)
- [Notificaciones WhatsApp](docs/WHATSAPP_NOTIFICATIONS.md)
- [Guía de Instalación Completa](docs/INSTALLATION.md)
- [Referencia Rápida](docs/QUICK_REFERENCE.md)

## 🔄 Actualización

```bash
composer update bachisoft/thunder-pack
php artisan migrate
php artisan view:clear
```

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un issue primero para discutir cambios mayores.

## 📄 Licencia

MIT License. Ver [LICENSE](LICENSE) para más detalles.

## 🙏 Créditos

Desarrollado por [Bachisoft](https://bachisoft.com)

## 🐛 Soporte

Para reportar bugs o solicitar características, por favor usa [GitHub Issues](https://github.com/bachisoft/thunder-pack/issues).
