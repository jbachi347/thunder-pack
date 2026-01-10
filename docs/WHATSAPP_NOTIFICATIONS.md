# Sistema de Notificaciones WhatsApp - Custody

Sistema completo de notificaciones WhatsApp multi-teléfono integrado con el panel Super Admin de Custody.

## 📋 Características

- ✅ **Múltiples números por tenant**: Cada tenant puede tener varios números de WhatsApp configurados
- ✅ **Notificaciones automáticas**: Integrado con el ciclo de vida de suscripciones
- ✅ **Envío asíncrono**: Usa jobs en cola con reintentos automáticos
- ✅ **Validación E.164**: Formato internacional estándar para números telefónicos
- ✅ **Historial completo**: Logs de todos los mensajes enviados con estado
- ✅ **Panel de administración**: Gestión completa desde Super Admin
- ✅ **Mensajes de prueba**: Envío de mensajes de prueba para verificar configuración

## 🏗️ Arquitectura

### Modelos

#### `TenantWhatsappPhone`
Representa un número de WhatsApp asociado a un tenant.

**Campos:**
- `phone_number`: Número en formato E.164 (ej: +50312345678)
- `instance_name`: Nombre de instancia en Evolution API (opcional)
- `is_default`: Teléfono predeterminado para el tenant
- `is_active`: Estado activo/inactivo
- `notification_types`: Array JSON de tipos de notificación habilitados

**Relaciones:**
- `tenant()`: BelongsTo Tenant
- `messageLogs()`: HasMany WhatsappMessageLog

**Scopes:**
- `active()`: Solo teléfonos activos
- `default()`: Solo teléfonos predeterminados
- `forNotificationType($type)`: Filtrar por tipo de notificación

#### `WhatsappMessageLog`
Registro de todos los mensajes enviados.

**Campos:**
- `tenant_id`: ID del tenant
- `tenant_whatsapp_phone_id`: ID del teléfono usado
- `phone_number`: Número al que se envió (redundante para mantener historial)
- `message`: Texto del mensaje
- `status`: Estado (pending, sent, failed, error)
- `response`: Respuesta de la API
- `notification_type`: Tipo de notificación
- `sent_at`: Timestamp de envío exitoso

**Scopes:**
- `recent()`: Ordenado por más reciente
- `sent()`: Solo mensajes enviados
- `failed()`: Solo mensajes fallidos
- `pending()`: Solo mensajes pendientes
- `forTenant($tenantId)`: Filtrar por tenant

### Servicios

#### `WhatsAppService`

Servicio principal para manejo de WhatsApp.

**Métodos principales:**

```php
// Enviar notificación a un tenant (a todos sus teléfonos habilitados)
sendNotification(Tenant $tenant, string $notificationType, string $message, bool $queue = true): array

// Enviar mensaje de prueba inmediato
sendTestMessage(TenantWhatsappPhone $phone, string $message): array

// Validar formato de número E.164
validatePhoneNumber(string $phoneNumber): bool

// Obtener historial de mensajes
getMessageHistory(Tenant $tenant, int $limit = 50): Collection

// Obtener estadísticas
getStatistics(Tenant $tenant): array

// Verificar si está configurado
isConfigured(): bool
```

**Tipos de notificación disponibles:**
- `subscription_activated`: Suscripción activada
- `subscription_expiring`: Suscripción por expirar (7 días antes)
- `subscription_expired`: Suscripción expirada
- `payment_received`: Pago recibido
- `staff_limit_reached`: Límite de personal alcanzado

#### Integración con `SubscriptionService`

El servicio de suscripciones ahora incluye métodos para notificaciones WhatsApp:

```php
// Notificar suscripción por expirar
notifySubscriptionExpiring(Tenant $tenant): void

// Notificar suscripción expirada
notifySubscriptionExpired(Tenant $tenant): void

// Notificar pago recibido
notifyPaymentReceived(Tenant $tenant, PaymentEvent $payment): void
```

### Jobs

#### `SendWhatsAppNotificationJob`

Job en cola para envío asíncrono de mensajes.

**Configuración:**
- **Intentos**: 3 reintentos automáticos
- **Backoff exponencial**: 1 min, 3 min, 10 min
- **Timeout**: 60 segundos

```php
SendWhatsAppNotificationJob::dispatch($phone, $message, $notificationType);
```

## 🎨 Panel Super Admin

### Tab WhatsApp en TenantShow

Acceso: `/sa/tenants/{tenant}?activeTab=whatsapp`

**Funcionalidades:**

1. **Estadísticas**
   - Total de mensajes enviados
   - Mensajes exitosos
   - Mensajes fallidos
   - Tasa de éxito

2. **Gestión de Teléfonos**
   - Agregar nuevo teléfono
   - Editar teléfono existente
   - Eliminar teléfono
   - Activar/Desactivar
   - Marcar como predeterminado
   - Enviar mensaje de prueba

3. **Historial de Mensajes**
   - Últimos 20 mensajes
   - Fecha, teléfono, tipo, mensaje, estado
   - Filtrado por tenant

## ⚙️ Configuración

### Variables de Entorno

Agregar al archivo `.env`:

```env
# WhatsApp Evolution API Configuration
WHATSAPP_EVOLUTION_ENABLED=true
WHATSAPP_EVOLUTION_API_URL=https://evo.bachisoft.com
WHATSAPP_EVOLUTION_API_KEY=tu_api_key_aqui
WHATSAPP_EVOLUTION_DEFAULT_INSTANCE=nombre_de_tu_instancia
```

**Nota importante**: El `WHATSAPP_EVOLUTION_DEFAULT_INSTANCE` debe coincidir con el nombre de una instancia existente en tu Evolution API. Si no especificas un `instance_name` al agregar un teléfono, se usará este valor por defecto.

### Configuración de Services

Ya configurado en `config/services.php`:

```php
'whatsapp' => [
    'enabled' => env('WHATSAPP_EVOLUTION_ENABLED', false),
    'url' => env('WHATSAPP_EVOLUTION_API_URL'),
    'key' => env('WHATSAPP_EVOLUTION_API_KEY'),
],
```

### Migraciones

Las migraciones ya están ejecutadas:
- `2026_01_09_000001_create_tenant_whatsapp_phones_table.php`
- `2026_01_09_000002_create_whatsapp_message_logs_table.php`

## 🚀 Uso

### 1. Agregar Teléfono a un Tenant

Desde el panel Super Admin:
1. Navegar a `/sa/tenants/{tenant}`
2. Hacer clic en tab "WhatsApp"
3. Clic en "Agregar Teléfono"
4. Ingresar número en formato E.164 (ej: +50312345678)
5. Seleccionar tipos de notificación (opcional)
6. Guardar

### 2. Enviar Mensaje de Prueba

Desde el mismo tab:
1. Clic en botón "📤 Enviar Prueba"
2. Seleccionar teléfono
3. Editar mensaje (pre-poblado)
4. Enviar

### 3. Notificaciones Automáticas

Las notificaciones se envían automáticamente cuando:

**Al activar suscripción:**
```php
$subscriptionService->activateManual($tenant, $plan, 30);
// → Envía email + WhatsApp a teléfonos con notification_type 'subscription_activated'
```

**Al registrar pago:**
```php
$subscriptionService->recordManualPayment($tenant, 9900, 'USD');
$subscriptionService->notifyPaymentReceived($tenant, $payment);
// → Envía WhatsApp a teléfonos con notification_type 'payment_received'
```

**Cron para suscripciones por expirar/expiradas:**
```php
// Crear comando artisan que ejecute:
foreach ($tenantsWithExpiringSub as $tenant) {
    $subscriptionService->notifySubscriptionExpiring($tenant);
}

foreach ($tenantsWithExpiredSub as $tenant) {
    $subscriptionService->notifySubscriptionExpired($tenant);
}
```

### 4. Envío Manual desde Código

```php
use App\Services\WhatsAppService;

$whatsappService = app(WhatsAppService::class);

// Enviar a un teléfono específico
$phone = TenantWhatsappPhone::find($phoneId);
$result = $whatsappService->sendTestMessage($phone, 'Tu mensaje aqui');

// Enviar notificación a todos los teléfonos del tenant
$whatsappService->sendNotification(
    $tenant, 
    'subscription_activated', 
    'Tu suscripción ha sido activada',
    true // queue = true para envío asíncrono
);
```

## 📊 Validación de Números

El sistema valida automáticamente el formato E.164:

**Formato válido:**
- `+50312345678` ✅
- `+15551234567` ✅
- `+442071234567` ✅

**Formato inválido:**
- `50312345678` ❌ (falta +)
- `+123` ❌ (muy corto)
- `+1234567890123456` ❌ (muy largo)

## 🔍 Troubleshooting

### WhatsApp no configurado

Si aparece el mensaje "Servicio WhatsApp no configurado":
1. Verificar variables en `.env`
2. Ejecutar `php artisan config:clear`
3. Verificar que `WHATSAPP_EVOLUTION_ENABLED=true`

### Mensajes no se envían

1. Verificar que el teléfono esté activo (`is_active = true`)
2. Verificar que el tipo de notificación esté habilitado
3. Revisar logs en `whatsapp_message_logs` tabla
4. Verificar cola de jobs: `php artisan queue:work`

### Error "The instance does not exist"

Este error ocurre cuando:
- El `instance_name` del teléfono no existe en Evolution API
- La instancia por defecto (`WHATSAPP_EVOLUTION_DEFAULT_INSTANCE`) no existe

**Solución**:
1. Verificar nombres de instancias en Evolution API
2. Al agregar un teléfono, especificar el `instance_name` correcto
3. O actualizar `WHATSAPP_EVOLUTION_DEFAULT_INSTANCE` en `.env` con una instancia válida

**Comando de prueba**:
```bash
php artisan whatsapp:test {phone_id}
```

Este comando muestra información detallada de configuración y el resultado del envío.

### Error de validación de número

- Usar formato E.164: `+[código país][número]`
- Mínimo 8 dígitos, máximo 15
- No incluir espacios ni guiones

## 🎯 Mejoras Futuras

- [ ] Panel de estadísticas global de WhatsApp en dashboard SA
- [ ] Templates de mensajes personalizables
- [ ] Programación de mensajes
- [ ] Respuestas automáticas
- [ ] Integración con webhooks de Evolution API
- [ ] Notificaciones por cambio de plan
- [ ] Límite de mensajes por día/mes
- [ ] Costos por mensaje

## 📝 Notas Técnicas

- El sistema usa **Laravel 12** con **Livewire 3**
- Compatible con **Evolution API** para WhatsApp
- Queue driver recomendado: `database` o `redis`
- Los logs se mantienen indefinidamente (considerar limpieza periódica)
- La relación `tenant_whatsapp_phone_id` en logs es `nullable` para mantener historial si se elimina el teléfono

## 🔗 Archivos Clave

- **Modelos**: `app/Models/TenantWhatsappPhone.php`, `app/Models/WhatsappMessageLog.php`
- **Servicios**: `app/Services/WhatsAppService.php`, `app/Services/SubscriptionService.php`
- **Job**: `app/Jobs/SendWhatsAppNotificationJob.php`
- **Componente Livewire**: `app/Livewire/SuperAdmin/TenantShow.php`
- **Vistas**: `resources/views/livewire/super-admin/tenant-show.blade.php`, `resources/views/livewire/super-admin/partials/tenant-whatsapp.blade.php`
- **Migraciones**: `database/migrations/2026_01_09_000001_*`, `database/migrations/2026_01_09_000002_*`
- **Config**: `config/services.php`

---

**Implementado:** 9 de enero, 2026  
**Versión:** 1.0.0  
**Autor:** GitHub Copilot + Development Team
