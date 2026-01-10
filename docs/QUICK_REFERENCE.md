# 🚀 Quick Reference - Sistema de Límites Flexible

## Importar Servicios
```php
use App\Services\PlanLimitService;
use App\Services\FeatureGate;
```

---

## 📦 PlanLimitService - Métodos Comunes

### Validar límite (con excepción)
```php
PlanLimitService::check($tenant, 'max_clients', 1);
// Lanza \Exception si excede
```

### Validar límite (bool)
```php
if (PlanLimitService::can($tenant, 'max_projects', 5)) {
    // Puede crear 5 proyectos
}
```

### Obtener límite
```php
$limit = PlanLimitService::getLimit($tenant, 'max_clients');
// 250 o null (ilimitado)
```

### Obtener uso actual
```php
$usage = PlanLimitService::getCurrentUsage($tenant, 'max_clients');
// 147
```

### Obtener restante
```php
$remaining = PlanLimitService::getRemaining($tenant, 'max_clients');
// 103
```

### Obtener porcentaje
```php
$percent = PlanLimitService::getUsagePercentage($tenant, 'max_clients');
// 58.8
```

### Registrar uso
```php
// Crear recurso (+1)
PlanLimitService::recordUsage($tenant, 'clients', 1, 'create');

// Eliminar recurso (-1)
PlanLimitService::recordUsage($tenant, 'clients', -1, 'delete');

// Con metadata
PlanLimitService::recordUsage($tenant, 'api_calls', 1, 'POST', [
    'endpoint' => '/api/clients',
    'ip' => $request->ip()
]);
```

### Override personalizado
```php
// VIP - límite custom
PlanLimitService::setOverride($tenant, 'max_clients', 500, 'Cliente VIP');

// Ilimitado
PlanLimitService::setOverride($tenant, 'api_calls_per_month', null);

// Remover override
PlanLimitService::removeOverride($tenant, 'max_clients');
```

---

## 🎭 FeatureGate - Métodos Comunes

### Verificar acceso
```php
if (FeatureGate::allows($tenant, 'whatsapp')) {
    // Tiene acceso a WhatsApp
}

if (FeatureGate::denies($tenant, 'api_access')) {
    return response()->json(['error' => 'No API access'], 403);
}
```

### Verificar múltiples
```php
// Al menos uno
if (FeatureGate::allowsAny($tenant, ['api', 'webhooks'])) {
    // Tiene API o Webhooks
}

// Todos
if (FeatureGate::allowsAll($tenant, ['custom_branding', 'white_label'])) {
    // Tiene ambos
}
```

### Obtener módulos
```php
$modules = FeatureGate::getModules($tenant);
// ['basic_reports', 'whatsapp', 'api']
```

---

## 🎨 Blade Directives

### @hasFeature
```blade
@hasFeature('whatsapp')
    <div>Módulo WhatsApp disponible</div>
@else
    <a href="/pricing">Actualizar plan</a>
@endhasFeature
```

### @canUseResource
```blade
@canUseResource('max_projects', 1)
    <button>+ Nuevo Proyecto</button>
@else
    <button disabled>Límite alcanzado</button>
@endcanUseResource
```

### @hasAnyFeature
```blade
@hasAnyFeature(['api_access', 'webhooks'])
    <a href="/integrations">Integraciones</a>
@endhasAnyFeature
```

---

## 💡 Patrones de Uso Comunes

### Pattern 1: Crear Recurso
```php
public function store(Request $request)
{
    $tenant = Tenant::find(session('current_tenant_id'));
    
    try {
        PlanLimitService::check($tenant, 'max_clients', 1);
        
        $client = $tenant->clients()->create($request->all());
        
        PlanLimitService::recordUsage($tenant, 'clients', 1, 'create', [
            'client_id' => $client->id
        ]);
        
        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### Pattern 2: Eliminar Recurso
```php
public function destroy($id)
{
    $tenant = Tenant::find(session('current_tenant_id'));
    $client = $tenant->clients()->findOrFail($id);
    
    $client->delete();
    
    // Liberar el slot
    PlanLimitService::recordUsage($tenant, 'clients', -1, 'delete', [
        'client_id' => $id
    ]);
    
    return redirect()->route('clients.index');
}
```

### Pattern 3: Feature Gating en Controller
```php
public function whatsappSettings()
{
    $tenant = Tenant::find(session('current_tenant_id'));
    
    if (FeatureGate::denies($tenant, 'whatsapp')) {
        return redirect()->route('pricing')
            ->with('error', 'Actualiza tu plan para WhatsApp');
    }
    
    return view('whatsapp.settings');
}
```

### Pattern 4: Rate Limiting (API)
```php
public function apiEndpoint(Request $request)
{
    $tenant = $this->getTenantFromToken($request);
    
    try {
        PlanLimitService::check($tenant, 'api_calls_per_day', 1);
        
        $result = $this->processRequest($request);
        
        PlanLimitService::recordUsage($tenant, 'api_calls', 1);
        
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Rate limit exceeded'
        ], 429);
    }
}
```

### Pattern 5: Bulk Import
```php
public function bulkImport(Request $request)
{
    $tenant = Tenant::find(session('current_tenant_id'));
    $items = $request->input('items');
    $count = count($items);
    
    // Verificar feature
    if (FeatureGate::denies($tenant, 'bulk_import')) {
        return back()->with('error', 'Feature no disponible');
    }
    
    // Verificar límite para todos
    try {
        PlanLimitService::check($tenant, 'max_clients', $count);
        
        foreach ($items as $item) {
            $tenant->clients()->create($item);
        }
        
        // Registrar en bloque
        PlanLimitService::recordUsage($tenant, 'clients', $count, 'bulk_import');
        
        return redirect()->route('clients.index');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### Pattern 6: Dashboard de Uso
```php
public function usageDashboard()
{
    $tenant = Tenant::find(session('current_tenant_id'));
    
    $stats = [
        'clients' => [
            'name' => 'Clientes',
            'limit' => PlanLimitService::getLimit($tenant, 'max_clients'),
            'usage' => PlanLimitService::getCurrentUsage($tenant, 'max_clients'),
            'remaining' => PlanLimitService::getRemaining($tenant, 'max_clients'),
            'percentage' => PlanLimitService::getUsagePercentage($tenant, 'max_clients'),
            'color' => $this->getColorByPercentage(
                PlanLimitService::getUsagePercentage($tenant, 'max_clients')
            ),
        ],
        // ... más recursos
    ];
    
    $features = [
        'whatsapp' => FeatureGate::allows($tenant, 'whatsapp'),
        'api' => FeatureGate::allows($tenant, 'api_access'),
        'bulk' => FeatureGate::allows($tenant, 'bulk_import'),
    ];
    
    return view('dashboard.usage', compact('stats', 'features'));
}

private function getColorByPercentage($percent)
{
    if ($percent >= 90) return 'red';
    if ($percent >= 70) return 'yellow';
    return 'green';
}
```

---

## 🔧 Super Admin - Gestión de Overrides

### Ver overrides de un tenant
```php
$overrides = TenantLimitOverride::where('tenant_id', $tenantId)->get();
```

### Establecer override
```php
PlanLimitService::setOverride(
    $tenant,
    'max_clients',
    500,
    'Cliente VIP - contrato especial'
);
```

### Eliminar override
```php
PlanLimitService::removeOverride($tenant, 'max_clients');
```

---

## 📊 Consultas de Uso

### Uso mensual de un recurso
```php
$usage = UsageEvent::getMonthlyUsage($tenantId, 'api_calls');
```

### Uso en rango de fechas
```php
$usage = UsageEvent::getUsage(
    $tenantId,
    'clients',
    now()->subMonth(),
    now()
);
```

### Últimos eventos
```php
$events = UsageEvent::where('tenant_id', $tenantId)
    ->where('resource_type', 'clients')
    ->latest()
    ->limit(50)
    ->get();
```

### Uso diario agregado
```php
$dailyUsage = UsageEvent::where('tenant_id', $tenantId)
    ->where('resource_type', 'api_calls')
    ->whereDate('created_at', '>=', now()->subDays(30))
    ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
    ->groupBy('date')
    ->get();
```

---

## 🎯 Límites Comunes

### Numéricos
```
max_clients          // Clientes máximos
max_projects         // Proyectos máximos
max_users            // Usuarios máximos
max_staff            // Staff máximo (alias de staff_limit)
max_storage_bytes    // Almacenamiento en bytes
```

### Temporales (Rate Limiting)
```
api_calls_per_day       // Llamadas API por día
api_calls_per_month     // Llamadas API por mes
api_calls_per_hour      // Llamadas API por hora
messages_per_day        // Mensajes por día
```

### Features (Booleanos)
```
custom_branding      // Marca personalizada
priority_support     // Soporte prioritario
api_access           // Acceso al API
bulk_import          // Importación masiva
white_label          // Marca blanca
advanced_reports     // Reportes avanzados
```

### Módulos (Array)
```
modules: [
    'basic_reports',
    'advanced_reports',
    'whatsapp',
    'api',
    'analytics',
    'webhooks'
]
```

---

## 🎨 Componentes UI Sugeridos

### Progress Bar de Límite
```blade
<div class="space-y-2">
    <div class="flex justify-between text-sm">
        <span>{{ $limit['name'] }}</span>
        <span>{{ $limit['usage'] }} / {{ $limit['limit'] ?? '∞' }}</span>
    </div>
    
    @if($limit['limit'])
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
                class="h-2 rounded-full {{ $limit['percentage'] > 80 ? 'bg-red-500' : 'bg-green-500' }}"
                style="width: {{ min($limit['percentage'], 100) }}%"
            ></div>
        </div>
    @endif
</div>
```

### Badge de Feature
```blade
@hasFeature('whatsapp')
    <span class="badge badge-success">✓ WhatsApp</span>
@else
    <span class="badge badge-gray">🔒 WhatsApp</span>
@endhasFeature
```

### Alerta de Límite Cercano
```blade
@if($usagePercentage > 80)
    <div class="alert alert-warning">
        ⚠️ Estás usando {{ number_format($usagePercentage, 1) }}% de tu límite.
        <a href="/pricing">Actualizar plan</a>
    </div>
@endif
```

---

## 🐛 Troubleshooting

### Limpiar caché
```php
PlanLimitService::clearCache($tenant);
FeatureGate::clearCache($tenant);
```

### Ver plan actual del tenant
```php
$subscription = $tenant->subscriptions()
    ->where('status', 'active')
    ->first();
    
$plan = $subscription->plan;
dd($plan->features);
```

### Verificar override
```php
$override = TenantLimitOverride::where('tenant_id', $tenant->id)
    ->where('limit_key', 'max_clients')
    ->first();
    
dd($override ? $override->getParsedValue() : 'No override');
```

### Ver eventos de uso
```php
UsageEvent::where('tenant_id', $tenant->id)
    ->latest()
    ->limit(10)
    ->get()
    ->toArray();
```

---

## 📚 Archivos de Referencia

- **Documentación completa:** `docs/FLEXIBLE_LIMITS_SYSTEM.md`
- **Ejemplos de código:** `app/Http/Controllers/Examples/LimitExamplesController.php`
- **Vistas de ejemplo:** `resources/views/examples/`
- **Servicios:** `app/Services/PlanLimitService.php`, `FeatureGate.php`
- **Modelos:** `app/Models/TenantLimitOverride.php`, `UsageEvent.php`

---

**¡Sistema listo para producción!** 🚀
