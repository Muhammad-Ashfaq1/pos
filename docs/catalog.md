# Catalog

Everything a shop sells or prices against: **categories → sub-categories → product types → products → services**, plus **discounts**, **discount groups**, and **product images**. This is the data the POS [order screen](orders.md) draws from.

All catalog modules follow the same shape:

```
Route (routes/tenant.php, gated by 'permission:…')
  → Controller (app/Http/Controllers/Tenant/)
  → FormRequest (app/Http/Requests/Tenant/Catalog/)   ── writes only
  → Repository (interface in app/Repositories/Interface/, impl in app/Repositories/)
  → Model (app/Models/, tenant-scoped via BelongsToTenant)
  → View (resources/views/tenant/ecommerce/<module>/)
```

Shared conventions across every module on this page:

- **Listings are AJAX.** Each resource has an `index` route (page chrome) and a `listing` route (DataTable JSON); both carry the same `permission:` middleware.
- **Glass listing UI.** Index pages use the shared kit in [pos-listing.md](pos-listing.md) (Categories / Discounts are the reference). Port remaining modules the same way — UI only; keep listing JSON contracts.
- **One save endpoint.** `POST /save` handles create (no `id`) and update (with `id`); the controller branches on `id`.
- **Slugs auto-generate.** Categories, sub-categories, and products use [`HandlesCatalogSlugs`](../app/Repositories/Support/Concerns/HandlesCatalogSlugs.php) — slug built from `name` when absent, unique per tenant.
- **`created_by`/`updated_by`** filled by the repository from `auth()->id()`.
- **`tenant_id` is automatic** via `BelongsToTenant` (set on create, immutable on update, route-model binding scoped).

For schema, see [database.md](database.md). For permission strings, see [rbac.md](rbac.md).

---

## Categories

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

## Sub-categories

Nested taxonomy under a category. Used by products only — services live directly under a category.

- **Routes** — [routes/tenant.php:65-81](../routes/tenant.php#L65-L81): `index`, `listing`, `save`, `destroy` under `permission:subcategory.{view,create,update,delete}`.
- **Controller**: [`Tenant\SubCategoryController`](../app/Http/Controllers/Tenant/SubCategoryController.php)
- **Validation**: [`SaveSubCategoryRequest`](../app/Http/Requests/Tenant/Catalog/SaveSubCategoryRequest.php)
- **Model**: [`SubCategory`](../app/Models/SubCategory.php) — fields: `category_id`, `name`, `slug`, `code`, `description`, `sort_order`, `is_active`. `belongsTo Category`, `hasMany Product`.
- **Views**: [resources/views/tenant/ecommerce/sub-categories/](../resources/views/tenant/ecommerce/sub-categories/)

## Product types

CRUD-managed product types (Oil, Filter, Part, …). Replaces the former hardcoded `Product::typeOptions()` constants; products now reference a type via `products.product_type_id`. Same modal + DataTable AJAX pattern as Categories.

- **Routes** — under `/tenant/ecommerce/product-types`: `index`, `listing`, `save`, `destroy`, gated on `permission:product-type.{view,create,update,delete}`.
- **Controller**: [`Tenant\ProductTypeController`](../app/Http/Controllers/Tenant/ProductTypeController.php)
- **Validation**: [`SaveProductTypeRequest`](../app/Http/Requests/Tenant/ProductTypes/SaveProductTypeRequest.php)
- **Repository**: [`ProductTypesRepository`](../app/Repositories/ProductTypesRepository.php) implementing [`ProductTypeRepositoryInterface`](../app/Repositories/Interface/ProductTypeRepositoryInterface.php)
- **Policy**: [`ProductTypePolicy`](../app/Policies/ProductTypePolicy.php)
- **Model**: [`ProductType`](../app/Models/ProductType.php) — fields: `name`, `slug`, `code`, `description`, `sort_order`, `is_active`. `hasMany Product`.
- **Views**: [resources/views/tenant/ecommerce/product-types/](../resources/views/tenant/ecommerce/product-types/) + [public/assets/js/tenant/e-com/product-types.js](../public/assets/js/tenant/e-com/product-types.js)
- **Notes**: a type used by ≥1 product cannot be deleted. The product form selects a type via `product_type_id`; the repository keeps the legacy `products.product_type` string in sync with the type's slug so POS catalog feeds and dropdowns that still read it keep working.

## Products

Sellable inventory items. Used as line items on orders and as components inside services.

- **Routes** — [routes/tenant.php:83-102](../routes/tenant.php#L83-L102):
  - `GET  /tenant/ecommerce/products` (perm: `product.view|products.view`)
  - `GET  /tenant/ecommerce/products/listing` (perm: `product.view|products.view`)
  - `GET  /tenant/ecommerce/products/{product}/edit` (perm: `product.update|products.manage`)
  - `POST /tenant/ecommerce/products/save` (perm: `product.create|product.update|products.manage`)
  - `DELETE /tenant/ecommerce/products/{product}` (perm: `product.delete|products.manage`)
- **Controller**: [`Tenant\ProductController`](../app/Http/Controllers/Tenant/ProductController.php) — also reused on the employee panel for in-POS product management (see [orders.md](orders.md)).
- **Validation**: [`SaveProductRequest`](../app/Http/Requests/Tenant/Catalog/SaveProductRequest.php)
- **Repository**: [`ProductsRepository`](../app/Repositories/ProductsRepository.php)
- **Model**: [`Product`](../app/Models/Product.php) — fields:
  - **Identification**: `category_id`, `sub_category_id`, `product_type_id` (FK to managed [Product types](#product-types); legacy `product_type` slug string kept in sync), `name`, `slug`, `sku`, `barcode`, `brand`, `unit`.
  - **Pricing**: `cost_price`, `sale_price` (decimal:2), `tax_percentage` (decimal:2).
  - **Inventory**: `opening_stock`, `current_stock`, `minimum_stock_level`, `reorder_level` (decimal:3), `track_inventory` (bool).
  - **Lifecycle**: `is_active`, `created_by`, `updated_by`.
  - **Type constants**: `inventory`, `oil`, `filter`, `part`, `additive`, `other`.
  - **Search scope**: `name`, `slug`, `sku`, `barcode`, `brand`, plus joined category/sub-category names.
- **Image attachment**: uses [`HasImages`](../app/Models/Concerns/HasImages.php); endpoints under `/tenant/ecommerce/images/` (see [Images](#images-polymorphic)).
- **Views**: [resources/views/tenant/ecommerce/products/](../resources/views/tenant/ecommerce/products/)
- **Inventory note**: `current_stock` is decremented by the POS at checkout and restored on returns — only for products with `track_inventory = true`. See [orders.md](orders.md).

## Services

Service templates (e.g. "Full synthetic oil change"). A service can require certain products as a Bill of Materials (BOM).

- **Routes** — [routes/tenant.php:104-123](../routes/tenant.php#L104-L123): `index`, `listing`, `edit`, `save`, `destroy`, gated on `service.{view,create,update,delete}`.
- **Controller**: [`Tenant\ServiceController`](../app/Http/Controllers/Tenant/ServiceController.php)
- **Validation**: [`SaveServiceRequest`](../app/Http/Requests/Tenant/Catalog/SaveServiceRequest.php)
- **Repository**: [`ServicesRepository`](../app/Repositories/ServicesRepository.php) — uses [`SyncServiceProductsAction`](../app/Actions/Tenant/Services/SyncServiceProductsAction.php) to reconcile the `service_products` pivot on save.
- **Model**: [`Service`](../app/Models/Service.php) — fields:
  - **Identification**: `category_id`, `name`, `code`, `description`.
  - **Pricing**: `standard_price` (decimal:2), `tax_percentage`.
  - **Operations**: `estimated_duration_minutes`, `requires_technician` (bool).
  - **Reminders**: `reminder_interval_days`, `mileage_interval` — for future reminder generation.
  - **Lifecycle**: `is_active`, `created_by`, `updated_by`.
- **Service ↔ Product pivot**: [`ServiceProduct`](../app/Models/ServiceProduct.php) carries `quantity`, `unit`, `is_required` per row. Accessed via `$service->products` (with pivot) or `$service->serviceProducts`.
- **Views**: [resources/views/tenant/ecommerce/services/](../resources/views/tenant/ecommerce/services/)
- **POS use**: services appear on a bill as **service fees** (catalog `type: service`, priced from `standard_price`). See [orders.md](orders.md).

## Discounts

Promotion definitions applicable at bill or item level.

- **Routes** — [routes/tenant.php:125-144](../routes/tenant.php#L125-L144): `index`, `listing`, `edit`, `save`, `destroy`, all gated on `discount.manage`.
- **Controller**: [`Tenant\DiscountController`](../app/Http/Controllers/Tenant/DiscountController.php)
- **Validation**: [`SaveDiscountRequest`](../app/Http/Requests/Tenant/Catalog/SaveDiscountRequest.php)
- **Repository**: [`DiscountsRepository`](../app/Repositories/DiscountsRepository.php)
- **Model**: [`Discount`](../app/Models/Discount.php) — fields:
  - **Identification**: `name`, `code`, `description`.
  - **Configuration**: `discount_type` ∈ `fixed | percentage`; `applies_to` ∈ `bill | item | customer_profile | voucher | promotion`; `value`; `max_discount_amount` (caps percentage discounts).
  - **Validity**: `starts_at`, `ends_at`, `usage_limit`.
  - **Behaviour flags**: `is_active`, `is_combinable`, `requires_reason`, `requires_manager_approval`.
- **Views**: [resources/views/tenant/ecommerce/discounts/](../resources/views/tenant/ecommerce/discounts/)
- **POS use**: only discounts with `applies_to = item` are auto-applied per line at checkout (active + within date window, honouring `max_discount_amount`). A product links its discount via `products.discount_id`. See [orders.md](orders.md).

## Discount groups

Customer-tier discounts: a group grants a percentage or fixed discount once a customer's bill clears a minimum spend. Customers are assigned a group via `customers.discount_group_id`, and the [order checkout](orders.md) applies it automatically.

- **Routes** — [routes/tenant.php:253-266](../routes/tenant.php#L253-L266), under prefix `/tenant/discounts/group` (a separate top-level group, **not** under `ecommerce`):
  - `GET    /tenant/discounts/group` (index, perm: `discount-group.view|discount-group.manage`)
  - `POST   /tenant/discounts/group/store` (perm: `discount-group.manage`)
  - `PUT    /tenant/discounts/group/{discountGroup}` (perm: `discount-group.manage`)
  - `DELETE /tenant/discounts/group/{discountGroup}` (perm: `discount-group.manage`)
- **Controller**: [`DiscountGroupController`](../app/Http/Controllers/DiscountGroupController.php) — note this lives in the root `Http\Controllers\` namespace, not `Tenant\`, and queries the model directly (no repository).
- **Validation**: [`CreateDiscountGroupRequest`](../app/Http/Requests/CreateDiscountGroupRequest.php) — handles create and update; enforces unique `title` per tenant, percentage value ≤ 100, fixed value ≤ `min_limit`.
- **Model**: [`DiscountGroup`](../app/Models/DiscountGroup.php) — fields: `name`, `slug`, `type` (`percentage|fixed`), `value`, `min_limit`, `is_active`. Uses `SoftDeletes`.
- **UI**: modal-driven DataTable — [resources/views/tenant/ecommerce/discounts/group/](../resources/views/tenant/ecommerce/discounts/group/) + [public/assets/js/tenant/discount-groups.js](../public/assets/js/tenant/discount-groups.js). Create and edit share one modal.
- **Dropdown**: `GET /tenant/ecommerce/dropdowns/discount-groups` feeds the customer form's group selector.

---

## Images (polymorphic)

Images attach to any model using the [`HasImages`](../app/Models/Concerns/HasImages.php) trait. Currently only `Product` does. Each `Image` record stores a file on a Laravel disk and tracks metadata (mime, size, sort, primary flag).

Demo products are seeded **with** a primary image: [`TenantCatalogSeeder`](../database/seeders/TenantCatalogSeeder.php) pushes static files from [`database/data/images/products/`](../database/data/images/) onto the `public` disk and writes the matching `images` rows. Run `php artisan storage:link` to expose them over HTTP.

- **Routes** — [routes/tenant.php:188-201](../routes/tenant.php#L188-L201):
  - `POST   /tenant/ecommerce/images/upload` (perm: `product.create|product.update|products.manage`)
  - `DELETE /tenant/ecommerce/images/{image}` (perm: `product.update|products.manage`)
  - `PATCH  /tenant/ecommerce/images/{image}/primary` (perm: `product.update|products.manage`)
- **Controller**: [`Tenant\ImageController`](../app/Http/Controllers/Tenant/ImageController.php)
- **Validation**: [`UploadImageRequest`](../app/Http/Requests/Tenant/Images/UploadImageRequest.php)
- **Service**: [`ImageService`](../app/Services/ImageService.php) handles disk upload, deletion, primary toggle.
- **Helper**: [`FileUploadManager`](../app/Helpers/FileUploadManager.php) wraps file moves.
- **Model**: [`Image`](../app/Models/Image.php) — fields: `imageable_type`, `imageable_id`, `disk`, `path`, `file_name`, `original_name`, `extension`, `mime_type`, `size`, `collection`, `sort_order`, `is_primary`, `uploaded_by`. Auto-deletes the file on model deletion via a `deleting` boot hook.

---

## Dropdown APIs (tenant portal)

The catalog UI populates `<select>` widgets via JSON endpoints under `/tenant/ecommerce/dropdowns/`. They short-circuit when no permission is held. [routes/tenant.php:26-45](../routes/tenant.php#L26-L45), implemented in [`Tenant\DropdownController`](../app/Http/Controllers/Tenant/DropdownController.php):

| Route | Returns | Required permissions (any of) |
|-------|---------|-------------------------------|
| `GET /tenant/ecommerce/dropdowns/categories` | active categories | category/subcategory/product/service view+manage |
| `GET /tenant/ecommerce/dropdowns/sub-categories?category_id=…` | active sub-categories filtered by category | subcategory/product view+manage |
| `GET /tenant/ecommerce/dropdowns/products?sub_category_id=…&category_id=…` | active products | product/service view+manage |
| `GET /tenant/ecommerce/dropdowns/customers` | customers | customer/vehicle/pos view+manage |
| `GET /tenant/ecommerce/dropdowns/vehicles?customer_id=…` | vehicles | vehicle/customer/pos view+manage |
| `GET /tenant/ecommerce/dropdowns/services` | active services | service view + `orders.create`/`pos.bill` |
| `GET /tenant/ecommerce/dropdowns/discounts` | active discounts | discount/product manage |
| `GET /tenant/ecommerce/dropdowns/discount-groups` | active discount groups | `discount-group.view`/`manage` or customer create/update/manage |

> The **employee POS** screen uses a *different* set of catalog feeds (`SharedDataController`, under `/employee/order/...`) — see [orders.md](orders.md).

---

## Quick reference

| Module | Route prefix | Controller | Repository | Permission group |
|--------|--------------|------------|------------|-------------------|
| Categories | `/tenant/ecommerce/categories` | `Tenant\CategoryController` | `CategoriesRepository` | `category.*` |
| Sub-categories | `/tenant/ecommerce/sub-categories` | `Tenant\SubCategoryController` | `SubCategoriesRepository` | `subcategory.*` |
| Product types | `/tenant/ecommerce/product-types` | `Tenant\ProductTypeController` | `ProductTypesRepository` | `product-type.*` |
| Products | `/tenant/ecommerce/products` | `Tenant\ProductController` | `ProductsRepository` | `product.*` / `products.*` |
| Services | `/tenant/ecommerce/services` | `Tenant\ServiceController` | `ServicesRepository` | `service.*` / `services.*` |
| Discounts | `/tenant/ecommerce/discounts` | `Tenant\DiscountController` | `DiscountsRepository` | `discount.*` / `discounts.*` |
| Discount groups | `/tenant/discounts/group` | `DiscountGroupController` | direct queries | `discount-group.view` / `discount-group.manage` |
| Images | `/tenant/ecommerce/images` | `Tenant\ImageController` | (uses `ImageService`) | inherits product perms |
| Dropdowns | `/tenant/ecommerce/dropdowns` | `Tenant\DropdownController` | direct queries | inherits owning module |

**Next in the journey:** [customers.md](customers.md) — the people and vehicles a bill is made out to.
</content>
