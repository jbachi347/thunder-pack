<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all Thunder Pack tables. Leave empty for no prefix.
    |
    */

    'table_prefix' => env('THUNDER_PACK_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Model Overriding
    |--------------------------------------------------------------------------
    |
    | You can override any of the package models by specifying your own
    | model class here. Useful for extending functionality or adding
    | custom relationships.
    |
    */

    'models' => [
        'tenant' => \ThunderPack\Models\Tenant::class,
        'plan' => \ThunderPack\Models\Plan::class,
        'subscription' => \ThunderPack\Models\Subscription::class,
        'tenant_user' => \ThunderPack\Models\TenantUser::class,
        'team_invitation' => \ThunderPack\Models\TeamInvitation::class,
        'payment_event' => \ThunderPack\Models\PaymentEvent::class,
        'subscription_notification' => \ThunderPack\Models\SubscriptionNotification::class,
        'tenant_limit_override' => \ThunderPack\Models\TenantLimitOverride::class,
        'usage_event' => \ThunderPack\Models\UsageEvent::class,
        'tenant_whatsapp_phone' => \ThunderPack\Models\TenantWhatsappPhone::class,
        'whatsapp_message_log' => \ThunderPack\Models\WhatsappMessageLog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure package routes. You can disable routes entirely or customize
    | the prefix and middleware applied to them.
    |
    */

    'routes' => [
        'enabled' => env('THUNDER_PACK_ROUTES_ENABLED', true),
        'prefix' => env('THUNDER_PACK_ROUTES_PREFIX', ''),
        'middleware' => ['web', 'auth'],
        'super_admin_prefix' => env('THUNDER_PACK_SA_PREFIX', 'sa'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the package. Useful for
    | gradual rollout or customizing which features your app uses.
    |
    */

    'features' => [
        'whatsapp' => env('THUNDER_PACK_WHATSAPP_ENABLED', true),
        'team_invitations' => env('THUNDER_PACK_TEAM_INVITATIONS_ENABLED', true),
        'super_admin_panel' => env('THUNDER_PACK_SA_PANEL_ENABLED', true),
        'usage_tracking' => env('THUNDER_PACK_USAGE_TRACKING_ENABLED', true),
        'limit_notifications' => env('THUNDER_PACK_LIMIT_NOTIFICATIONS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cache TTL (time-to-live) for various cached data.
    | Values are in seconds.
    |
    */

    'cache' => [
        'ttl' => [
            'limits' => env('THUNDER_PACK_CACHE_LIMITS_TTL', 300),        // 5 minutes
            'features' => env('THUNDER_PACK_CACHE_FEATURES_TTL', 600),    // 10 minutes
            'subscription' => env('THUNDER_PACK_CACHE_SUBSCRIPTION_TTL', 300), // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration for subscription management.
    |
    */

    'subscription' => [
        'expiring_threshold_days' => env('THUNDER_PACK_EXPIRING_THRESHOLD_DAYS', 7),
        'default_trial_days' => env('THUNDER_PACK_DEFAULT_TRIAL_DAYS', 0),
        'grace_period_days' => env('THUNDER_PACK_GRACE_PERIOD_DAYS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Lemon Squeezy payment gateway.
    | Get your API key from: https://app.lemonsqueezy.com/settings/api
    | Get your store ID from: https://app.lemonsqueezy.com/settings/stores
    |
    */

    'lemon_squeezy' => [
        'api_key' => env('LEMON_SQUEEZY_API_KEY'),
        'store_id' => env('LEMON_SQUEEZY_STORE_ID'),
        'signing_secret' => env('LEMON_SQUEEZY_SIGNING_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp Evolution API integration.
    | These settings can also be managed per-tenant.
    |
    */

    'whatsapp' => [
        'enabled' => env('WHATSAPP_EVOLUTION_ENABLED', false),
        'url' => env('WHATSAPP_EVOLUTION_API_URL'),
        'key' => env('WHATSAPP_EVOLUTION_API_KEY'),
        'default_instance' => env('WHATSAPP_EVOLUTION_DEFAULT_INSTANCE', 'default'),
        
        // Available notification types
        'notification_types' => [
            'subscription_activated',
            'subscription_expiring',
            'subscription_expired',
            'payment_received',
            'staff_limit_reached',
            'test',
        ],

        // Retry configuration for failed messages
        'retry' => [
            'attempts' => env('WHATSAPP_RETRY_ATTEMPTS', 3),
            'delays' => [60, 180, 600], // seconds: 1m, 3m, 10m
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Plan Limits
    |--------------------------------------------------------------------------
    |
    | Default limit values when not specified in plan features.
    | null means unlimited.
    |
    */

    'default_limits' => [
        'staff_limit' => 1,
        'max_clients' => null,
        'max_projects' => null,
        'storage_quota_mb' => 100,
        'api_calls_per_month' => 1000,
        'api_calls_per_day' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Limit Notification Thresholds
    |--------------------------------------------------------------------------
    |
    | Percentage thresholds for sending limit notifications.
    |
    */

    'limit_notification_thresholds' => [
        80,  // Warning at 80%
        90,  // Strong warning at 90%
        100, // Limit reached at 100%
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Super Admin panel.
    |
    */

    'super_admin' => [
        'per_page' => env('THUNDER_PACK_SA_PER_PAGE', 15),
        'bypass_tenant_scope' => true, // Super admins can see all data
        'bypass_subscription_check' => true, // Super admins can access expired tenants
    ],

    /*
    |--------------------------------------------------------------------------
    | Team Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for team management and invitations.
    |
    */

    'team' => [
        'invitation_expiry_hours' => env('THUNDER_PACK_INVITATION_EXPIRY_HOURS', 72), // 3 days
        'default_role' => 'staff',
        'roles' => [
            'owner' => 'Propietario',
            'admin' => 'Administrador',
            'staff' => 'Personal',
        ],
        'permissions' => [
            'owner' => [
                'manage_team' => true,
                'manage_billing' => true,
                'manage_settings' => true,
                'view_reports' => true,
            ],
            'admin' => [
                'manage_team' => true,
                'manage_billing' => false,
                'manage_settings' => true,
                'view_reports' => true,
            ],
            'staff' => [
                'manage_team' => false,
                'manage_billing' => false,
                'manage_settings' => false,
                'view_reports' => true,
            ],
        ],
    ],

];
