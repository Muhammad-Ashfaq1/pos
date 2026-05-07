# Roles & Permissions (RBAC)

Authorization is implemented with [`spatie/laravel-permission`](https://spatie.be/docs/laravel-permission/) v7.3, with the **teams** feature enabled so roles and permissions are scoped per tenant.

## Why teams?

Two tenants can both have a role called `manager`, but the *permissions attached to that role* are owned by their tenant. A super admin is a tenant-less ("central") user and works in the global team (`team_id = null` / `0` depending on the call site).

`config/permission.php` enables this:

```php
'teams' => true,
'team_resolver' => App\Support\Permissions\TenantPermissionTeamResolver::class,
```

[`TenantPermissionTeamResolver`](../app/Support/Permissions/TenantPermissionTeamResolver.php) resolves the active team in this priority order:

1. An explicitly-set team via `setPermissionsTeamId(...)` (used inside [`PermissionTeamScope::for()`](../app/Support/Permissions/PermissionTeamScope.php)).
2. The current `tenant()` from stancl/tenancy.
3. `auth()->user()->tenant_id`, falling back to `0`.

This means most code never has to think about teams — just call `$user->hasRole('manager')` or `$user->can('product.update')` and the right team applies automatically.

When you need to operate on roles for a specific tenant (e.g. seeders, the roles UI), wrap the work in `PermissionTeamScope::for($tenantId, fn () => …)`:

```php
PermissionTeamScope::for($tenantId, function () {
    return Role::query()->where('guard_name', 'web')->get();
});
```

## Roles

Defined as constants on the [`User` model](../app/Models/User.php#L22-L36):

| Constant | Value | Audience | Created by |
|----------|-------|----------|------------|
| `User::SUPER_ADMIN` | `super_admin` | Platform operator. No `tenant_id`. | [`SuperAdminSeeder`](../database/seeders/SuperAdminSeeder.php) |
| `User::TENANT_ADMIN` | `tenant_admin` | Shop owner. Created at signup. | [`RegisterTenantShopAction`](../app/Actions/Auth/RegisterTenantShopAction.php) |
| `User::MANAGER` | `manager` | Shop manager. | Tenant admin via roles UI |
| `User::CASHIER` | `cashier` | Front-desk POS operator. | Tenant admin |
| `User::TECHNICIAN` | `technician` | Service technician. | Tenant admin |
| `User::INVENTORY_CLERK` | `inventory_clerk` | Stock manager. | Tenant admin |
| `User::EMPLOYEE` | `employee` | Generic employee fallback. | Tenant admin |
| `User::CUSTOMER` | `customer` | End customer with portal access (placeholder). | n/a |

Roles are seeded by [`RoleSeeder`](../database/seeders/RoleSeeder.php). The [`RolesPermissionsController`](../app/Http/Controllers/Tenant/RolesPermissionsController.php#L21-L24) marks `super_admin` and `tenant_admin` as **protected**: they cannot be created, renamed, or deleted from the tenant UI.

### Dual storage of role

A user's role is stored *twice*:

- `users.role` (string) — used for fast `match` checks like `defaultDashboardRouteName()` and `isEmployee()`.
- `model_has_roles` (Spatie pivot) — used for permission checks via `$user->can(...)`.

`assignPrimaryRole()` keeps both in sync:

```php
public function assignPrimaryRole(string $role, ?int $tenantId = null): void
{
    PermissionTeamScope::for($tenantId ?? 0, function () use ($role) {
        $this->syncRoles([$role]);
    });
    $this->forceFill(['role' => $role])->saveQuietly();
}
```

The boolean predicates (`isSuperAdmin()`, `isTenantAdmin()`, `isEmployee()`) check both — so a stale `users.role` is forgiving as long as the Spatie role is correct.

## Permissions

55 permissions are seeded by [`PermissionSeeder`](../database/seeders/PermissionSeeder.php). They follow two naming conventions:

- **Granular**: `category.view`, `category.create`, `category.update`, `category.delete`.
- **Aggregate**: `products.view`, `products.manage` (added later for screens that group create/update/delete).

Routes use either form — many use `permission:product.update|products.manage` (OR-match). Both forms exist for backwards compatibility while the team migrates fully to the granular set.

### Full permission catalogue

| Group | Permissions |
|-------|-------------|
| **Dashboard** | `dashboard.view` |
| **Tenant ops** (super admin only) | `tenant.approvals.manage`, `tenant.impersonate` |
| **Users** | `users.view`, `users.create`, `users.update`, `users.delete` |
| **Roles** | `roles.view`, `roles.manage` |
| **Categories** | `category.view`, `category.create`, `category.update`, `category.delete` |
| **Sub-categories** | `subcategory.view`, `subcategory.create`, `subcategory.update`, `subcategory.delete` |
| **Products** | `product.view`, `product.create`, `product.update`, `product.delete`, `product.adjust_stock`, `products.view`, `products.manage` |
| **Services** | `service.view`, `service.create`, `service.update`, `service.delete`, `services.view`, `services.manage` |
| **Inventory** | `inventory.view`, `inventory.manage` |
| **POS** | `pos.bill` |
| **Discounts** | `discount.manage`, `discount.apply_bill`, `discount.apply_item`, `discounts.manage` |
| **Refunds** | `refunds.manage` |
| **Customers** | `customer.view`, `customer.create`, `customer.update`, `customer.delete`, `customers.view`, `customers.manage` |
| **Vehicles** | `vehicle.view`, `vehicle.create`, `vehicle.update`, `vehicle.delete`, `vehicles.view`, `vehicles.manage` |
| **Reminders** | `reminders.manage` |
| **Reports** | `reports.view`, `audit-logs.view` |
| **Settings** | `settings.manage` |

The same list is also embedded in [`RolesPermissionsController::PERMISSION_GROUPS`](../app/Http/Controllers/Tenant/RolesPermissionsController.php#L26-L43) — that constant drives the UI grouping and is the source of truth for valid permissions when syncing.

### Adding a new permission

1. Add the string to [`PermissionSeeder::$permissions`](../database/seeders/PermissionSeeder.php#L15-L70).
2. Add it to the appropriate group in [`RolesPermissionsController::PERMISSION_GROUPS`](../app/Http/Controllers/Tenant/RolesPermissionsController.php#L26-L43) so it appears in the UI.
3. Run the custom artisan command:

   ```bash
   php artisan permissions:sync
   ```

   This re-runs `PermissionSeeder` so existing tenants pick up the new permission. Roles still need to be re-synced (`RolePermissionSeeder`) to grant the new permission where appropriate.

## Default role → permission matrix

Seeded by [`RolePermissionSeeder`](../database/seeders/RolePermissionSeeder.php). New tenants inherit these defaults; tenant admins can then customise via the roles UI.

| Permission | super_admin | tenant_admin | manager | cashier | technician | inventory_clerk | employee | customer |
|------------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| dashboard.view | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| tenant.approvals.manage | ✅ |   |   |   |   |   |   |   |
| tenant.impersonate | ✅ |   |   |   |   |   |   |   |
| users.view / create / update | ✅ | ✅ |   |   |   |   |   |   |
| users.delete | ✅ |   |   |   |   |   |   |   |
| roles.view | ✅ | ✅ |   |   |   |   |   |   |
| roles.manage | ✅ |   |   |   |   |   |   |   |
| category.view | ✅ | ✅ | ✅ |   |   |   |   |   |
| category.create / update / delete | ✅ | ✅ |   |   |   |   |   |   |
| subcategory.view | ✅ | ✅ | ✅ |   |   |   |   |   |
| subcategory.create / update / delete | ✅ | ✅ |   |   |   |   |   |   |
| product.view / products.view | ✅ | ✅ | ✅ |   |   | ✅ | ✅ |   |
| product.create / update / delete / products.manage | ✅ | ✅ |   |   |   |   |   |   |
| product.adjust_stock | ✅ | ✅ |   |   |   | ✅ |   |   |
| service.view / services.view | ✅ | ✅ | ✅ |   | ✅ |   | ✅ |   |
| service.create / update / delete / services.manage | ✅ | ✅ |   |   |   |   |   |   |
| inventory.view | ✅ | ✅ | ✅ |   |   | ✅ |   |   |
| inventory.manage | ✅ | ✅ |   |   |   | ✅ |   |   |
| pos.bill | ✅ | ✅ | ✅ | ✅ |   |   |   |   |
| discount.manage / discounts.manage | ✅ | ✅ | ✅ |   |   |   |   |   |
| discount.apply_bill / apply_item | ✅ | ✅ | ✅ | ✅ |   |   |   |   |
| refunds.manage | ✅ | ✅ |   |   |   |   |   |   |
| customer.view / customers.view | ✅ | ✅ | ✅ | ✅ | ✅ |   | ✅ |   |
| customer.create / update | ✅ | ✅ | ✅ | ✅ |   |   |   |   |
| customer.delete / customers.manage | ✅ | ✅ |   |   |   |   |   |   |
| vehicle.view / vehicles.view | ✅ | ✅ | ✅ | ✅ | ✅ |   | ✅ |   |
| vehicle.create / update | ✅ | ✅ | ✅ | ✅ |   |   |   |   |
| vehicle.delete / vehicles.manage | ✅ | ✅ |   |   |   |   |   |   |
| reminders.manage | ✅ | ✅ |   |   |   |   |   |   |
| reports.view | ✅ | ✅ | ✅ |   |   |   |   |   |
| audit-logs.view | ✅ |   |   |   |   |   |   |   |
| settings.manage | ✅ | ✅ |   |   |   |   |   |   |

> Reading guide: super_admin gets every permission via [`Gate::before`](../app/Providers/AppServiceProvider.php#L46-L50) returning `true`, so the table-level grants are mostly redundant for them — they're seeded for explicitness.

## How permissions guard requests

### Route-level

Spatie's `permission` middleware is registered in [`bootstrap/app.php`](../bootstrap/app.php#L37) and used inline in route files. Pipe-separated permission names mean *any of these grants access*:

```php
Route::get('/products', 'index')
    ->middleware('permission:product.view|products.view')
    ->name('index');
```

A route may chain multiple permission middlewares for AND-like semantics, but the codebase consistently uses the OR form.

### Policy-level

Each tenant resource has a Policy class:
[`CategoryPolicy`](../app/Policies/CategoryPolicy.php), [`SubCategoryPolicy`](../app/Policies/SubCategoryPolicy.php), [`ProductPolicy`](../app/Policies/ProductPolicy.php), [`ServicePolicy`](../app/Policies/ServicePolicy.php), [`CustomerPolicy`](../app/Policies/CustomerPolicy.php), [`VehiclePolicy`](../app/Policies/VehiclePolicy.php), [`DiscountPolicy`](../app/Policies/DiscountPolicy.php), [`TenantPolicy`](../app/Policies/TenantPolicy.php).

Use them via `$this->authorize('update', $product)` in controllers, or `@can('update', $product)` in Blade.

### Super-admin gate override

[`AppServiceProvider::boot()`](../app/Providers/AppServiceProvider.php#L46-L50):

```php
Gate::before(function (User $user): ?bool {
    return $user->isSuperAdmin() ? true : null;
});
```

Returning `true` short-circuits *every* policy check. Returning `null` lets the normal flow continue. This means super admin users bypass policies even without explicit permission grants.

## Roles & permissions UI

```
GET    /tenant/settings/roles-permissions          → index
POST   /tenant/settings/roles-permissions/role-permissions
POST   /tenant/settings/roles-permissions/roles/save
DELETE /tenant/settings/roles-permissions/roles/{role}
POST   /tenant/settings/roles-permissions/permissions/sync
GET    /tenant/settings/roles-permissions/staff
GET    /tenant/settings/roles-permissions/staff/{user}/impersonate
```

All gated by `permission:roles.manage|settings.manage`. Implemented in [`RolesPermissionsController`](../app/Http/Controllers/Tenant/RolesPermissionsController.php).

What you can do here:

- **Create / rename / delete custom roles** — name must match `^[a-z_]+$`. `super_admin` and `tenant_admin` are protected and cannot be edited or deleted. Roles in use by ≥1 user cannot be deleted.
- **Sync permissions to a role** — a `POST` with `role_id` + `permissions[]`. The controller intersects with `PERMISSION_GROUPS` to silently drop anything unknown, then calls `Role::syncPermissions()`. Wraps everything in `PermissionTeamScope::for($tenantId)`.
- **List staff** — JSON list of all users in this tenant excluding the current user and any `tenant_admin`.
- **Impersonate staff** — see [auth-and-onboarding.md](auth-and-onboarding.md#impersonation).

## Cheatsheet for common checks

```php
// Programmatic checks
$user->isSuperAdmin();              // role column or Spatie role
$user->isTenantAdmin();
$user->hasRole('manager');          // Spatie - resolves team automatically
$user->hasAnyRole(['cashier','manager']);
$user->can('product.update');       // Spatie - resolves team automatically
$user->can('update', $product);     // delegates to ProductPolicy@update

// In Blade
@can('product.update')        ... @endcan
@can('update', $product)      ... @endcan
@role('manager')              ... @endrole

// Operating on a specific tenant's team (seeders, admin tooling)
PermissionTeamScope::for($tenantId, function () use ($user) {
    $user->syncRoles(['cashier']);
});
```
