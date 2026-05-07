# Architecture

## Tech stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Language | PHP 8.3+ | strict types, readonly properties used in controllers |
| Framework | Laravel 13 | configured via `bootstrap/app.php` (no `app/Http/Kernel.php`) |
| Multi-tenancy | `stancl/tenancy` ^3.10 | single-database mode; `Tenant` implements `Stancl\Tenancy\Contracts\Tenant` |
| Authorization | `spatie/laravel-permission` ^7.3 | teams feature enabled, `tenant_id` is the team key |
| Frontend templating | Blade | layouts in [resources/views/layouts/](../resources/views/layouts/) |
| CSS / build | Tailwind 4 + Vite 7 | bundled Vuexy admin template lives at [resources/views/vuexy-template/](../resources/views/vuexy-template/) |
| JavaScript | axios + vanilla JS | no SPA framework |
| Database | MySQL/Postgres (configurable) | migrations live in [database/migrations/](../database/migrations/) |
| Mail | Laravel Mail | verification + status notifications via queued notifications |

Composer packages of interest are declared in [composer.json](../composer.json). Laravel Pail (log tail), Pint (formatter), and Faker drive the dev/test loop.

## Folder layout

```
app/
├── Actions/                  business operations that span multiple models
│   ├── Admin/ChangeTenantStatusAction.php
│   ├── Auth/RegisterTenantShopAction.php
│   └── Tenant/Services/SyncServiceProductsAction.php
├── Enums/TenantStatus.php
├── Exceptions/InvalidTenantStatusTransitionException.php
├── Helpers/FileUploadManager.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/             super-admin screens
│   │   ├── Auth/              register, login, password reset, verify
│   │   ├── Employee/          employee panel + POS new-order
│   │   ├── Tenant/            per-shop catalog, customers, settings, roles
│   │   └── SharedDataController.php
│   ├── Middleware/            7 custom middlewares — see table below
│   └── Requests/              FormRequest validation classes
├── Models/                    Eloquent models (User, Tenant, Category, …)
│   └── Concerns/
│       ├── BelongsToTenant.php   global scope + auto-fill tenant_id
│       └── HasImages.php          polymorphic image relation helper
├── Notifications/             QueuedVerifyEmail, TenantStatusChangedNotification
├── Policies/                  one policy per resource model
├── Providers/AppServiceProvider.php
├── Repositories/              one Eloquent-backed class per resource
│   ├── Interface/             contracts bound in AppServiceProvider
│   └── Support/Concerns/HandlesCatalogSlugs.php
├── Services/ImageService.php
└── Support/
    ├── Permissions/PermissionTeamScope.php, TenantPermissionTeamResolver.php
    └── Tenancy/TenantContext.php
bootstrap/app.php              Laravel 13 entry — middleware aliases, routes, exception map
config/                        permission.php and tenancy.php carry the SaaS config
database/
├── migrations/                chronological schema
└── seeders/                   roles, permissions, super admin, demo data
routes/                        web/auth/admin/tenant/employee/console
resources/views/               Blade templates by audience
```

## Request lifecycle

A typical request to a tenant route (e.g. `GET /tenant/ecommerce/products`) flows through:

```
Browser
   │  cookie session
   ▼
public/index.php → bootstrap/app.php (Laravel 13 application factory)
   │
   ▼  middleware stack (in order)
1. web                              session, CSRF, encrypt cookies
2. auth                             requires logged-in user
3. verified                         requires email_verified_at
4. active.user (EnsureActiveUser)   blocks soft-disabled users
5. tenant.init                      InitializeTenancyFromAuthenticatedUser
6. tenant.approved                  EnsureTenantIsApproved
7. permission:product.view|...      Spatie route-level permission check
   │
   ▼
TenantController dispatches to → ProductController@index
   │
   ▼
ProductController → ProductRepositoryInterface (bound to ProductsRepository)
   │
   ▼
Eloquent Product model
   - BelongsToTenant trait adds: WHERE tenant_id = <current tenant>
   - Returns scoped result
   │
   ▼
View: resources/views/tenant/ecommerce/products/index.blade.php
```

## Middleware reference

All custom aliases are registered in [bootstrap/app.php](../bootstrap/app.php#L31-L44):

| Alias | Class | Purpose |
|-------|-------|---------|
| `active.user` | [EnsureActiveUser](../app/Http/Middleware/EnsureActiveUser.php) | Logs out users where `is_active = false`. |
| `central.user` | [EnsureCentralUser](../app/Http/Middleware/EnsureCentralUser.php) | Allows only users with no `tenant_id` (super admin). Used by `/admin/*`. |
| `employee.panel` | [EnsureEmployeePanelAccess](../app/Http/Middleware/EnsureEmployeePanelAccess.php) | Restricts `/employee/*` to users with employee-tier roles. |
| `impersonating` | [EnsureImpersonatingSession](../app/Http/Middleware/EnsureImpersonatingSession.php) | Enables `stop-impersonate` only when `session('impersonator_id')` is set. |
| `tenant.init` | [InitializeTenancyFromAuthenticatedUser](../app/Http/Middleware/InitializeTenancyFromAuthenticatedUser.php) | Calls `tenancy()->initialize()` from `auth()->user()->tenant`. Must run before any tenant-scoped query. |
| `tenant.approved` | [EnsureTenantIsApproved](../app/Http/Middleware/EnsureTenantIsApproved.php) | Blocks logged-in users whose tenant status is not `Approved`. |
| `super_admin` | [IsSuperAdmin](../app/Http/Middleware/IsSuperAdmin.php) | Hard-gate for `/admin/*`. |
| `permission` / `role` / `role_or_permission` | Spatie defaults | Route-level RBAC checks. |

Middleware groups for each route file are visible at the top of [routes/admin.php](../routes/admin.php#L7), [routes/tenant.php](../routes/tenant.php#L17), [routes/employee.php](../routes/employee.php#L7).

## Layered design

The codebase intentionally separates concerns into four layers:

### 1. Controllers (`app/Http/Controllers/`)

Thin HTTP adapters. They:

- Receive a typed `FormRequest` for validation.
- Call a repository or action.
- Return a Blade view, redirect, or `JsonResponse`.

Most write paths delegate to **actions** for cross-aggregate work (signup, status transitions) and to **repositories** for single-aggregate CRUD.

### 2. Form Requests (`app/Http/Requests/`)

One class per write operation. Examples: `RegisterShopRequest`, `LoginRequest`, `SaveProductRequest`, `SaveServiceRequest`, `SaveDiscountRequest`, `ChangeTenantStatusRequest`. They centralise rules and authorization (`authorize()` method).

### 3. Repositories (`app/Repositories/`)

Each tenant resource has an interface in `Repositories/Interface/` and a concrete class bound in [AppServiceProvider::register()](../app/Providers/AppServiceProvider.php#L30-L40). The contracts are:

| Interface | Implementation |
|-----------|----------------|
| `CategoryRepositoryInterface` | `CategoriesRepository` |
| `SubCategoryRepositoryInterface` | `SubCategoriesRepository` |
| `ProductRepositoryInterface` | `ProductsRepository` |
| `ServiceRepositoryInterface` | `ServicesRepository` |
| `CustomerRepositoryInterface` | `CustomersRepository` |
| `VehicleRepositoryInterface` | `VehiclesRepository` |
| `DiscountRepositoryInterface` | `DiscountsRepository` |
| `ShopSettingsRepositoryInterface` | `ShopSettingsRepository` |

Repositories own search scopes, slug generation (via `HandlesCatalogSlugs` trait), and pagination.

### 4. Actions (`app/Actions/`)

Used when an operation spans models or wraps a transaction:

- [`RegisterTenantShopAction`](../app/Actions/Auth/RegisterTenantShopAction.php) — creates `Tenant` + tenant-admin `User`, assigns the team-scoped role.
- [`ChangeTenantStatusAction`](../app/Actions/Admin/ChangeTenantStatusAction.php) — applies an approve/reject/suspend/reactivate transition, toggles the admin `is_active`, sends a notification.
- [`SyncServiceProductsAction`](../app/Actions/Tenant/Services/SyncServiceProductsAction.php) — reconciles the products attached to a service.

### 5. Models (`app/Models/`)

Eloquent models for `User`, `Tenant`, `Category`, `SubCategory`, `Product`, `Service`, `ServiceProduct`, `Customer`, `Vehicle`, `Discount`, `Image`. All tenant-owned models use the `BelongsToTenant` trait; `Product` additionally uses `HasImages` for polymorphic image attachments.

## Multi-tenancy mechanism

This is the load-bearing design choice in the app — read carefully.

### Single-database, column-scoped

Every domain table has a `tenant_id` column. There is exactly one application database. The `stancl/tenancy` package is used for **identification only** — it tells the app which tenant is active per request — and is not used for separate per-tenant connections or schemas.

### How a tenant becomes "current"

1. After login, the [`InitializeTenancyFromAuthenticatedUser`](../app/Http/Middleware/InitializeTenancyFromAuthenticatedUser.php) middleware reads `auth()->user()->tenant` and calls `tenancy()->initialize($tenant)`.
2. The `TenantContext::current()` helper at [app/Support/Tenancy/TenantContext.php](../app/Support/Tenancy/TenantContext.php) wraps this — first checking the `tenant()` global helper, then falling back to the user's `tenant_id`.
3. From this point, any code calling `app(TenantContext::class)->id()` gets the active tenant ID.

### How models stay isolated

The [`BelongsToTenant`](../app/Models/Concerns/BelongsToTenant.php) trait does three things:

```php
// 1. Global scope — all queries get WHERE tenant_id = <id>
static::addGlobalScope('tenant', function (Builder $b) {
    $tenantId = static::resolveTenantId();
    if ($tenantId !== null) {
        $b->where($b->getModel()->qualifyColumn('tenant_id'), $tenantId);
    }
});

// 2. Auto-fill on create
static::creating(function (Model $m) {
    $tenantId = static::resolveTenantId();
    if ($tenantId !== null) $m->tenant_id = $tenantId;
});

// 3. Lock tenant_id on update — silently restores original
static::updating(function (Model $m) {
    if (changed_tenant_id) $m->tenant_id = $original;
});
```

Route-model binding (`Route::get('/{product}', ...)`) is also tenant-scoped through `resolveRouteBindingQuery` — a tenant cannot fetch another tenant's record by guessing IDs.

### Escape hatches

- `Model::withoutTenantScope()` returns a query without the global scope (used by super admin code).
- `Model::forTenant($id)` re-scopes to a different tenant (used by seeders and admin actions).

### Tenant status gate

Even when `tenant_id` resolves correctly, [`EnsureTenantIsApproved`](../app/Http/Middleware/EnsureTenantIsApproved.php) blocks the request if `Tenant::status` is not `Approved`. The `TenantStatus` enum drives the message shown to the user — see [app/Enums/TenantStatus.php](../app/Enums/TenantStatus.php).

## Authorization model in two layers

1. **Route middleware** — `permission:product.view|products.view` declares the permission required to even reach the controller. See examples in [routes/tenant.php](../routes/tenant.php).
2. **Policies + Gates** — model-level `Policy` classes (`ProductPolicy`, `CategoryPolicy`, `TenantPolicy`, …) handle per-record checks (`authorize('update', $product)`).
3. **Super-admin override** — [AppServiceProvider::boot()](../app/Providers/AppServiceProvider.php#L46-L50) registers a `Gate::before` that returns `true` for any super admin, so policies don't need to check the role explicitly.

See [rbac.md](rbac.md) for the full role/permission matrix.

## Service container bindings

`AppServiceProvider::register()` binds all eight repository interfaces to their concrete implementations. The permission team resolver is bound by `config/permission.php`:

```php
// config/permission.php
'teams' => true,
'team_resolver' => App\Support\Permissions\TenantPermissionTeamResolver::class,
```

This swap means every `$user->hasRole(...)` / `$user->can(...)` check transparently scopes to the current tenant's team.

## Frontend pipeline

- Vite entrypoint via [vite.config.js](../vite.config.js); `npm run dev` for HMR, `npm run build` for production.
- The Vuexy admin template assets live under [resources/views/vuexy-template/assets](../resources/views/vuexy-template/assets/) and are referenced from layout partials in [resources/views/layouts/](../resources/views/layouts/).
- Three layouts: `layouts/app.blade.php` (admin/tenant portal), `layouts/employee-portal.blade.php` (POS panel), `auth/layout.blade.php` (auth pages), `layouts/public.blade.php` (landing).
- Server-rendered pages with axios calls for: dropdown population (`tenant.ecommerce.dropdowns.*`), JSON listings (every `*.listing` route), and the new-order screen.

## Console & queue

- Custom command: `permissions:sync` (registered in [routes/console.php](../routes/console.php)) — re-runs `PermissionSeeder` so newly added permission strings are persisted.
- Queue driver is configured in [config/queue.php](../config/queue.php). Email verification and tenant-status notifications are queued (see `QueuedVerifyEmail`, `TenantStatusChangedNotification`).

## Where to look next

- Onboarding flow → [auth-and-onboarding.md](auth-and-onboarding.md)
- Permission tables, role matrix → [rbac.md](rbac.md)
- Per-module routes/controllers/views → [modules.md](modules.md)
- Schema → [database.md](database.md)
