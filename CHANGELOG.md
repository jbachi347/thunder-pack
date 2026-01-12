# Changelog

All notable changes to `thunder-pack` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.0]: https://github.com/bachisoft/thunder-pack/releases/tag/v1.0.0
