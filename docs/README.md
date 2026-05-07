# POS Application — Documentation

A multi-tenant Point of Sale + auto-service shop platform built on Laravel 13. Each tenant (shop) gets isolated catalog, customers, staff, roles, settings, and a POS panel. A single super-admin tier oversees tenant onboarding and lifecycle.

## What this app is

- **SaaS POS** for service-oriented retail (oil change shops, auto-service centres, parts retailers).
- **Single database, multi-tenant**: one Postgres/MySQL instance hosts every shop. Isolation is enforced through a `tenant_id` column scoped by a global Eloquent scope.
- **Three audiences**: super admin (system-wide), tenant admin/manager (per-shop owners), employees (cashier / technician / inventory clerk).
- **Stack**: Laravel 13 + Blade + Tailwind 4 + Vite. No SPA framework — server-rendered pages with light AJAX (axios + vanilla JS) for dropdowns, listings, and the POS new-order screen. The Vuexy admin template provides the visual shell.

## Documentation map

| File | Covers |
|------|--------|
| [architecture.md](architecture.md) | Tech stack, request lifecycle, layers (controller → repository → model), multi-tenancy mechanism, middleware aliases, file/folder layout. |
| [auth-and-onboarding.md](auth-and-onboarding.md) | Public landing, shop registration, email verification, super-admin approval, login gating, password reset, impersonation, logout. End-to-end flow. |
| [rbac.md](rbac.md) | Roles (8), permissions (~55), per-tenant team scoping via Spatie, role/permission management UI, role seeding, gate behaviour for super admin. |
| [modules.md](modules.md) | Every tenant module: categories, sub-categories, products, services (with product BOM), customers, vehicles, discounts, product images, shop settings, and the employee POS new-order screen. |
| [database.md](database.md) | Migration timeline, full schema per table, foreign keys, soft deletes, polymorphic relations, default tenant settings. |

## Key concepts at a glance

**Tenant lifecycle**: `pending → approved → suspended` (or `rejected`). Only `approved` allows login. Status drives gate middleware and notifications.

**Tenant scoping** (`app/Models/Concerns/BelongsToTenant.php`): every domain model uses this trait, which adds a global `where tenant_id = ?` scope, auto-fills `tenant_id` on create, and prevents `tenant_id` reassignment on update. Route-model binding also applies the scope, so `/{product}` cannot leak across tenants.

**Permission teams**: Spatie's teams feature is enabled with `tenant_id` acting as the team key. Tenant admins create custom roles per shop without colliding across tenants. Implemented via [TenantPermissionTeamResolver](../app/Support/Permissions/TenantPermissionTeamResolver.php).

**Impersonation**: super admins can log in as a tenant admin (`admin.shops.impersonate`); tenant admins can log in as their staff (`tenant.settings.roles-permissions.staff.impersonate`). `session('impersonator_id')` lets the user switch back via the `impersonating` middleware.

**Two POS surfaces**:
- Tenant admin/manager portal (`/tenant/...`) — manage catalog, customers, settings, roles.
- Employee panel (`/employee/...`) — focused new-order screen with category → sub-category → product drill-down powered by [SharedDataController](../app/Http/Controllers/SharedDataController.php).

## Quick navigation: code entry points

- Routes: [routes/web.php](../routes/web.php), [routes/auth.php](../routes/auth.php), [routes/admin.php](../routes/admin.php), [routes/tenant.php](../routes/tenant.php), [routes/employee.php](../routes/employee.php)
- Middleware aliases: [bootstrap/app.php](../bootstrap/app.php#L31-L44)
- Repository bindings: [app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php)
- Tenant context: [app/Support/Tenancy/TenantContext.php](../app/Support/Tenancy/TenantContext.php)
- Permission resolver: [app/Support/Permissions/TenantPermissionTeamResolver.php](../app/Support/Permissions/TenantPermissionTeamResolver.php)
- Seed entry: [database/seeders/DatabaseSeeder.php](../database/seeders/DatabaseSeeder.php)

## Reading order for a new contributor

1. **[architecture.md](architecture.md)** — understand how a request flows from URL to repository and how tenant isolation is enforced.
2. **[auth-and-onboarding.md](auth-and-onboarding.md)** — follow the signup → approval → first-login journey end-to-end.
3. **[rbac.md](rbac.md)** — learn how roles attach to tenants and how permission middleware gates routes.
4. **[modules.md](modules.md)** — pick the module you'll work on; each section has its routes, controller, repository, validation request, and view paths.
5. **[database.md](database.md)** — reference when designing migrations or queries.
