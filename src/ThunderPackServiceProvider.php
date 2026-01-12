<?php

namespace ThunderPack;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ThunderPack\Console\Commands\CheckStaffLimits;
use ThunderPack\Console\Commands\TestWhatsAppCommand;
use ThunderPack\Console\Commands\ThunderPackInstallCommand;
use ThunderPack\Http\Middleware\CheckSubscriptionStatus;
use ThunderPack\Http\Middleware\CheckTenantPermission;
use ThunderPack\Http\Middleware\EnsureSuperAdmin;
use ThunderPack\Http\Middleware\TenantMiddleware;
use ThunderPack\Services\FeatureGate;
use ThunderPack\Services\LimitNotificationService;
use ThunderPack\Services\PlanLimitService;
use ThunderPack\Services\SubscriptionService;
use ThunderPack\Services\WhatsAppService;

class ThunderPackServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/thunder-pack.php',
            'thunder-pack'
        );

        // Register services as singletons
        $this->app->singleton(FeatureGate::class, function ($app) {
            return new FeatureGate();
        });

        $this->app->singleton(PlanLimitService::class, function ($app) {
            return new PlanLimitService();
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService();
        });

        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService();
        });

        $this->app->singleton(LimitNotificationService::class, function ($app) {
            return new LimitNotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware aliases
        $this->registerMiddleware();

        // Register Blade directives
        $this->registerBladeDirectives();

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register commands
        $this->registerCommands();

        // Register routes
        $this->registerRoutes();

        // Register migrations
        $this->registerMigrations();

        // Register publishables
        $this->registerPublishables();

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('tenant', TenantMiddleware::class);
        $router->aliasMiddleware('subscription', CheckSubscriptionStatus::class);
        $router->aliasMiddleware('superadmin', EnsureSuperAdmin::class);
        $router->aliasMiddleware('tenant.permission', CheckTenantPermission::class);
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        // Directive for feature checking
        Blade::if('hasFeature', function (string $feature) {
            $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
            $tenant = $tenantClass::find(session('current_tenant_id'));
            if (!$tenant) {
                return false;
            }
            return app(FeatureGate::class)->allows($tenant, $feature);
        });

        // Directive for limit checking
        Blade::if('canUseResource', function (string $limitKey, int $amount = 1) {
            $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
            $tenant = $tenantClass::find(session('current_tenant_id'));
            if (!$tenant) {
                return false;
            }
            return app(PlanLimitService::class)->can($tenant, $limitKey, $amount);
        });

        // Directive to check if any feature is available
        Blade::if('hasAnyFeature', function (array $features) {
            $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
            $tenant = $tenantClass::find(session('current_tenant_id'));
            if (!$tenant) {
                return false;
            }
            return app(FeatureGate::class)->allowsAny($tenant, $features);
        });

        // Directive to render subscription status badge
        Blade::directive('subscriptionStatus', function ($expression) {
            return "<?php echo \Livewire\Livewire::mount('thunder-pack::subscription-status-badge', [{$expression}])->html(); ?>";
        });
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        // Main components
        Livewire::component('thunder-pack::tenant-selector', \ThunderPack\Livewire\TenantSelector::class);
        Livewire::component('thunder-pack::create-tenant-with-plan', \ThunderPack\Livewire\CreateTenantWithPlan::class);
        Livewire::component('thunder-pack::subscription-expired', \ThunderPack\Livewire\SubscriptionExpired::class);
        Livewire::component('thunder-pack::subscription-status-badge', \ThunderPack\Livewire\SubscriptionStatusBadge::class);

        // Super Admin components
        Livewire::component('thunder-pack::super-admin.dashboard', \ThunderPack\Livewire\SuperAdmin\Dashboard::class);
        Livewire::component('thunder-pack::super-admin.tenants-index', \ThunderPack\Livewire\SuperAdmin\TenantsIndex::class);
        Livewire::component('thunder-pack::super-admin.tenant-show', \ThunderPack\Livewire\SuperAdmin\TenantShow::class);
        Livewire::component('thunder-pack::super-admin.tenant-limits', \ThunderPack\Livewire\SuperAdmin\TenantLimits::class);
        Livewire::component('thunder-pack::super-admin.subscriptions-index', \ThunderPack\Livewire\SuperAdmin\SubscriptionsIndex::class);
        Livewire::component('thunder-pack::super-admin.subscription-show', \ThunderPack\Livewire\SuperAdmin\SubscriptionShow::class);

        // Team components
        Livewire::component('thunder-pack::team.index', \ThunderPack\Livewire\Team\Index::class);
        Livewire::component('thunder-pack::team.invite', \ThunderPack\Livewire\Team\Invite::class);
        Livewire::component('thunder-pack::team.accept-invitation', \ThunderPack\Livewire\Team\AcceptInvitation::class);
    }

    /**
     * Register Artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ThunderPackInstallCommand::class,
                CheckStaffLimits::class,
                TestWhatsAppCommand::class,
            ]);
        }
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('thunder-pack.routes.enabled', true)) {
            Route::group($this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/thunder-pack.php');
            });
        }
    }

    /**
     * Get route group configuration.
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('thunder-pack.routes.prefix', ''),
            'middleware' => config('thunder-pack.routes.middleware', ['web', 'auth']),
        ];
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        }
    }

    /**
     * Register publishable resources.
     */
    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/thunder-pack.php' => config_path('thunder-pack.php'),
            ], 'thunder-pack-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/Database/Migrations' => database_path('migrations'),
            ], 'thunder-pack-migrations');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/thunder-pack'),
            ], 'thunder-pack-views');

            // Publish documentation
            $this->publishes([
                __DIR__.'/../docs' => base_path('docs/thunder-pack'),
            ], 'thunder-pack-docs');

            // Publish all
            $this->publishes([
                __DIR__.'/../config/thunder-pack.php' => config_path('thunder-pack.php'),
                __DIR__.'/Database/Migrations' => database_path('migrations'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/thunder-pack'),
                __DIR__.'/../docs' => base_path('docs/thunder-pack'),
            ], 'thunder-pack');
        }

        // Load views with namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'thunder-pack');
    }

    /**
     * Register event listeners for cache management.
     */
    protected function registerEventListeners(): void
    {
        // Clear cache when users are attached/detached from tenants
        Event::listen(
            'eloquent.attached: ' . config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class),
            function ($event, $data) {
                $tenant = $data[0] ?? null;
                $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
                if ($tenant instanceof $tenantClass) {
                    app(PlanLimitService::class)->clearCache($tenant);
                }
            }
        );

        Event::listen(
            'eloquent.detached: ' . config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class),
            function ($event, $data) {
                $tenant = $data[0] ?? null;
                $tenantClass = config('thunder-pack.models.tenant', \ThunderPack\Models\Tenant::class);
                if ($tenant instanceof $tenantClass) {
                    app(PlanLimitService::class)->clearCache($tenant);
                }
            }
        );
    }
}
