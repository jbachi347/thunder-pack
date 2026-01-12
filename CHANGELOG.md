# Changelog

All notable changes to `thunder-pack` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-01-12

### Added
- **Plans CRUD**: Full management interface for subscription plans in SuperAdmin panel
  - Create, edit, and delete plans
  - Set plan pricing (monthly_price_cents) and currency
  - Configure staff limits and storage quotas
  - View subscription count per plan
  - Prevent deletion of plans with active subscriptions
- New route: `/sa/plans` (SuperAdmin only)
- New Livewire component: `ThunderPack\Livewire\SuperAdmin\PlansIndex`
- Added "Planes" link to SuperAdmin sidebar navigation

### Changed
- Enhanced Plan model with subscription count relationship
- Updated SuperAdmin layout with Plans navigation item

## [1.2.3] - 2026-01-12

### Fixed
- Update orchestra/testbench to ^10.0 for Laravel 12 compatibility in GitHub Actions

## [1.2.2] - 2026-01-12

### Added
- Subscription status badge now displayed in tenant selector for better visibility
- Extensible SuperAdmin sidebar navigation via `partials.superadmin-nav` view (allows apps to add custom menu items)

### Improved
- Tenant selector UI now shows subscription status inline with tenant name
- SuperAdmin layout more flexible for app-specific customizations

## [1.2.1] - 2026-01-12

### Fixed
- Fix GitHub Actions tests by adding Livewire service provider to TestCase
- Add Thunder-Pack configuration to test environment setup

## [1.2.0] - 2026-01-12

### Added
- **SubscriptionStatusBadge Component**: New Livewire component to display subscription status
- Visual badges for: Trial, Active, Suspended, Past Due, Canceled, No Subscription
- Interactive tooltips showing expiration/renewal dates
- Color-coded status indicators (green, blue, yellow, orange, red, gray)
- `@subscriptionStatus` Blade directive for easy integration in layouts
- Icon indicators for each subscription state

### Features
- **Reusable Status Display**: Use `@subscriptionStatus` or `@livewire('thunder-pack::subscription-status-badge')` anywhere
- **Automatic Status Detection**: Detects trial vs regular subscription states
- **Dark Mode Support**: Full Tailwind dark mode theming for badges

## [1.1.2] - 2026-01-12

### Fixed
- Fix "Call to a member function format() on null" error in SubscriptionService when creating trials
- Handle both `trial_ends_at` and `ends_at` in subscription activation messages
- Improve subscription message to differentiate between trial and regular subscription

## [1.1.1] - 2026-01-12

### Fixed
- Auto-generate unique slug for tenants when creating via `CreateTenantWithPlan` component
- Added random 4-character suffix to slugs to prevent collisions with duplicate company names
- Slug generation now falls back to timestamp if random suffixes are exhausted

## [1.1.0] - 2026-01-12

### Added
- **Self-Service Tenant Creation**: New `CreateTenantWithPlan` Livewire component for user-initiated tenant creation
- **Automatic Trial Activation**: 7-day trial periods automatically created when new tenants are registered
- **Plan Selection UI**: Users can now choose plans during tenant creation with pricing and limits displayed
- **"Create New Organization" Button**: Added to TenantSelector component for easy access to tenant creation

### Changed
- Updated `SubscriptionService::activateManual()` to support trial mode with new `$isTrial` parameter
- Enhanced TenantSelector to refresh automatically when new tenants are created via event listeners
- Improved empty state messaging in TenantSelector to encourage tenant creation

### Security
- Replaced exposed APP_KEY in phpunit.xml with dummy key for security

## [1.0.0] - 2026-01-12

### Added
- Initial stable release
- Multi-tenant SaaS architecture with session-based tenancy
- Subscription management system with flexible plans (monthly/yearly)
- Feature gates for tier-based functionality control
- Plan limits system with soft/hard limits and overrides
- WhatsApp integration for notifications (optional)
- Super Admin panel at `/sa/*` routes
- Tenant management and switching
- Team invitation system
- Livewire v3 components for interactive UI
- Email notifications for subscription events
- Payment events tracking
- Usage events tracking for analytics
- Middleware stack: `tenant`, `subscription`, `superadmin`
- `BelongsToTenant` trait for automatic model scoping
- `HasTenants` trait for user-tenant relationships
- Comprehensive documentation in `/docs` folder
- Artisan commands for maintenance and testing
- 13 database migrations for complete schema
- Plan seeder with example plans

### Features
- **Subscription Management**: Manual activation, status checking, expiration handling
- **Flexible Limits System**: Per-tenant limits with overrides, soft/hard enforcement
- **Feature Gating**: Enable/disable features based on subscription tier
- **Multi-Tenancy**: Session-based tenant isolation with automatic query scoping
- **Team Management**: Invite users to tenants with role-based access
- **Super Admin Panel**: Manage all tenants, subscriptions, and system settings
- **WhatsApp Notifications**: Optional integration for subscription alerts
- **Email Notifications**: Automated emails for subscription lifecycle events
- **Usage Tracking**: Monitor feature usage and enforce limits
- **Payment Events**: Track all payment-related activities
- **Dark Mode Support**: Full Tailwind CSS dark mode theming

### Documentation
- Installation guide with step-by-step setup
- Flexible limits system documentation
- WhatsApp notifications integration guide
- Quick reference for common tasks
- Implementation summary
- Full feature documentation

[1.2.3]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.2.3
[1.2.2]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.2.2
[1.2.1]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.2.1
[1.2.0]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.2.0
[1.1.2]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.1.2
[1.1.1]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.1.1
[1.1.0]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.1.0
[1.0.0]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.0.0
