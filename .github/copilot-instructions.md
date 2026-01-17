# Thunder-Pack - Laravel Multi-Tenant SaaS Package

## Package Identity

**Thunder-Pack** (`bachisoft/thunder-pack`) is a **reusable Laravel package** published on Packagist. It provides multi-tenancy, subscription management, flexible limits system, and WhatsApp integration for SaaS applications.

**Version**: 1.5.9  
**License**: MIT  
**Packagist**: https://packagist.org/packages/bachisoft/thunder-pack  
**Repository**: https://github.com/bachisoft/thunder-pack

## CRITICAL: Package Publishing Workflow

### When Making Changes to Thunder-Pack

**ALWAYS follow this workflow**:

1. **Develop locally** in `thunder-pack/` workspace folder
2. **Test changes** in consuming projects (Custody, Thunder-Theme) using local composer path:
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "../thunder-pack"
       }
   ]
   ```
3. **Update version** in `composer.json` following semantic versioning:
   - Patch: Bug fixes (1.5.9 → 1.5.10)
   - Minor: New features, backward compatible (1.5.9 → 1.6.0)
   - Major: Breaking changes (1.5.9 → 2.0.0)
4. **Update CHANGELOG.md** with version and changes
5. **Commit and tag** the release:
   ```bash
   git add .
   git commit -m "Release v1.6.0: Add tenant/user CRUD components"
   git tag v1.6.0
   git push origin main --tags
   ```
6. **Publish to Packagist** (auto-updates via GitHub webhook)
7. **Update consuming projects** (Custody, Thunder-Theme):
   ```bash
   composer update bachisoft/thunder-pack --no-cache
   php artisan optimize:clear
   ```

**Never** publish incomplete features or breaking changes without proper version bump.

## Architecture Overview

### Multi-Tenancy Pattern

**Session-based isolation** (no separate databases per tenant):
- Current tenant stored in `session('current_tenant_id')`
- `BelongsToTenant` trait auto-scopes queries
- Super admins bypass tenant scoping via middleware

**Key Trait**: `ThunderPack\Traits\BelongsToTenant`
```php
use ThunderPack\Traits\BelongsToTenant;

class YourModel extends Model {
    use BelongsToTenant; // Auto-scopes to current tenant
}
```

**Middleware Stack** (order matters):
1. `tenant` - Validates tenant access, redirects if none set
2. `subscription` - Blocks access if subscription inactive
3. `superadmin` - Restricts routes to super admins only

### Core Models

**Package Namespace**: `ThunderPack\Models\`

- **Tenant**: Organizations/companies. Fields: `name`, `slug`, `brand_name`, `storage_quota_bytes`, `storage_used_bytes`, `data` (JSON)
- **User**: Via `App\Models\User` with `HasTenants` trait. Pivot: `tenant_user` with `role`, `is_owner`
- **Plan**: Subscription plans. Fields: `name`, `price_monthly`, `price_yearly`, `trial_days`, `features` (JSON), `limits` (JSON), `is_active`
- **Subscription**: Tenant subscriptions. Fields: `status`, `starts_at`, `expires_at`, `plan_id`, `tenant_id`
- **TenantUser**: Pivot model for tenant-user relationship
- **TeamInvitation**: Email invitations to join tenants
- **PaymentEvent**: Lemon Squeezy webhook events
- **TenantLimitOverride**: Custom limits per tenant
- **UsageEvent**: Resource usage tracking
- **TenantWhatsappPhone**: WhatsApp phone per tenant
- **WhatsappMessageLog**: WhatsApp message history

### Service Layer

**Facades**: `FeatureGate`, `PlanLimitService`, `SubscriptionService`

**Key Services**:
- `ThunderPack\Services\SubscriptionService` - Subscription lifecycle management
- `ThunderPack\Services\FeatureGateService` - Feature flags and limits checking
- `ThunderPack\Services\PlanLimitService` - Plan limits and usage tracking
- `ThunderPack\Services\WhatsAppService` - WhatsApp Evolution API integration

**Usage Examples**:
```php
// Check feature access
if (FeatureGate::hasFeature('api_access')) { }
if (FeatureGate::canUseResource('max_clients', 1)) { }

// Track usage
PlanLimitService::trackUsage('api_calls_per_month');
PlanLimitService::getRemainingLimit('max_projects');

// Subscription management
SubscriptionService::activateManual($tenant, $plan, 30);
SubscriptionService::isSubscriptionActive($tenant);
```

### Livewire Components Architecture

**Component Location**: `thunder-pack/src/Livewire/` (classes) + `thunder-pack/resources/views/livewire/` (views)

**Naming Convention**: PascalCase classes, kebab-case views
- Class: `ThunderPack\Livewire\SuperAdmin\TenantsIndex`
- View: `thunder-pack::livewire.super-admin.tenants-index`
- Route name: `thunder-pack.sa.tenants.index`

**Component Types**:

1. **Full-Page Components** (SuperAdmin panel):
   - Use `->layout('thunder-pack::layouts.app-sidebar-sa')`
   - Return view directly (no `@section('slot')`)
   - Examples: `Dashboard`, `TenantsIndex`, `PlansIndex`

2. **Inline Components** (embeddable):
   - No layout, return view only
   - Can be `@livewire('component-name')` in other views
   - Examples: `TenantSelector`, `SubscriptionStatusBadge`

**Current SuperAdmin Components**:
- `Dashboard` - SuperAdmin metrics dashboard
- `TenantsIndex` - List tenants (search, filter by status)
- `TenantShow` - Tenant detail with tabs (info, subscription, limits, WhatsApp)
- `TenantLimits` - Manage tenant-specific limit overrides
- `SubscriptionsIndex` - List subscriptions (filter by status)
- `SubscriptionShow` - Subscription detail
- `PlansIndex` - List plans (create, edit, toggle active)
- `PlanLimits` - Configure plan limits and features

**Missing Components** (to be created):
- ❌ `TenantsCreate` - Create new tenant
- ❌ `TenantsEdit` - Edit tenant basic info
- ❌ `TenantsDelete` - Delete tenant with confirmation
- ❌ `UsersIndex` - List all users
- ❌ `UsersShow` - User detail and management
- ❌ `UsersCreate` - Create user and assign tenants
- ❌ `UserTenantsEdit` - Manage user-tenant relationships

### Routes Structure

**File**: `thunder-pack/routes/thunder-pack.php`

**Prefix**: Configurable via `thunder-pack.routes.super_admin_prefix` (default: `sa`)

**Current Routes**:
```php
// SuperAdmin Routes (prefix: /sa)
thunder-pack.sa.dashboard                 // GET  /sa/dashboard
thunder-pack.sa.tenants.index            // GET  /sa/tenants
thunder-pack.sa.tenants.show             // GET  /sa/tenants/{tenant}
thunder-pack.sa.tenants.limits           // GET  /sa/tenants/{tenant}/limits
thunder-pack.sa.subscriptions.index      // GET  /sa/subscriptions
thunder-pack.sa.subscriptions.show       // GET  /sa/subscriptions/{subscription}
thunder-pack.sa.plans.index              // GET  /sa/plans
thunder-pack.sa.plans.limits             // GET  /sa/plans/{plan}/limits

// Tenant Routes (no prefix, requires 'tenant' middleware)
thunder-pack.tenant.select               // GET  /tenant/select
thunder-pack.tenant.switch               // POST /tenant/switch
thunder-pack.team.index                  // GET  /team (if team_management enabled)
thunder-pack.team.invitations.send       // POST /team/invitations
```

**Adding New Routes** (example):
```php
Route::get('/tenants/create', TenantsCreate::class)->name('tenants.create');
Route::get('/tenants/{tenant}/edit', TenantsEdit::class)->name('tenants.edit');
Route::get('/users', UsersIndex::class)->name('users.index');
Route::get('/users/{user}', UsersShow::class)->name('users.show');
```

### Configuration System

**File**: `thunder-pack/config/thunder-pack.php`

**Key Sections**:

1. **Model Overriding**: Allow projects to extend package models
2. **Routes Config**: Enable/disable routes, customize prefixes
3. **Feature Toggles**: Enable/disable package features
4. **Subscription Defaults**: Trial days, grace periods
5. **WhatsApp Config**: API credentials, retry settings
6. **Default Limits**: Fallback values for plans
7. **UI Customization**: Branding, colors

**Environment Variables** (for consuming projects):
```env
THUNDER_PACK_ROUTES_ENABLED=true
THUNDER_PACK_SA_PREFIX=sa
THUNDER_PACK_TEAM_INVITATIONS_ENABLED=true
THUNDER_PACK_TEAM_MANAGEMENT_ENABLED=true
THUNDER_PACK_WHATSAPP_ENABLED=false
```

### View Publishing & Overriding

**Publishing Views** (from consuming project):
```bash
php artisan vendor:publish --tag=thunder-pack-views
```

**Override Pattern**: Published views in `resources/views/vendor/thunder-pack/` take precedence over package views.

**Example**: Custody overrides SuperAdmin layout:
- Package: `thunder-pack/resources/views/layouts/app-sidebar-sa.blade.php`
- Override: `custody/resources/views/vendor/thunder-pack/layouts/app-sidebar-sa.blade.php`

**When creating new views**, consider:
- Will projects want to customize this view?
- Should it be blade component vs full template?
- Does it need slots for extension?

## UI Design System (Package Standard)

Thunder-Pack provides **pre-styled Livewire components** following minimalist design principles. Consuming projects should maintain consistency.

### Design Philosophy

**MINIMALIST** - Clean, compact, functional. No unnecessary decorations.

### Input Fields (Standard Pattern)

**Always use this styling** for forms in package components:

```blade
<!-- Text Input -->
<input type="text" 
    wire:model="field"
    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">

<!-- Select Dropdown -->
<select wire:model="field"
    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2">
    <option value="">Seleccionar...</option>
</select>

<!-- Textarea -->
<textarea wire:model="field"
    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md text-sm px-3 py-2" 
    rows="3"></textarea>
```

**Critical Rules**:
- ❌ NO `shadow-sm` class on inputs
- ✅ `text-sm` for compact text (not text-base)
- ✅ `px-3 py-2` for tight padding
- ✅ `focus:ring-2` (always include ring width)
- ✅ Always include dark mode variants

### Labels

```blade
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
    Campo Label
</label>
```

### Buttons

```blade
<!-- Primary Button -->
<button type="submit" 
    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
    Guardar
</button>

<!-- Secondary/Cancel Button -->
<button type="button" 
    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
    Cancelar
</button>
```

### Tables (Compact Minimalist Style)

**Reference**: `resources/views/livewire/super-admin/plans-index.blade.php`

```blade
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Header
                </th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    Content
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

**Key Characteristics**:
- Header padding: `px-4 py-2` (compact, not px-6 py-3)
- Cell padding: `px-4 py-3`
- Text sizes: `text-xs` (headers), `text-sm` (cells)
- Avatars: `h-8 w-8` (small, not h-10 w-10)
- Hover states: `hover:bg-gray-50 dark:hover:bg-gray-700/50`
- Use borders instead of shadows

### Cards & Containers

```blade
<!-- Use borders instead of heavy shadows -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
    <!-- Content -->
</div>

<!-- Avoid shadow-md, shadow-lg - only shadow-sm when necessary -->
```

### Typography Hierarchy

- Page titles: `text-xl font-semibold` (not text-2xl font-bold)
- Section headers: `text-sm font-medium`
- Body text: `text-sm`
- Helper text: `text-xs text-gray-500`

### Spacing Philosophy

- Vertical rhythm: `space-y-4` or `space-y-6`
- Avoid excessive margins (prefer mt-4, mb-4 over mt-6, mb-8)
- Keep layouts compact and information-dense

### Status Badges

```blade
<!-- Success (green) -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
    Activo
</span>

<!-- Warning (yellow) -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
    Vencido
</span>

<!-- Info (blue) -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
    Prueba
</span>

<!-- Gray (neutral) -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
    Cancelado
</span>
```

## Development Workflow

### Local Development Setup

```bash
# Clone repository
git clone https://github.com/bachisoft/thunder-pack.git
cd thunder-pack

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit
```

### Testing in Consuming Projects

**Use local path repository** in project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../thunder-pack",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "bachisoft/thunder-pack": "@dev"
    }
}
```

Then:
```bash
composer require bachisoft/thunder-pack:@dev
php artisan vendor:publish --tag=thunder-pack-views --force
php artisan optimize:clear
```

### Creating New Components

**1. Create Livewire Component Class**:
```php
// thunder-pack/src/Livewire/SuperAdmin/TenantsCreate.php
namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;

class TenantsCreate extends Component
{
    public string $name = '';
    public string $slug = '';
    
    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tenants,slug',
        ]);
        
        Tenant::create($validated);
        
        session()->flash('message', 'Tenant creado exitosamente');
        return redirect()->route('thunder-pack.sa.tenants.index');
    }

    public function render()
    {
        return view('thunder-pack::livewire.super-admin.tenants-create')
            ->layout('thunder-pack::layouts.app-sidebar-sa');
    }
}
```

**2. Create Blade View**:
```blade
{{-- thunder-pack/resources/views/livewire/super-admin/tenants-create.blade.php --}}
<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Crear Tenant</h1>
        
        <form wire:submit="save" class="mt-6 space-y-4">
            <!-- Form fields using standard input styling -->
        </form>
    </div>
</div>
```

**3. Register Route**:
```php
// thunder-pack/routes/thunder-pack.php
Route::get('/tenants/create', TenantsCreate::class)->name('tenants.create');
```

**4. Add to Menu** (if applicable):
```blade
{{-- thunder-pack/resources/views/layouts/app-sidebar-sa.blade.php --}}
<a href="{{ route('thunder-pack.sa.tenants.create') }}" class="...">
    Crear Tenant
</a>
```

### Migrations

**Naming Convention**: `YYYY_MM_DD_HHMMSS_descriptive_name.php`

Thunder-Pack uses **2026** prefix for migrations to avoid conflicts:
- `2026_01_01_000001_create_tenants_table.php`
- `2026_01_01_000002_create_plans_table.php`

**When adding new migrations**:
1. Use future date prefix (2026+)
2. Include rollback logic
3. Test both `up()` and `down()`
4. Document any index/foreign key changes

### Testing

**Test Structure**: `thunder-pack/tests/Feature/`

```php
namespace ThunderPack\Tests\Feature;

use ThunderPack\Tests\TestCase;

class TenantManagementTest extends TestCase
{
    public function test_super_admin_can_create_tenant()
    {
        // Test implementation
    }
}
```

**Running Tests**:
```bash
./vendor/bin/phpunit
./vendor/bin/phpunit --filter TenantManagementTest
```

## Common Patterns

### Tenant Isolation

**Always use** `BelongsToTenant` trait for tenant-scoped models:
```php
use ThunderPack\Traits\BelongsToTenant;

class Invoice extends Model
{
    use BelongsToTenant;
    
    // Automatically scoped to current tenant
}
```

### Feature Gate Checks

```php
// In controllers/components
if (!FeatureGate::hasFeature('api_access')) {
    abort(403, 'Feature not available in your plan');
}

// In Blade views
@hasFeature('custom_branding')
    <!-- Show branding controls -->
@endhasFeature
```

### Usage Tracking

```php
// Track single usage
PlanLimitService::trackUsage('api_calls_per_month');

// Check if can use before creating
if (PlanLimitService::canUseResource('max_projects', 1)) {
    Project::create($data);
    PlanLimitService::trackUsage('max_projects');
}
```

### WhatsApp Notifications

```php
use ThunderPack\Services\WhatsAppService;

$whatsapp = app(WhatsAppService::class);
$whatsapp->sendNotification(
    tenant: $tenant,
    phoneNumber: '1234567890',
    message: 'Tu suscripción expira en 7 días',
    notificationType: 'subscription_expiring'
);
```

## What NOT to Do

- ❌ Don't hardcode app-specific logic in package components
- ❌ Don't create tight coupling to consuming project structure
- ❌ Don't use `shadow-sm` on input fields (minimalist design)
- ❌ Don't use large text sizes (text-2xl+) for headers - keep compact
- ❌ Don't forget to publish changes to Packagist before updating projects
- ❌ Don't break semantic versioning when releasing updates
- ❌ Don't create views without dark mode support
- ❌ Don't modify database without migrations
- ❌ Don't bypass tenant middleware in package routes

## Package Extensions & Customization

Projects consuming Thunder-Pack should:

1. **Publish views** to override: `php artisan vendor:publish --tag=thunder-pack-views`
2. **Extend models** via config: `'models.tenant' => \App\Models\CustomTenant::class`
3. **Add custom routes** in project's `routes/web.php` (not package routes)
4. **Use feature flags** to disable unwanted features
5. **Override layouts** for branding consistency

## Documentation

- **Installation Guide**: `thunder-pack/docs/INSTALLATION.md`
- **Quick Reference**: `thunder-pack/docs/QUICK_REFERENCE.md`
- **Flexible Limits**: `thunder-pack/docs/FLEXIBLE_LIMITS_SYSTEM.md`
- **WhatsApp Integration**: `thunder-pack/docs/WHATSAPP_NOTIFICATIONS.md`
- **Lemon Squeezy**: `thunder-pack/LEMON_SQUEEZY_INTEGRATION.md`
- **Changelog**: `thunder-pack/CHANGELOG.md`

## Support & Contributing

- **Issues**: https://github.com/bachisoft/thunder-pack/issues
- **Email**: info@bachisoft.com
- **License**: MIT

When contributing:
1. Follow existing code style and patterns
2. Add tests for new features
3. Update documentation
4. Submit PR with clear description
5. Ensure backward compatibility (or bump major version)
