# Integración Lemon Squeezy - Estado Actual

**Fecha**: 14 de enero, 2026  
**Versión**: Thunder-Pack con API directa de Lemon Squeezy (sin paquete oficial por compatibilidad Laravel 12/Windows)

---

## ✅ COMPLETADO (Core Funcional)

### 1. **Abstracción de Payment Gateways** ✓
**Archivos**:
- `thunder-pack/src/Services/Gateways/PaymentGatewayInterface.php`
- `thunder-pack/src/Services/Gateways/ManualGateway.php`
- `thunder-pack/src/Services/Gateways/LemonSqueezyGateway.php`

**Funcionalidades**:
- Interface común para todos los payment gateways
- ManualGateway: Pagos manuales (lógica existente encapsulada)
- LemonSqueezyGateway: Implementación completa con API directa de Lemon Squeezy
  - Creación de checkout URLs
  - Manejo de 12 tipos de webhooks
  - Soporte para upgrades/downgrades automáticos
  - Verificación de firmas de webhooks
  - Customer portal URLs

### 2. **Controlador de Webhooks** ✓
**Archivo**: `thunder-pack/src/Http/Controllers/WebhookController.php`

**Funcionalidades**:
- Recibe webhooks de Lemon Squeezy en `/webhooks/lemon-squeezy`
- Verifica firma con signing secret
- Delega procesamiento al gateway correspondiente
- Logging completo de eventos

### 3. **Migraciones de Base de Datos** ✓
**Archivos**:
- `thunder-pack/src/Database/Migrations/2026_01_14_120000_add_lemon_squeezy_fields_to_plans.php`
- `thunder-pack/src/Database/Migrations/2026_01_14_120001_add_billing_fields_to_subscriptions.php`

**Cambios en `plans`**:
- `lemon_monthly_variant_id` (string, nullable)
- `lemon_yearly_variant_id` (string, nullable)
- `yearly_price_cents` (int, nullable)

**Cambios en `subscriptions`**:
- `billing_cycle` (enum: monthly, yearly, default: monthly)
- `next_billing_date` (timestamp, nullable) - **FIX CRÍTICO**: Campo usado en código pero faltaba migración

### 4. **Configuración** ✓
**Archivo**: `thunder-pack/config/thunder-pack.php`

**Sección agregada**:
```php
'lemon_squeezy' => [
    'api_key' => env('LEMON_SQUEEZY_API_KEY'),
    'store_id' => env('LEMON_SQUEEZY_STORE_ID'),
    'signing_secret' => env('LEMON_SQUEEZY_SIGNING_SECRET'),
],
```

### 5. **Modelos Actualizados** ✓
**Plan.php**:
- Agregados campos a `$fillable`: `yearly_price_cents`, `lemon_monthly_variant_id`, `lemon_yearly_variant_id`
- Métodos nuevos:
  - `getYearlyPrice()`: Accessor para precio anual
  - `getLemonVariantId($billingCycle)`: Obtener variant ID por ciclo
  - `hasLemonSqueezyIntegration()`: Verificar si tiene Lemon Squeezy configurado

**Subscription.php**:
- Agregados a `$fillable`: `billing_cycle`, `next_billing_date`
- Agregados a `$casts`: `next_billing_date` => 'datetime'

### 6. **SubscriptionService Extendido** ✓
**Archivo**: `thunder-pack/src/Services/SubscriptionService.php`

**Métodos agregados**:
- `getGateway($provider)`: Resuelve gateway por nombre (manual, lemon_squeezy)
- `createCheckout($tenant, $plan, $provider, $billingCycle)`: Genera checkout URL
- `clearNotifications()` y `sendActivationEmail()` ahora públicos para gateways

### 7. **ServiceProvider Registrado** ✓
**Archivo**: `thunder-pack/src/ThunderPackServiceProvider.php`

**Servicios registrados**:
```php
$this->app->singleton(ManualGateway::class, ...);
$this->app->singleton(LemonSqueezyGateway::class, ...);
```

### 8. **Rutas Registradas** ✓
**Archivo**: `thunder-pack/routes/thunder-pack.php`

**Ruta agregada**:
```php
Route::post('/webhooks/lemon-squeezy', [WebhookController::class, 'lemonSqueezy'])
    ->name('thunder-pack.webhooks.lemon-squeezy');
```

### 9. **Componente ChoosePlan** ✓
**Archivos**:
- `thunder-pack/src/Livewire/ChoosePlan.php`
- `thunder-pack/resources/views/livewire/choose-plan.blade.php`

**Funcionalidades**:
- Lista todos los planes disponibles
- Toggle entre monthly/yearly con indicador de ahorro
- Botones de suscripción que generan checkout URLs
- Manejo de errores
- UI responsive con dark mode

---

## ⏳ PENDIENTE

### 1. **CSRF Protection** ⚠️ CRÍTICO
**Acción requerida**: Excluir webhook de CSRF en aplicaciones que usen Thunder-Pack

**Laravel 12** (en `bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhooks/*',
    ]);
})
```

**O en Middleware tradicional** (`app/Http/Middleware/VerifyCsrfToken.php`):
```php
protected $except = [
    'webhooks/*',
];
```

### 2. **Registro de Componente Livewire**
**Archivo**: `thunder-pack/src/ThunderPackServiceProvider.php`

Agregar en método `registerLivewireComponents()`:
```php
Livewire::component('thunder-pack::choose-plan', \ThunderPack\Livewire\ChoosePlan::class);
```

### 3. **Ruta de Selección de Planes**
**Archivo**: `thunder-pack/routes/thunder-pack.php`

Agregar:
```php
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/plans', \ThunderPack\Livewire\ChoosePlan::class)
        ->name('thunder-pack.plans.choose');
});
```

### 4. **Panel de Testing en Custody** (Opcional pero recomendado)
**Componente**: `custody/app/Livewire/SuperAdmin/LemonSqueezyTest.php`

**Funcionalidades sugeridas**:
- Sección "Configuración": Verificar API key, listar stores
- Sección "Sincronizar Planes": Listar variants de Lemon, botón para asociar variant_id a Plan local
- Sección "Crear Checkout": Selector de tenant + plan, generar URL de prueba
- Sección "Ver Webhooks": Últimos 10 `payment_events` con `provider='lemon_squeezy'`
- Sección "Simular Eventos": Formulario para disparar webhook manualmente

### 5. **Botón Customer Portal en License.php** (Opcional pero recomendado)
**Archivo**: `custody/app/Livewire/Dashboard/License.php`

**Lógica sugerida**:
```php
public function openCustomerPortal()
{
    $tenant = session('current_tenant');
    $subscription = $tenant->latestSubscription();

    if ($subscription && $subscription->provider === 'lemon_squeezy' && $subscription->provider_customer_id) {
        $gateway = app(\ThunderPack\Services\Gateways\LemonSqueezyGateway::class);
        $portalUrl = $gateway->getCustomerPortalUrl($subscription->provider_customer_id);

        if ($portalUrl) {
            return redirect($portalUrl);
        }
    }

    session()->flash('error', 'No se pudo abrir el portal de cliente.');
}
```

### 6. **Documentación Completa**
**Archivo sugerido**: `thunder-pack/docs/LEMON_SQUEEZY_SETUP.md`

**Secciones**:
1. Crear productos en Lemon Squeezy dashboard
2. Copiar variant IDs (monthly y yearly)
3. Configurar planes en Thunder-Pack SuperAdmin
4. Configurar webhook en Lemon Squeezy apuntando a `https://tu-app.com/webhooks/lemon-squeezy`
5. Configurar variables de entorno
6. Probar en panel de testing de Custody
7. Troubleshooting común

---

## 🔧 SETUP REQUERIDO

### Variables de Entorno
Agregar a `.env` de aplicaciones que usen Thunder-Pack:

```env
# Lemon Squeezy Configuration
LEMON_SQUEEZY_API_KEY=your-api-key-here
LEMON_SQUEEZY_STORE_ID=your-store-id-here
LEMON_SQUEEZY_SIGNING_SECRET=your-signing-secret-here
```

### Obtener Credenciales

1. **API Key**:
   - Ir a https://app.lemonsqueezy.com/settings/api
   - Crear nueva API key en modo "testing" para desarrollo
   - Copiar key y guardar en `.env`

2. **Store ID**:
   - Ir a https://app.lemonsqueezy.com/settings/stores
   - Copiar el número después del `#` (ej: si es `#12345`, usar `12345`)

3. **Signing Secret**:
   - Ir a https://app.lemonsqueezy.com/settings/webhooks
   - Crear webhook apuntando a `https://tu-app.com/webhooks/lemon-squeezy`
   - Seleccionar TODOS los event types
   - Copiar signing secret

### Sincronización Manual de Planes

1. **En Lemon Squeezy Dashboard**:
   - Crear productos (ej: "Plan Basic", "Plan Pro")
   - Para cada producto, crear 2 variants:
     - Variant 1: Monthly ($XX/mes)
     - Variant 2: Yearly ($XX/año)
   - Copiar los "Variant IDs" de cada uno

2. **En Thunder-Pack SuperAdmin** (o directamente en BD):
   - Editar cada plan en tabla `plans`
   - Pegar `lemon_monthly_variant_id` con ID del variant mensual
   - Pegar `lemon_yearly_variant_id` con ID del variant anual
   - Establecer `yearly_price_cents` (ej: `119900` para $1,199.00/año)

---

## 📊 WEBHOOKS SOPORTADOS

| Evento Lemon Squeezy | Acción en Thunder-Pack |
|----------------------|------------------------|
| `subscription_created` | Crea `Subscription` con provider='lemon_squeezy', envía email de activación |
| `subscription_updated` | Actualiza status, ends_at, next_billing_date |
| `subscription_cancelled` | Status='canceled', establece ends_at |
| `subscription_resumed` | Status='active', actualiza ends_at |
| `subscription_expired` | Status='canceled' |
| `subscription_paused` | Status='paused' |
| `subscription_unpaused` | Status='active' |
| `subscription_payment_success` | Registra en `payment_events`, extiende ends_at |
| `subscription_payment_failed` | Status='past_due', registra en `payment_events` |
| `subscription_payment_recovered` | Status='active' |
| `order_created` | Registra en `payment_events` |
| `order_refunded` | Registra en `payment_events` (sin cancelar suscripción) |

---

## 🎯 FLUJO DE CHECKOUT

1. Usuario hace clic en "Suscribirse" en `/plans`
2. `ChoosePlan` llama a `SubscriptionService::createCheckout()`
3. `LemonSqueezyGateway` hace POST a API de Lemon Squeezy con:
   - Store ID
   - Variant ID (según billing cycle)
   - Custom data: `tenant_id`, `plan_id`, `billing_cycle`
4. API devuelve checkout URL
5. Usuario es redirigido a Lemon Squeezy para pago
6. Lemon Squeezy envía webhook `subscription_created`
7. `WebhookController` verifica firma y delega a `LemonSqueezyGateway`
8. Gateway crea `Subscription` en BD con datos del webhook
9. Usuario recibe email de confirmación
10. Usuario puede acceder a dashboard con suscripción activa

---

## 🚀 PRÓXIMOS PASOS

1. ✅ Completar CSRF exclusion en aplicaciones
2. ✅ Registrar componente Livewire `ChoosePlan`
3. ✅ Agregar ruta `/plans` en aplicaciones
4. ⚠️ Ejecutar migraciones en Thunder-Pack y aplicaciones:
   ```bash
   php artisan migrate
   ```
5. ⚠️ Sincronizar planes con Lemon Squeezy (agregar variant IDs manualmente)
6. ⚠️ Configurar webhook en Lemon Squeezy
7. 🔧 (Opcional) Crear panel de testing en Custody
8. 🔧 (Opcional) Agregar botón Customer Portal
9. 📝 (Opcional) Escribir documentación completa

---

## 🐛 TROUBLESHOOTING

### Webhook no recibido
- Verificar URL pública accesible (usar ngrok para desarrollo local)
- Verificar que ruta esté excluida de CSRF
- Revisar logs de Lemon Squeezy en dashboard para ver errores

### Checkout URL no genera
- Verificar que plan tenga `lemon_monthly_variant_id` o `lemon_yearly_variant_id` configurado
- Verificar API key y Store ID en `.env`
- Revisar logs de Laravel (`storage/logs/laravel.log`)

### Signature verification failed
- Verificar que `LEMON_SQUEEZY_SIGNING_SECRET` esté correcto en `.env`
- Verificar que webhook en Lemon Squeezy dashboard tenga el mismo signing secret

### Suscripción no se crea después de pago
- Verificar que webhook esté configurado y recibiendo eventos
- Verificar custom data en checkout (tenant_id, plan_id)
- Revisar logs para ver si webhook fue procesado

---

## 📦 ARQUITECTURA

```
Thunder-Pack
├── Gateways (Abstracción)
│   ├── PaymentGatewayInterface
│   ├── ManualGateway (existente)
│   └── LemonSqueezyGateway (nuevo)
├── SubscriptionService (orquestador)
│   ├── getGateway($provider)
│   └── createCheckout($tenant, $plan, $provider, $cycle)
├── WebhookController
│   └── lemonSqueezy(Request) → delega a gateway
├── Models
│   ├── Plan (con Lemon variant IDs)
│   └── Subscription (con billing_cycle)
└── Livewire
    └── ChoosePlan (UI selección de planes)

Aplicaciones (Custody, Thunder-Theme)
├── Usan Thunder-Pack como dependencia
├── Configuran .env con API keys
├── Registran exclusión CSRF
└── (Opcional) Implementan testing panel
```

---

**Estado General**: ✅ **CORE FUNCIONAL** - La integración base está completa y lista para usar. Solo faltan ajustes de configuración y features opcionales.
