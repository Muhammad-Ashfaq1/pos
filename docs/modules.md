# Tenant Modules

Every shop ("tenant") has the same set of modules. They follow an identical pattern:

```
Route          (in routes/tenant.php, gated by 'permission:…')
   ↓
Controller     (in app/Http/Controllers/Tenant/)
   ↓
FormRequest    (in app/Http/Requests/) for writes
   ↓
Repository     (interface in app/Repositories/Interface/, implementation in app/Repositories/)
   ↓
Model          (in app/Models/, scoped via BelongsToTenant)
   ↓
View           (in resources/views/tenant/ecommerce/<module>/ or settings/)
```

This document describes each module's data shape, public route surface, and notable rules. For schema details, see [database.md](database.md). For the permission strings used as middleware, see [rbac.md](rbac.md).

## Common patterns

- **Listings are AJAX.** Each resource has both an `index` route (returns the page chrome) and a `listing` route (returns JSON for the DataTable). The listing endpoint applies the same `permission:` middleware as `index`.
- **Save is one endpoint.** Most modules expose `POST /save` that handles both create (no `id` in payload) and update (with `id`). The controller branches on the presence of `id`.
- **Slugs auto-generate.** Catalog resources (categories, sub-categories, products) use the [`HandlesCatalogSlugs`](../app/Repositories/Support/Concerns/HandlesCatalogSlugs.php) trait — a slug is built from `name` if not provided and uniqueness is enforced per tenant.
- **`created_by` / `updated_by`** are filled by the repository from `auth()->id()` and link back to `User`.
- **`tenant_id` is automatic.** The `BelongsToTenant` trait sets it on create and refuses to mutate it on update; route-model binding is also tenant-scoped.

---

## Catalog

### Categories

Top-level taxonomy for products and services.

- **Routes** — [routes/tenant.php:47-63](../routes/tenant.php#L47-L63):
  - `GET  /tenant/ecommerce/categories` (index, perm: `category.view`)
  - `GET  /tenant/ecommerce/categories/listing` (listing JSON, perm: `category.view`)
  - `POST /tenant/ecommerce/categories/save` (perm: `category.create|category.update`)
  - `DELETE /tenant/ecommerce/categories/{category}` (perm: `category.delete`)
- **Controller**: [`Tenant\CategoryController`](../app/Http/Controllers/Tenant/CategoryController.php)
- **Validation**: [`SaveCategoryRequest`](../app/Http/Requests/Tenant/Catalog/SaveCategoryRequest.php)
- **Repository**: [`CategoriesRepository`](../app/Repositories/CategoriesRepository.php) implementing [`CategoryRepositoryInterface`](../app/Repositories/Interface/CategoryRepositoryInterface.php)
- **Model**: [`Category`](../app/Models/Category.php) — fields: `name`, `slug`, `code`, `description`, `sort_order`, `is_active`. Has many `SubCategory`, `Product`, `Service`.
- **Views**: [resources/views/tenant/ecommerce/categories/](../resources/views/tenant/ecommerce/categories/)

### Sub-categories

Nested taxonomy under a category. Used by products only — services live directly under a category.

- **Routes** — [routes/tenant.php:65-81](../routes/tenant.php#L65-L81): `index`, `listing`, `save`, `destroy` under `permission:subcategory.{view,create,update,delete}`.
- **Controller**: [`Tenant\SubCategoryController`](../app/Http/Controllers/Tenant/SubCategoryController.php)
- **Validation**: [`SaveSubCategoryRequest`](../app/Http/Requests/Tenant/Catalog/SaveSubCategoryRequest.php)
- **Model**: [`SubCategory`](../app/Models/SubCategory.php) — fields: `category_id`, `name`, `slug`, `code`, `description`, `sort_order`, `is_active`. `belongsTo Category`, `hasMany Product`.
- **Views**: [resources/views/tenant/ecommerce/sub-categories/](../resources/views/tenant/ecommerce/sub-categories/)

### Products

Sellable inventory items. Used as line items on orders and as components inside services.

- **Routes** — [routes/tenant.php:83-102](../routes/tenant.php#L83-L102):
  - `GET  /tenant/ecommerce/products` (perm: `product.view|products.view`)
  - `GET  /tenant/ecommerce/products/listing` (perm: `product.view|products.view`)
  - `GET  /tenant/ecommerce/products/{product}/edit` (perm: `product.update|products.manage`)
  - `POST /tenant/ecommerce/products/save` (perm: `product.create|product.update|products.manage`)
  - `DELETE /tenant/ecommerce/products/{product}` (perm: `product.delete|products.manage`)
- **Controller**: [`Tenant\ProductController`](../app/Http/Controllers/Tenant/ProductController.php)
- **Validation**: [`SaveProductRequest`](../app/Http/Requests/Tenant/Catalog/SaveProductRequest.php)
- **Repository**: [`ProductsRepository`](../app/Repositories/ProductsRepository.php)
- **Model**: [`Product`](../app/Models/Product.php) — fields:
  - **Identification**: `category_id`, `sub_category_id`, `product_type`, `name`, `slug`, `sku`, `barcode`, `brand`, `unit`.
  - **Pricing**: `cost_price`, `sale_price` (decimal:2), `tax_percentage` (decimal:2).
  - **Inventory**: `opening_stock`, `current_stock`, `minimum_stock_level`, `reorder_level` (decimal:3), `track_inventory` (bool).
  - **Lifecycle**: `is_active`, `created_by`, `updated_by`.
  - **Type constants**: `inventory`, `oil`, `filter`, `part`, `additive`, `other`.
  - **Search scope**: matches against `name`, `slug`, `sku`, `barcode`, `brand`, plus joined category and sub-category names.
- **Image attachment** — uses [`HasImages`](../app/Models/Concerns/HasImages.php) trait. Image management endpoints live under `/tenant/ecommerce/images/` (see Images section below).
- **Views**: [resources/views/tenant/ecommerce/products/](../resources/views/tenant/ecommerce/products/)

### Services

Service templates (e.g. "Full synthetic oil change"). A service can require certain products as a Bill of Materials (BOM).

- **Routes** — [routes/tenant.php:104-123](../routes/tenant.php#L104-L123): `index`, `listing`, `edit`, `save`, `destroy`, gated on `service.{view,create,update,delete}`.
- **Controller**: [`Tenant\ServiceController`](../app/Http/Controllers/Tenant/ServiceController.php)
- **Validation**: [`SaveServiceRequest`](../app/Http/Requests/Tenant/Catalog/SaveServiceRequest.php)
- **Repository**: [`ServicesRepository`](../app/Repositories/ServicesRepository.php) — uses [`SyncServiceProductsAction`](../app/Actions/Tenant/Services/SyncServiceProductsAction.php) to reconcile the `service_products` pivot when a service is saved.
- **Model**: [`Service`](../app/Models/Service.php) — fields:
  - **Identification**: `category_id`, `name`, `code`, `description`.
  - **Pricing**: `standard_price` (decimal:2), `tax_percentage`.
  - **Operations**: `estimated_duration_minutes`, `requires_technician` (bool).
  - **Reminders**: `reminder_interval_days`, `mileage_interval` — used by future reminder generation.
  - **Lifecycle**: `is_active`, `created_by`, `updated_by`.
- **Service ↔ Product pivot**: [`ServiceProduct`](../app/Models/ServiceProduct.php) carries `quantity`, `unit`, `is_required` per row. Accessed via `$service->products` (with pivot data) or `$service->serviceProducts`.
- **Views**: [resources/views/tenant/ecommerce/services/](../resources/views/tenant/ecommerce/services/)

### Discounts

Promotion definitions applicable at bill or item level.

- **Routes** — [routes/tenant.php:125-144](../routes/tenant.php#L125-L144): `index`, `listing`, `edit`, `save`, `destroy`, all gated on `discount.manage`.
- **Controller**: [`Tenant\DiscountController`](../app/Http/Controllers/Tenant/DiscountController.php)
- **Validation**: [`SaveDiscountRequest`](../app/Http/Requests/Tenant/Catalog/SaveDiscountRequest.php)
- **Repository**: [`DiscountsRepository`](../app/Repositories/DiscountsRepository.php)
- **Model**: [`Discount`](../app/Models/Discount.php) — fields:
  - **Identification**: `name`, `code`, `description`.
  - **Configuration**:
    - `discount_type` ∈ `fixed | percentage`
    - `applies_to` ∈ `bill | item | customer_profile | voucher | promotion`
    - `value` — fixed amount or percentage
    - `max_discount_amount` — caps percentage discounts
  - **Validity**: `starts_at`, `ends_at`, `usage_limit`.
  - **Behaviour flags**: `is_active`, `is_combinable`, `requires_reason`, `requires_manager_approval`.
- **Views**: [resources/views/tenant/ecommerce/discounts/](../resources/views/tenant/ecommerce/discounts/)

---

## CRM

### Customers

Captures registered customers, walk-ins, and corporate accounts.

- **Routes** — [routes/tenant.php:146-165](../routes/tenant.php#L146-L165): `index`, `listing`, `edit`, `save`, `destroy`, gated on `customer.{view,create,update,delete}` plus `customers.{view,manage}`.
- **Controller**: [`Tenant\CustomerController`](../app/Http/Controllers/Tenant/CustomerController.php)
- **Validation**: [`SaveCustomerRequest`](../app/Http/Requests/Tenant/Crm/SaveCustomerRequest.php)
- **Repository**: [`CustomersRepository`](../app/Repositories/CustomersRepository.php)
- **Model**: [`Customer`](../app/Models/Customer.php) — fields:
  - **Identification**: `customer_type` ∈ `registered | walk_in | corporate`, `name`, `phone`, `email`, `address`, `notes`, `date_of_birth`.
  - **Engagement**: `total_visits`, `lifetime_value`, `loyalty_points_balance`, `credit_balance`, `last_visit_at`.
  - **Walk-in shortcut**: `Customer::DEFAULT_WALK_IN_NAME` (`"Walk-in Customer"`).
- **Search scope**: name, phone, email, address, plus joined vehicle plate/make/model/registration.
- **Views**: [resources/views/tenant/ecommerce/customers/](../resources/views/tenant/ecommerce/customers/)

### Vehicles

Customer-owned vehicles, used for service history and reminder generation.

- **Routes** — [routes/tenant.php:167-186](../routes/tenant.php#L167-L186): `index`, `listing`, `edit`, `save`, `destroy`, gated on `vehicle.{view,create,update,delete}` plus `vehicles.{view,manage}`.
- **Controller**: [`Tenant\VehicleController`](../app/Http/Controllers/Tenant/VehicleController.php)
- **Validation**: [`SaveVehicleRequest`](../app/Http/Requests/Tenant/Crm/SaveVehicleRequest.php)
- **Repository**: [`VehiclesRepository`](../app/Repositories/VehiclesRepository.php)
- **Model**: [`Vehicle`](../app/Models/Vehicle.php) — fields: `customer_id`, `plate_number`, `registration_number`, `make`, `model`, `year`, `color`, `engine_type`, `odometer`, `notes`, `is_default`. `belongsTo Customer`. A customer's first vehicle may be marked `is_default = true`, available via `$customer->defaultVehicle`.
- **Views**: [resources/views/tenant/ecommerce/vehicles/](../resources/views/tenant/ecommerce/vehicles/)

---

## Images (polymorphic)

Images attach to any model that uses the [`HasImages`](../app/Models/Concerns/HasImages.php) trait. Currently only `Product` does. Each `Image` record stores a file on a Laravel disk and tracks metadata (mime, size, sort, primary flag).

- **Routes** — [routes/tenant.php:188-201](../routes/tenant.php#L188-L201):
  - `POST   /tenant/ecommerce/images/upload` (perm: `product.create|product.update|products.manage`)
  - `DELETE /tenant/ecommerce/images/{image}` (perm: `product.update|products.manage`)
  - `PATCH  /tenant/ecommerce/images/{image}/primary` (perm: `product.update|products.manage`)
- **Controller**: [`Tenant\ImageController`](../app/Http/Controllers/Tenant/ImageController.php)
- **Validation**: [`UploadImageRequest`](../app/Http/Requests/Tenant/Images/UploadImageRequest.php)
- **Service**: [`ImageService`](../app/Services/ImageService.php) handles disk upload, deletion, and primary toggle.
- **Helper**: [`FileUploadManager`](../app/Helpers/FileUploadManager.php) wraps actual file moves.
- **Model**: [`Image`](../app/Models/Image.php) — fields: `imageable_type`, `imageable_id`, `disk`, `path`, `file_name`, `original_name`, `extension`, `mime_type`, `size`, `collection`, `sort_order`, `is_primary`, `uploaded_by`. Auto-deletes the file on model deletion via a `deleting` boot hook.

---

## Dropdown APIs (tenant portal)

The catalog UI populates many `<select>` widgets via JSON endpoints. They live under `/tenant/ecommerce/dropdowns/` and short-circuit when no permission is held.

[routes/tenant.php:26-45](../routes/tenant.php#L26-L45):

| Route | Returns | Required permissions (any of) |
|-------|---------|-------------------------------|
| `GET /tenant/ecommerce/dropdowns/categories` | active categories | category/subcategory/product/service view+manage perms |
| `GET /tenant/ecommerce/dropdowns/sub-categories?category_id=…` | active sub-categories filtered by category | subcategory/product view+manage perms |
| `GET /tenant/ecommerce/dropdowns/products?sub_category_id=…&category_id=…` | active products | product/service view+manage perms |
| `GET /tenant/ecommerce/dropdowns/customers` | customers | customer/vehicle/pos view+manage perms |
| `GET /tenant/ecommerce/dropdowns/vehicles?customer_id=…` | vehicles | vehicle/customer/pos view+manage perms |

Implemented in [`Tenant\DropdownController`](../app/Http/Controllers/Tenant/DropdownController.php).

---

## Shop settings

Tenant admins configure their shop in four sections, all gated by `permission:settings.manage`. Routes live in [routes/tenant.php:204-227](../routes/tenant.php#L204-L227).

| Section | URL | Edits |
|---------|-----|-------|
| **General** | `/tenant/settings/shop-profile/general` | shop name, business email/phone, address |
| **Regional** | `/tenant/settings/shop-profile/regional` | currency, timezone, locale, tax name & percentage, invoice prefix & next number |
| **Operations** | `/tenant/settings/shop-profile/operations` | inventory low-stock threshold, business hours per day, loyalty enabled + ratio |
| **Notifications** | `/tenant/settings/shop-profile/notifications` | reminder & receipt email toggles |

- **Controller**: [`Tenant\ShopSettingsController`](../app/Http/Controllers/Tenant/ShopSettingsController.php)
- **Repository**: [`ShopSettingsRepository`](../app/Repositories/ShopSettingsRepository.php) implementing [`ShopSettingsRepositoryInterface`](../app/Repositories/Interface/ShopSettingsRepositoryInterface.php)
- **Validation**: [`SaveShopGeneralSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopGeneralSettingsRequest.php), [`SaveShopRegionalSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopRegionalSettingsRequest.php), [`SaveShopOperationsSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopOperationsSettingsRequest.php), [`SaveShopNotificationsSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopNotificationsSettingsRequest.php)
- **Storage**: settings are stored as a JSON column on `tenants.settings`. Defaults are merged on read via [`Tenant::mergedSettings()`](../app/Models/Tenant.php#L204-L207); see the `DEFAULT_SETTINGS` constant for the full default tree.
- **Views**: [resources/views/tenant/settings/shop-profile/](../resources/views/tenant/settings/shop-profile/)

### Default settings tree

```php
[
    'regional' => ['currency' => 'USD', 'timezone' => 'UTC', 'locale' => 'en'],
    'tax'      => ['name' => 'Sales Tax', 'percentage' => '0.00'],
    'invoice'  => ['prefix' => 'INV', 'next_number' => 1],
    'inventory'=> ['low_stock_threshold' => 5],
    'notifications' => [
        'reminder_email_enabled' => true,
        'receipt_email_enabled'  => true,
    ],
    'loyalty'  => ['enabled' => false, 'points_per_currency' => '1.00'],
    'business_hours' => [
        'monday'    => ['is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'tuesday'   => ['is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'wednesday' => ['is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'thursday'  => ['is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'friday'    => ['is_closed' => false, 'open' => '09:00', 'close' => '18:00'],
        'saturday'  => ['is_closed' => false, 'open' => '09:00', 'close' => '13:00'],
        'sunday'    => ['is_closed' => true,  'open' => '09:00', 'close' => '13:00'],
    ],
]
```

Read a setting anywhere with `$tenant->setting('regional.currency', 'USD')`.

---

## Roles & permissions UI

See [rbac.md](rbac.md#roles--permissions-ui) for the full description. Routes live under `/tenant/settings/roles-permissions/` and are gated by `permission:roles.manage|settings.manage`.

---

## Employee POS panel

The cashier/manager-facing POS surface. Lives under `/employee/*`.

### Middleware stack

[routes/employee.php:7](../routes/employee.php#L7):

```php
['auth', 'verified', 'active.user', 'employee.panel', 'tenant.init']
```

`employee.panel` ([`EnsureEmployeePanelAccess`](../app/Http/Middleware/EnsureEmployeePanelAccess.php)) restricts access to users whose role is one of the staff tiers (manager / cashier / technician / inventory_clerk / employee) or who have explicit access. `tenant.init` initialises tenancy from the user's tenant_id.

### Routes

| Route | Handler | Purpose |
|-------|---------|---------|
| `GET /employee/dashboard` | [`Employee\PanelController@dashboard`](../app/Http/Controllers/Employee/PanelController.php) | Employee landing screen |
| `GET /employee/order/new` | [`Employee\PanelController@newOrder`](../app/Http/Controllers/Employee/PanelController.php) | Renders the new-order POS page |
| `GET /employee/order/categories` | [`SharedDataController@categories`](../app/Http/Controllers/SharedDataController.php#L13-L27) | JSON: active categories (with optional `?q=`) |
| `GET /employee/order/sub-categories` | [`SharedDataController@subCategories`](../app/Http/Controllers/SharedDataController.php#L29-L55) | JSON: sub-categories filtered by `?category_id=` |
| `GET /employee/order/products` | [`SharedDataController@products`](../app/Http/Controllers/SharedDataController.php#L57-L84) | JSON: products filtered by `?sub_category_id=` and/or `?category_id=` |
| `GET /employee/order/search` | [`SharedDataController@search`](../app/Http/Controllers/SharedDataController.php#L86-L124) | JSON: union search across categories, sub-categories, products (limit 20/20/40) |

### Frontend behaviour

[resources/views/employee/order/new-order.blade.php](../resources/views/employee/order/new-order.blade.php) uses Select2 + axios to drive a drill-down: pick a category → fetch sub-categories → fetch products → add to cart. The free-text search box hits `/employee/order/search` and presents a unified result list. Layout: [resources/views/layouts/employee-portal.blade.php](../resources/views/layouts/employee-portal.blade.php).

> POS billing/checkout itself (turning a cart into a persisted order with totals, tax, payment) is **not yet implemented**. Permissions exist (`pos.bill`, `discount.apply_*`, `refunds.manage`) and the UI cart is partly there, but no `Order` model or table is in the migrations yet.

---

## Super-admin module

Routes live under `/admin/*` (see [routes/admin.php](../routes/admin.php)). Two pages:

| Route | Handler | Purpose |
|-------|---------|---------|
| `GET /admin/dashboard` | [`Admin\DashboardController`](../app/Http/Controllers/Admin/DashboardController.php) | Platform overview |
| `GET /admin/shops` | [`Admin\TenantController@index`](../app/Http/Controllers/Admin/TenantController.php#L19-L29) | List all tenants with admin user |
| `POST /admin/shops/{tenant}/status/{action}` | [`@changeStatus`](../app/Http/Controllers/Admin/TenantController.php#L31-L42) | approve / reject / suspend / reactivate |
| `GET /admin/shops/impersonate/{tenant}` | [`@impersonate`](../app/Http/Controllers/Admin/TenantController.php#L44-L65) | Log in as the tenant admin |
| `GET /admin/impersonate/stop` | [`@stopImpersonate`](../app/Http/Controllers/Admin/TenantController.php#L67-L77) | Restore the original session |

The status transitions and approval flow are described in detail in [auth-and-onboarding.md](auth-and-onboarding.md#super-admin-approval).

---

## Module quick-reference

| Module | Route prefix | Controller | Repository | Permission group |
|--------|--------------|------------|------------|-------------------|
| Categories | `/tenant/ecommerce/categories` | `Tenant\CategoryController` | `CategoriesRepository` | `category.*` |
| Sub-categories | `/tenant/ecommerce/sub-categories` | `Tenant\SubCategoryController` | `SubCategoriesRepository` | `subcategory.*` |
| Products | `/tenant/ecommerce/products` | `Tenant\ProductController` | `ProductsRepository` | `product.*` / `products.*` |
| Services | `/tenant/ecommerce/services` | `Tenant\ServiceController` | `ServicesRepository` | `service.*` / `services.*` |
| Discounts | `/tenant/ecommerce/discounts` | `Tenant\DiscountController` | `DiscountsRepository` | `discount.*` / `discounts.*` |
| Customers | `/tenant/ecommerce/customers` | `Tenant\CustomerController` | `CustomersRepository` | `customer.*` / `customers.*` |
| Vehicles | `/tenant/ecommerce/vehicles` | `Tenant\VehicleController` | `VehiclesRepository` | `vehicle.*` / `vehicles.*` |
| Images | `/tenant/ecommerce/images` | `Tenant\ImageController` | (uses `ImageService`) | inherits from product perms |
| Dropdowns | `/tenant/ecommerce/dropdowns` | `Tenant\DropdownController` | direct queries | inherits from owning module |
| Shop settings | `/tenant/settings/shop-profile` | `Tenant\ShopSettingsController` | `ShopSettingsRepository` | `settings.manage` |
| Roles & staff | `/tenant/settings/roles-permissions` | `Tenant\RolesPermissionsController` | direct + `ShopSettingsRepository` | `roles.manage` / `settings.manage` |
| Employee POS | `/employee` | `Employee\PanelController`, `SharedDataController` | direct queries | role-based via `employee.panel` middleware |
| Admin shops | `/admin/shops` | `Admin\TenantController` | direct + `ChangeTenantStatusAction` | super_admin only |
