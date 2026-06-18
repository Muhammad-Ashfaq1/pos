# POS Application — Documentation

A multi-tenant Point of Sale + auto-service shop platform built on Laravel 13. Each tenant (shop) gets isolated catalog, customers, staff, roles, settings, and a POS panel. A single super-admin tier oversees tenant onboarding and lifecycle, and triages demo-request leads captured from the public landing page.

## What this app is

- **SaaS POS** for service-oriented retail (oil change shops, auto-service centres, parts retailers).
- **Single database, multi-tenant**: one Postgres/MySQL instance hosts every shop. Isolation is enforced through a `tenant_id` column scoped by a global Eloquent scope.
- **Three audiences**: super admin (system-wide), tenant admin/manager (per-shop owners), employees (cashier / technician / inventory clerk).
- **Stack**: Laravel 13 + Blade + Tailwind 4 + Vite. No SPA framework — server-rendered pages with light AJAX (axios + vanilla JS) for dropdowns, listings, and the POS new-order screen. The Vuexy admin template provides the visual shell.

## Documentation map

The docs are ordered as a **journey** — the path a shop travels from first signup to a running, analysed business. Read them top to bottom, or jump to the domain you're working on. Each domain file is self-contained: routes, controllers, repositories, requests, models, and views for that area.

| # | File | The story it tells |
|---|------|--------------------|
| 1 | [architecture.md](architecture.md) | **Foundations** — tech stack, request lifecycle, controller → repository → model layers, multi-tenancy mechanism, middleware aliases, folder layout. |
| 2 | [landing-page.md](landing-page.md) | **Public landing** — marketing homepage (`/`): sections, content model, layout/styling system, scripts, and the Request-a-Demo modal + lead capture. |
| 3 | [auth-and-onboarding.md](auth-and-onboarding.md) | **Signup → onboarding** — shop registration, email verification, super-admin approval, login gating, password reset, impersonation, logout. |
| 4 | [rbac.md](rbac.md) | **Access** — roles (8), permissions (58), per-tenant team scoping via Spatie, the role/staff management UI, role seeding, super-admin gate. |
| 5 | [settings.md](settings.md) | **Configure the shop** — general/regional/operations/notification settings, currency formatting, order-related toggles, the default settings tree. |
| 6 | [catalog.md](catalog.md) | **Stock the shop** — categories, sub-categories, product types, products, services (with BOM), discounts, discount groups, product images, dropdown APIs. |
| 7 | [customers.md](customers.md) | **Know the customer** — customers (registered/walk-in/corporate) and their vehicles. |
| 8 | [orders.md](orders.md) | **Sell** — the employee POS lifecycle end-to-end: cart → estimate → checkout → partial payments → invoices/receipts (print/PDF/email) → returns & refunds. |
| 9 | [customer-portal.md](customer-portal.md) | **Customer portal** — Sanctum token API (reused by web + Flutter), tenant-scoped login/registration/invites, and the store-credit loyalty wallet (earn on paid visit, redeem at payment). |
| 10 | [dashboards.md](dashboards.md) | **Analyse & oversee** — employee panel, tenant analytics dashboard, and the super-admin platform view. |
| — | [database.md](database.md) | **Reference** — migration timeline, full schema per table, foreign keys, soft deletes, polymorphic relations, default tenant settings. |

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

Follow the journey in order — each step builds on the previous one:

1. **[architecture.md](architecture.md)** — how a request flows from URL to repository, and how tenant isolation is enforced.
2. **[auth-and-onboarding.md](auth-and-onboarding.md)** — the signup → approval → first-login journey end-to-end.
3. **[rbac.md](rbac.md)** — how roles attach to tenants and how permission middleware gates routes.
4. **[settings.md](settings.md)** — the first thing an approved shop admin does: configure currency, tax, hours, and order rules.
5. **[catalog.md](catalog.md)** — stock the catalog the POS will sell from.
6. **[customers.md](customers.md)** — the customers and vehicles a bill is made out to.
7. **[orders.md](orders.md)** — the POS itself: creating orders, estimates, payments, invoices, and returns. The heart of the app.
8. **[dashboards.md](dashboards.md)** — how it all rolls up for staff, owners, and the platform admin.

Keep **[database.md](database.md)** open alongside any of the above when designing migrations or queries.
