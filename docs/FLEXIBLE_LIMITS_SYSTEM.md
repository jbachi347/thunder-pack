# Sistema de Límites Flexible - Documentación

## Descripción General

Este sistema permite definir límites dinámicos y controles de acceso a funcionalidades (features) sin necesidad de modificar el esquema de base de datos. Es ideal para multi-tenant SaaS con diferentes modelos de negocio.

---

## Arquitectura

### 1. **Plans con Features JSON**
Los planes ahora tienen un campo `features` (JSON) que almacena:
- Límites numéricos (max_clients, max_projects, api_calls_per_month)
- Banderas de funcionalidad (custom_branding, api_access)
- Módulos habilitados (whatsapp, reports, analytics)

### 2. **Tenant Limit Overrides**
Tabla para excepciones por tenant (clientes VIP, casos especiales):
```php
TenantLimitOverride::create([
    'tenant_id' => 5,
    'limit_key' => 'max_clients',
    'limit_value' => '500', // Override del plan
    'notes' => 'Cliente VIP - contrato especial'
]);
```

### 3. **Usage Events**
Registro histórico de consumo:
```php
UsageEvent::create([
    'tenant_id' => 3,
    'resource_type' => 'api_calls',
    'amount' => 1,
    'action' => 'POST /api/clients',
    'metadata' => ['endpoint' => '/api/clients', 'ip' => '192.168.1.1']
]);
```

---

## Uso del Sistema

### **PlanLimitService** - Validación de Límites

#### Verificar límites antes de acciones
```php
use App\Services\PlanLimitService;

// Lanza excepción si excede límite
try {
    PlanLimitService::check($tenant, 'max_clients', 1);
    $client = Client::create([...]); // Crear cliente
    PlanLimitService::recordUsage($tenant, 'clients', 1, 'create');
} catch (\Exception $e) {
    return redirect()->back()->with('error', $e->getMessage());
}

// Versión sin excepción (retorna bool)
if (PlanLimitService::can($tenant, 'max_projects', 5)) {
    // Usuario puede crear 5 proyectos más
}
```

#### Obtener información de límites
```php
// Límite actual del tenant
$limit = PlanLimitService::getLimit($tenant, 'max_clients'); // 250

// Uso actual
$usage = PlanLimitService::getCurrentUsage($tenant, 'max_clients'); // 147

// Restante
$remaining = PlanLimitService::getRemaining($tenant, 'max_clients'); // 103

// Porcentaje de uso (0-100)
$percentage = PlanLimitService::getUsagePercentage($tenant, 'max_clients'); // 58.8
```

#### Rate Limiting (límites temporales)
```php
// En el plan JSON:
"features": {
    "api_calls_per_month": 25000,
    "api_calls_per_day": 1000
}

// En el código:
PlanLimitService::check($tenant, 'api_calls_per_day', 1);
PlanLimitService::recordUsage($tenant, 'api_calls', 1);
```

#### Overrides personalizados
```php
// Dar límite personalizado a un tenant
PlanLimitService::setOverride($tenant, 'max_clients', 500, 'Cliente enterprise');

// Límite ilimitado (null)
PlanLimitService::setOverride($tenant, 'api_calls_per_month', null, 'Sin límites');

// Remover override
PlanLimitService::removeOverride($tenant, 'max_clients');
```

---

### **FeatureGate** - Control de Funcionalidades

#### Verificar acceso a features
```php
use App\Services\FeatureGate;

// Verificar acceso a un módulo
if (FeatureGate::allows($tenant, 'whatsapp')) {
    // Mostrar sección de WhatsApp
}

// Verificar múltiples features
if (FeatureGate::allowsAny($tenant, ['api', 'webhooks'])) {
    // Al menos una disponible
}

if (FeatureGate::allowsAll($tenant, ['custom_branding', 'white_label'])) {
    // Todas disponibles
}

// Obtener todos los módulos habilitados
$modules = FeatureGate::getModules($tenant);
// ['basic_reports', 'whatsapp', 'api']
```

#### En Controladores
```php
class ApiController extends Controller
{
    public function index()
    {
        $tenant = Tenant::find(session('current_tenant_id'));
        
        if (FeatureGate::denies($tenant, 'api_access')) {
            return response()->json([
                'error' => 'Actualiza tu plan para acceder al API'
            ], 403);
        }
        
        // API lógica...
    }
}
```

---

### **Blade Directives**

#### @hasFeature
```blade
@hasFeature('whatsapp')
    <div class="whatsapp-integration">
        <h3>Integración WhatsApp</h3>
        <!-- Contenido del módulo -->
    </div>
@else
    <div class="upgrade-prompt">
        <p>Actualiza tu plan para acceder a WhatsApp</p>
        <a href="/pricing">Ver planes</a>
    </div>
@endhasFeature
```

#### @canUseResource
```blade
@canUseResource('max_projects', 1)
    <a href="/projects/create" class="btn-primary">
        + Nuevo Proyecto
    </a>
@else
    <span class="text-gray-500" title="Límite alcanzado">
        + Nuevo Proyecto (límite alcanzado)
    </span>
@endcanUseResource
```

#### @hasAnyFeature
```blade
@hasAnyFeature(['api_access', 'webhooks'])
    <nav>
        <a href="/integrations">Integraciones</a>
    </nav>
@endhasAnyFeature
```

---

## Ejemplos por Tipo de SaaS

### **SaaS de Gestión de Clientes (CRM)**
```json
"features": {
    "max_clients": 250,
    "max_contacts_per_client": 50,
    "max_custom_fields": 20,
    "email_campaigns_per_month": 10,
    "modules": ["crm", "reports", "email_marketing"]
}
```

```php
// Al crear cliente
PlanLimitService::check($tenant, 'max_clients', 1);
$client = Client::create($data);
PlanLimitService::recordUsage($tenant, 'clients', 1, 'create');

// Al eliminar
PlanLimitService::recordUsage($tenant, 'clients', -1, 'delete');
```

---

### **SaaS de Licencias de Software**
```json
"features": {
    "max_licenses": 100,
    "max_activations_per_license": 3,
    "license_duration_days": 365,
    "modules": ["license_management", "analytics"]
}
```

```php
// Validar activaciones
$license = License::find($id);
$activations = $license->activations()->count();
$limit = PlanLimitService::getLimit($tenant, 'max_activations_per_license');

if ($activations >= $limit) {
    throw new \Exception("Máximo de activaciones alcanzado para esta licencia");
}
```

---

### **SaaS de E-commerce**
```json
"features": {
    "max_products": 1000,
    "max_categories": 50,
    "max_orders_per_month": 5000,
    "transaction_fee_percentage": 2.5,
    "modules": ["inventory", "shipping", "analytics", "abandoned_cart"]
}
```

```php
// Verificar límite mensual de órdenes
PlanLimitService::check($tenant, 'max_orders_per_month', 1);
$order = Order::create($data);
PlanLimitService::recordUsage($tenant, 'orders', 1, 'create');
```

---

### **SaaS de Almacenamiento de Archivos**
```json
"features": {
    "max_storage_gb": 100,
    "max_file_size_mb": 50,
    "max_shared_links": 500,
    "max_team_folders": 10,
    "modules": ["file_versioning", "collaboration"]
}
```

```php
$fileSize = $request->file('upload')->getSize();
PlanLimitService::check($tenant, 'max_storage_bytes', $fileSize);

// Guardar archivo...
PlanLimitService::recordUsage($tenant, 'storage', $fileSize);
```

---

## Configuración de Planes

### **Plan Solo (Freelancers)**
```php
'features' => [
    'max_clients' => 50,
    'max_projects' => 10,
    'api_calls_per_month' => 5000,
    'modules' => ['basic_reports'],
    'custom_branding' => false,
    'api_access' => false,
]
```

### **Plan Team (Pequeñas empresas)**
```php
'features' => [
    'max_clients' => 250,
    'max_projects' => 50,
    'api_calls_per_month' => 25000,
    'modules' => ['basic_reports', 'whatsapp', 'api'],
    'custom_branding' => true,
    'api_access' => true,
    'bulk_import' => true,
]
```

### **Plan Agency (Empresas grandes)**
```php
'features' => [
    'max_clients' => 1000,
    'max_projects' => 200,
    'api_calls_per_month' => 100000,
    'modules' => ['basic_reports', 'advanced_reports', 'whatsapp', 'api', 'analytics'],
    'custom_branding' => true,
    'priority_support' => true,
    'white_label' => true,
]
```

---

## Migraciones y Actualización de Planes

### Agregar nuevos límites (sin migración)
```php
// En PlanSeeder o directamente:
$plan = Plan::where('code', 'team')->first();
$features = $plan->features;
$features['max_webhooks'] = 10;
$features['max_automation_rules'] = 25;
$plan->update(['features' => $features]);
```

### Backward Compatibility
El sistema mantiene compatibilidad con columnas legacy:
```php
// Funciona con columna antigua
$limit = $plan->staff_limit;

// Funciona con nuevo sistema
$limit = $plan->getLimit('staff_limit');
$limit = $plan->getLimit('max_staff'); // Alias
```

---

## Reportes de Uso

### Dashboard de uso del tenant
```php
$limits = [
    'clients' => [
        'limit' => PlanLimitService::getLimit($tenant, 'max_clients'),
        'usage' => PlanLimitService::getCurrentUsage($tenant, 'max_clients'),
        'percentage' => PlanLimitService::getUsagePercentage($tenant, 'max_clients'),
    ],
    'api_calls' => [
        'limit' => PlanLimitService::getLimit($tenant, 'api_calls_per_month'),
        'usage' => UsageEvent::getMonthlyUsage($tenant->id, 'api_calls'),
        'percentage' => PlanLimitService::getUsagePercentage($tenant, 'api_calls_per_month'),
    ],
];

return view('dashboard', compact('limits'));
```

### Historial de uso
```php
// Últimos 30 días de uso de API
$events = UsageEvent::where('tenant_id', $tenant->id)
    ->where('resource_type', 'api_calls')
    ->where('created_at', '>=', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->get();

// Uso agregado por día
$dailyUsage = UsageEvent::where('tenant_id', $tenant->id)
    ->where('resource_type', 'api_calls')
    ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
    ->groupBy('date')
    ->get();
```

---

## Mejores Prácticas

### 1. **Validar antes de ejecutar**
```php
// ✅ Correcto
PlanLimitService::check($tenant, 'max_projects', 1);
$project = Project::create($data);
PlanLimitService::recordUsage($tenant, 'projects', 1);

// ❌ Incorrecto
$project = Project::create($data);
// Validar después es tarde
```

### 2. **Registrar uso negativo al eliminar**
```php
public function destroy(Project $project)
{
    $project->delete();
    PlanLimitService::recordUsage($tenant, 'projects', -1, 'delete');
}
```

### 3. **Usar caché para consultas frecuentes**
```php
// El servicio ya cachea por 5 minutos automáticamente
$usage = PlanLimitService::getCurrentUsage($tenant, 'api_calls_per_day');
```

### 4. **Nombres consistentes de límites**
```
max_* → Límites totales (max_clients, max_projects)
*_per_month → Límites mensuales (api_calls_per_month)
*_per_day → Límites diarios (api_calls_per_day)
```

### 5. **Metadata en eventos**
```php
PlanLimitService::recordUsage($tenant, 'api_calls', 1, 'POST', [
    'endpoint' => '/api/clients',
    'ip' => $request->ip(),
    'user_id' => auth()->id(),
]);
```

---

## Testing

```php
use App\Services\PlanLimitService;
use App\Models\{Tenant, Plan};

public function test_can_check_limits()
{
    $plan = Plan::factory()->create([
        'features' => ['max_clients' => 10]
    ]);
    
    $tenant = Tenant::factory()->create();
    $tenant->subscriptions()->create(['plan_id' => $plan->id, 'status' => 'active']);
    
    $this->assertTrue(PlanLimitService::can($tenant, 'max_clients', 1));
    
    // Simular 10 clientes
    for ($i = 0; $i < 10; $i++) {
        PlanLimitService::recordUsage($tenant, 'clients', 1);
    }
    
    $this->assertFalse(PlanLimitService::can($tenant, 'max_clients', 1));
}
```

---

## Troubleshooting

### Cache issues
```php
// Limpiar caché de un tenant
PlanLimitService::clearCache($tenant);
FeatureGate::clearCache($tenant);
```

### Override no funciona
```php
// Verificar que existe
$override = TenantLimitOverride::where('tenant_id', $tenant->id)
    ->where('limit_key', 'max_clients')
    ->first();
    
dd($override->getParsedValue());
```

### Uso incorrecto
```php
// Verificar eventos registrados
UsageEvent::where('tenant_id', $tenant->id)
    ->where('resource_type', 'clients')
    ->sum('amount');
```

---

## Archivos Clave

- **PlanLimitService**: `app/Services/PlanLimitService.php`
- **FeatureGate**: `app/Services/FeatureGate.php`
- **Plan Model**: `app/Models/Plan.php`
- **TenantLimitOverride Model**: `app/Models/TenantLimitOverride.php`
- **UsageEvent Model**: `app/Models/UsageEvent.php`
- **Blade Directives**: `app/Providers/AppServiceProvider.php`
