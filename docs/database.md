# Database & Schema

This is the canonical schema reference. Files cited live under [database/migrations/](../database/migrations/) (chronological) and [database/seeders/](../database/seeders/).

## Conventions

- All domain tables (everything except `users`, `tenants`, `roles`, `permissions`, infrastructure tables) carry a non-null `tenant_id` and `cascadeOnDelete` to `tenants.id`.
- Composite uniqueness uses `(tenant_id, …)` so two tenants can share the same name/slug/code/SKU/barcode without conflict.
- `created_by` / `updated_by` are nullable foreign keys to `users.id` with `nullOnDelete`.
- Decimal precision: prices `(12, 2)`, stock quantities `(12, 3)`, percentages `(5, 2)`.
- Timestamps default to `created_at` / `updated_at`. Soft deletes only on `tenants`.

## Migration timeline

```
0001_01_01_000000  create_users_table
0001_01_01_000001  create_cache_table                          (Laravel default)
0001_01_01_000002  create_jobs_table                           (Laravel default)
2019_09_15_000010  create_tenants_table                        (string PK, used as the seed)
2019_09_15_000020  create_domains_table                        (later dropped)
2026_04_10_133819  create_permission_tables                    (Spatie)
2026_04_11_080419  add_tenant_fk_to_users_table
2026_04_13_180000  align_users_for_single_database_tenancy
2026_04_13_222852  convert_tenant_primary_key_to_bigint_auto_increment
2026_04_14_000000  refactor_tenant_schema_for_single_database_saas
2026_04_14_000100  enable_permission_teams                      (adds team_id to Spatie tables)
2026_04_14_000200  drop_legacy_tenant_id_column                 (removes the old string column)
2026_04_22_000000  create_categories_table
2026_04_22_120000  add_slug_to_categories_table
2026_04_23_000000  create_sub_categories_table
2026_04_23_010000  create_products_table
2026_04_23_020000  create_images_table                          (polymorphic)
2026_04_23_030000  create_services_table
2026_04_23_030100  create_service_products_table                (BOM pivot)
2026_04_23_040000  create_customers_table
2026_04_23_040100  create_vehicles_table
2026_04_23_050000  create_discounts_table
```

## Entity relationship overview

```
                 ┌──────────┐
                 │ tenants  │
                 └──┬───────┘
                    │ 1
        ┌───────────┼─────────────┬──────────────┬─────────────┬─────────┐
        │           │             │              │             │         │
        ▼           ▼             ▼              ▼             ▼         ▼
   ┌────────┐  ┌──────────┐  ┌────────┐    ┌──────────┐  ┌──────────┐  ┌─────────┐
   │ users  │  │categories│  │products│    │ services │  │customers │  │discounts│
   └───┬────┘  └────┬─────┘  └────┬───┘    └────┬─────┘  └────┬─────┘  └─────────┘
       │  M:N       │1            │M:N          │1            │1
       │  (Spatie)  │             └─┬──────┬────┘             │
       ▼            ▼               │      │                  ▼
  ┌─────────┐  ┌──────────────┐     ▼      ▼              ┌─────────┐
  │ roles & │  │ sub_categories│  service_products        │vehicles │
  │ perms   │  └──────────────┘  (pivot, w/qty + req)     └─────────┘
  └─────────┘
```

`images` is a polymorphic relation — it can attach to any model (currently only `Product`).

## Tables

### `users`

Source: [0001_01_01_000000_create_users_table.php](../database/migrations/0001_01_01_000000_create_users_table.php) + [add_tenant_fk_to_users_table](../database/migrations/2026_04_11_080419_add_tenant_fk_to_users_table.php) + [align_users_for_single_database_tenancy](../database/migrations/2026_04_13_180000_align_users_for_single_database_tenancy.php) + [drop_legacy_tenant_id_column](../database/migrations/2026_04_14_000200_drop_legacy_tenant_id_column.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string, unique | global unique |
| `email_verified_at` | timestamp, nullable | |
| `password` | string | hashed (cast `'hashed'` on model) |
| `tenant_id` | bigint, nullable, FK → tenants.id | super admins have `null`/0 |
| `role` | string | one of `super_admin`, `tenant_admin`, `manager`, `cashier`, `technician`, `inventory_clerk`, `employee`, `customer`. Default `tenant_admin`. |
| `is_active` | bool | default `false`; flipped to `true` on tenant approval |
| `phone` | string, nullable | |
| `failed_attempts` | int | reset to 0 on successful login |
| `locked_until` | timestamp, nullable | reserved for lockout logic |
| `last_login_at` | timestamp, nullable | |
| `last_login_ip` | string, nullable | |
| `remember_token` | string, nullable | Laravel "remember me" |
| `created_at` / `updated_at` | timestamps | |

Indexes: `tenant_id`, unique `(tenant_id, email)`.

### `tenants`

Source: [2019_09_15_000010_create_tenants_table.php](../database/migrations/2019_09_15_000010_create_tenants_table.php) + [convert_tenant_primary_key_to_bigint_auto_increment](../database/migrations/2026_04_13_222852_convert_tenant_primary_key_to_bigint_auto_increment.php) + [refactor_tenant_schema_for_single_database_saas](../database/migrations/2026_04_14_000000_refactor_tenant_schema_for_single_database_saas.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK auto-increment | originally string; later migrated |
| `name` | string, nullable | preferred display name; backfilled from `shop_name` / `business_name` / `owner_name` |
| `slug` | string, unique | generated from `name` + 6 random chars during signup |
| `shop_name` | string | original signup field — kept for backwards compatibility |
| `business_type` | string, nullable | |
| `business_name` | string, nullable | |
| `business_email` | string, nullable | |
| `business_phone` | string, nullable | |
| `owner_name` | string | |
| `owner_email` | string, unique nullable | |
| `owner_phone` | string, nullable | |
| `email` | string, unique | retained from legacy schema; same value as `owner_email` for new shops |
| `phone` | string, nullable | |
| `website_url` | string, nullable | |
| `address` | text, nullable | |
| `city` / `state` / `country` | string, nullable | |
| `status` | varchar(20) | enum-string: `pending`, `approved`, `rejected`, `suspended`, `inactive`. Default `pending`. Cast to [`TenantStatus`](../app/Enums/TenantStatus.php). |
| `approved_by` | bigint, nullable | super admin user id |
| `approved_at` / `rejected_at` / `suspended_at` | timestamp, nullable | set by [`ChangeTenantStatusAction`](../app/Actions/Admin/ChangeTenantStatusAction.php) |
| `rejected_reason` | text, nullable | |
| `onboarding_status` | enum | `not_started`, `in_progress`, `completed` |
| `onboarding_completed_at` | timestamp, nullable | |
| `settings` | json, nullable | merged with [`Tenant::DEFAULT_SETTINGS`](../app/Models/Tenant.php#L19-L53) on read |
| `created_at` / `updated_at` / `deleted_at` | timestamps | soft deletes enabled |

Indexes: `status`, unique `slug`, unique `owner_email`, unique `email`.

### `password_reset_tokens` & `sessions`

Standard Laravel scaffolding from [0001_01_01_000000_create_users_table.php](../database/migrations/0001_01_01_000000_create_users_table.php). Keyed by `email` and string `id` respectively.

### Spatie permission tables

Source: [2026_04_10_133819_create_permission_tables.php](../database/migrations/2026_04_10_133819_create_permission_tables.php) + [enable_permission_teams](../database/migrations/2026_04_14_000100_enable_permission_teams.php).

- `permissions(id, name, guard_name, timestamps)` — unique `(name, guard_name)`.
- `roles(id, team_id, name, guard_name, timestamps)` — unique `(team_id, name, guard_name)`. `team_id` = tenant id.
- `model_has_permissions(permission_id, model_type, model_id, team_id)` — composite PK including `team_id`.
- `model_has_roles(role_id, model_type, model_id, team_id)` — composite PK including `team_id`.
- `role_has_permissions(permission_id, role_id)` — composite PK.

The `team_id` column is set automatically by Spatie when `setPermissionsTeamId()` has been called (driven by [`TenantPermissionTeamResolver`](../app/Support/Permissions/TenantPermissionTeamResolver.php)).

### `categories`

Source: [2026_04_22_000000_create_categories_table.php](../database/migrations/2026_04_22_000000_create_categories_table.php) + [add_slug_to_categories_table](../database/migrations/2026_04_22_120000_add_slug_to_categories_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `name` | string(150) | unique per tenant |
| `slug` | string | unique per tenant (added in second migration) |
| `code` | string(50), nullable | unique per tenant |
| `description` | text, nullable | |
| `sort_order` | uint | default 0 |
| `is_active` | bool | default true |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes: `(tenant_id, is_active)`, `(tenant_id, sort_order)`, `(tenant_id, name)`.

### `sub_categories`

Source: [2026_04_23_000000_create_sub_categories_table.php](../database/migrations/2026_04_23_000000_create_sub_categories_table.php).

Same shape as `categories` plus `category_id` FK (cascade). Unique on `(tenant_id, name)`, `(tenant_id, slug)`, `(tenant_id, code)`. Indexes on `(tenant_id, category_id)` etc.

### `products`

Source: [2026_04_23_010000_create_products_table.php](../database/migrations/2026_04_23_010000_create_products_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `category_id` | bigint, nullable, FK → categories(id), nullOnDelete | |
| `sub_category_id` | bigint, nullable, FK → sub_categories(id), nullOnDelete | |
| `product_type` | string(50) | one of `inventory`, `oil`, `filter`, `part`, `additive`, `other` |
| `name` | string(150) | unique per tenant |
| `slug` | string(170), nullable | unique per tenant |
| `sku` | string(80), nullable | unique per tenant |
| `barcode` | string(80), nullable | unique per tenant |
| `brand` | string(120), nullable | |
| `unit` | string(50), nullable | e.g. `litre`, `piece`, `box` |
| `description` | text, nullable | |
| `cost_price` | decimal(12,2) | default 0 |
| `sale_price` | decimal(12,2) | default 0 |
| `tax_percentage` | decimal(5,2), nullable | |
| `opening_stock` | decimal(12,3) | default 0 |
| `current_stock` | decimal(12,3) | default 0 |
| `minimum_stock_level` | decimal(12,3) | default 0 |
| `reorder_level` | decimal(12,3) | default 0 |
| `track_inventory` | bool | default true |
| `is_active` | bool | default true |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes: `(tenant_id, category_id)`, `(tenant_id, sub_category_id)`, `(tenant_id, product_type)`, `(tenant_id, track_inventory)`, `(tenant_id, is_active)`.

### `services`

Source: [2026_04_23_030000_create_services_table.php](../database/migrations/2026_04_23_030000_create_services_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `category_id` | bigint, nullable, FK → categories(id), nullOnDelete | |
| `name` | string(150) | unique per tenant |
| `code` | string(50), nullable | unique per tenant |
| `description` | text, nullable | |
| `standard_price` | decimal(12,2) | default 0 |
| `estimated_duration_minutes` | uint, nullable | |
| `tax_percentage` | decimal(5,2), nullable | |
| `reminder_interval_days` | uint, nullable | for future reminders module |
| `mileage_interval` | uint, nullable | for future reminders module |
| `is_active` | bool | default true |
| `requires_technician` | bool | default false |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes: `(tenant_id, category_id)`, `(tenant_id, is_active)`, `(tenant_id, requires_technician)`.

### `service_products` (BOM)

Source: [2026_04_23_030100_create_service_products_table.php](../database/migrations/2026_04_23_030100_create_service_products_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `service_id` | bigint, FK → services(id), cascade | |
| `product_id` | bigint, FK → products(id), cascade | |
| `quantity` | decimal(12,3) | required |
| `unit` | string(50), nullable | typically inherits product unit |
| `is_required` | bool | default true |
| `created_at` / `updated_at` | timestamps | |

Unique: `(tenant_id, service_id, product_id)`. Indexes on `(tenant_id, service_id)` and `(tenant_id, product_id)`.

### `customers`

Source: [2026_04_23_040000_create_customers_table.php](../database/migrations/2026_04_23_040000_create_customers_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `customer_type` | string(30) | `registered` (default), `walk_in`, `corporate` |
| `name` | string(150) | walk-ins default to "Walk-in Customer" |
| `phone` | string(30), nullable | |
| `email` | string(150), nullable | |
| `address` | text, nullable | |
| `notes` | text, nullable | |
| `date_of_birth` | date, nullable | |
| `total_visits` | uint | default 0 |
| `lifetime_value` | decimal(12,2) | default 0 |
| `loyalty_points_balance` | uint | default 0 |
| `credit_balance` | decimal(12,2) | default 0 |
| `last_visit_at` | timestamp, nullable | |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes on `(tenant_id, customer_type)`, `(tenant_id, name)`, `(tenant_id, phone)`, `(tenant_id, email)`, `(tenant_id, last_visit_at)`. **No uniqueness on phone/email** — duplicates allowed since walk-ins are common.

### `vehicles`

Source: [2026_04_23_040100_create_vehicles_table.php](../database/migrations/2026_04_23_040100_create_vehicles_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `customer_id` | bigint, FK → customers(id), cascade | |
| `plate_number` | string(50) | unique per tenant |
| `registration_number` | string(80), nullable | unique per tenant |
| `make` | string(100), nullable | |
| `model` | string(100), nullable | |
| `year` | uint smallint, nullable | |
| `color` | string(50), nullable | |
| `engine_type` | string(80), nullable | |
| `odometer` | decimal(12,1), nullable | |
| `notes` | text, nullable | |
| `is_default` | bool | default false; only one per customer set true by app logic |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes on `(tenant_id, customer_id)`, `(tenant_id, is_default)`, `(tenant_id, make, model)`.

### `discounts`

Source: [2026_04_23_050000_create_discounts_table.php](../database/migrations/2026_04_23_050000_create_discounts_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `name` | string(150) | |
| `code` | string(50), nullable | unique per tenant |
| `description` | text, nullable | |
| `discount_type` | string(20) | `fixed`, `percentage` |
| `applies_to` | string(30) | `bill`, `item`, `customer_profile`, `voucher`, `promotion` |
| `value` | decimal(12,2) | required |
| `max_discount_amount` | decimal(12,2), nullable | caps percentage discounts |
| `starts_at` / `ends_at` | timestamp, nullable | validity window |
| `usage_limit` | uint, nullable | |
| `is_active` | bool | default true |
| `is_combinable` | bool | default true |
| `requires_reason` | bool | default false |
| `requires_manager_approval` | bool | default false |
| `created_by` / `updated_by` | bigint, nullable, FK → users(id) | |
| `created_at` / `updated_at` | timestamps | |

Indexes on `(tenant_id, discount_type)`, `(tenant_id, applies_to)`, `(tenant_id, is_active)`, `(tenant_id, starts_at, ends_at)`, `(tenant_id, name)`.

### `images` (polymorphic)

Source: [2026_04_23_020000_create_images_table.php](../database/migrations/2026_04_23_020000_create_images_table.php).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | bigint, FK → tenants(id), cascade | |
| `imageable_type` / `imageable_id` | string + bigint | polymorphic morph (created via `$table->morphs('imageable')`) |
| `disk` | string(50) | default `'public'` |
| `path` | string | relative path on disk |
| `file_name` | string, nullable | stored file name |
| `original_name` | string | as uploaded |
| `extension` | string(20), nullable | |
| `mime_type` | string(120), nullable | |
| `size` | unsigned bigint | bytes; default 0 |
| `collection` | string(50) | default `'gallery'` |
| `sort_order` | uint | default 1 |
| `is_primary` | bool | default false; only one true per imageable |
| `uploaded_by` | bigint, nullable, FK → users(id), nullOnDelete | |
| `created_at` / `updated_at` | timestamps | |

Indexes: `(tenant_id, collection)`, `(tenant_id, is_primary)`, `(tenant_id, imageable_type, imageable_id)`.

The `Image` model registers a `deleting` boot hook that removes the underlying file from disk when the record is destroyed.

## Seeders

[`DatabaseSeeder`](../database/seeders/DatabaseSeeder.php) runs in this order:

| Seeder | Effect |
|--------|--------|
| [`RoleSeeder`](../database/seeders/RoleSeeder.php) | Creates the 8 named roles (`super_admin`, `tenant_admin`, `manager`, `cashier`, `technician`, `inventory_clerk`, `employee`, `customer`). |
| [`PermissionSeeder`](../database/seeders/PermissionSeeder.php) | Creates the 55 permission strings. Re-runnable via `php artisan permissions:sync`. |
| [`RolePermissionSeeder`](../database/seeders/RolePermissionSeeder.php) | Attaches permissions to each role per the matrix in [rbac.md](rbac.md). |
| [`SuperAdminSeeder`](../database/seeders/SuperAdminSeeder.php) | Creates the platform super admin (no `tenant_id`). |
| [`ApprovedShopSeeder`](../database/seeders/ApprovedShopSeeder.php) | Creates a demo tenant in `approved` status with a tenant admin. |
| [`TenantEmployeeSeeder`](../database/seeders/TenantEmployeeSeeder.php) | Adds employees of various roles to the demo tenant. |
| [`TenantRoleUserSeeder`](../database/seeders/TenantRoleUserSeeder.php) | Wires team-scoped Spatie roles to seeded users. |
| [`TenantCatalogSeeder`](../database/seeders/TenantCatalogSeeder.php) | Populates demo categories, sub-categories, products, services, discounts, customers, and vehicles. |

Run all of them with:

```bash
php artisan migrate:fresh --seed
```

## Common queries

### Find a tenant's active products in a category

```php
Product::query()
    ->where('category_id', $categoryId)
    ->where('is_active', true)
    ->get(); // tenant scope is automatic
```

### List a service's required products with quantities

```php
$service->products()->wherePivot('is_required', true)->get();
// or via the dedicated relation:
$service->serviceProducts()->with('product')->get();
```

### Switch context to a different tenant (admin tooling)

```php
$other = $someTenant->getKey();

Product::forTenant($other)->where('is_active', true)->get();
```

### Bypass scoping (super admin / migrations)

```php
Product::withoutTenantScope()->count();
```

## Adding a new tenant resource — checklist

1. Create migration: include `tenant_id`, FKs, `(tenant_id, …)` unique/index pairs.
2. Create model: `use BelongsToTenant;` (and `HasImages` if attaching files).
3. Add a Repository interface in [app/Repositories/Interface/](../app/Repositories/Interface/) and concrete in [app/Repositories/](../app/Repositories/).
4. Bind the interface in [`AppServiceProvider::register()`](../app/Providers/AppServiceProvider.php#L30-L40).
5. Add a `FormRequest` for write operations.
6. Add a Policy under [app/Policies/](../app/Policies/).
7. Add permissions to [`PermissionSeeder`](../database/seeders/PermissionSeeder.php) and to the [`PERMISSION_GROUPS`](../app/Http/Controllers/Tenant/RolesPermissionsController.php#L26-L43) constant.
8. Update [`RolePermissionSeeder`](../database/seeders/RolePermissionSeeder.php) defaults.
9. Add controller under `app/Http/Controllers/Tenant/` and routes inside the `ecommerce` group in [routes/tenant.php](../routes/tenant.php) with `permission:` middleware.
10. Build views under `resources/views/tenant/ecommerce/<module>/`.
11. Run `php artisan permissions:sync` to register the new permissions.
