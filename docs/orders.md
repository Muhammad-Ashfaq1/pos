# Orders — Employee POS Lifecycle

The full lifecycle of a POS bill on the **employee panel**: building a cart, saving an **estimate**, **checkout**, **partial payments**, **invoices/receipts** (print / PDF / email), and **returns & refunds**. Everything lives under `/employee/*` and is driven by:

- [`Employee\OrderController`](../app/Http/Controllers/Employee/OrderController.php) — thin HTTP layer (request validation, policy guards, view rendering).
- [`OrdersRepository`](../app/Repositories/OrdersRepository.php) — **all** money / stock / status logic. Bound to [`OrderRepositoryInterface`](../app/Repositories/Interface/OrderRepositoryInterface.php) in [`AppServiceProvider`](../app/Providers/AppServiceProvider.php).
- [`Employee\PanelController`](../app/Http/Controllers/Employee/PanelController.php) — the employee dashboard landing screen (see [dashboards.md](dashboards.md)).

The cart is fed from the catalog defined in [catalog.md](catalog.md), billed to a customer/vehicle from [customers.md](customers.md). For schema, see [database.md](database.md).

---

## Access & middleware

[routes/employee.php:7](../routes/employee.php#L7) wraps every employee route in:

```php
['auth', 'verified', 'active.user', 'employee.panel', 'tenant.init']
```

- `employee.panel` ([`EnsureEmployeePanelAccess`](../app/Http/Middleware/EnsureEmployeePanelAccess.php)) restricts access to staff-tier roles (manager / cashier / technician / inventory_clerk / employee) or users with explicit access.
- `tenant.init` initialises tenancy from the user's `tenant_id`.
- All order/item/payment rows are tenant-scoped via the `BelongsToTenant` global scope — cross-tenant records are invisible and unroutable.

> **No `OrderPolicy` exists.** Access is gated solely by route-level `permission:` middleware plus the tenant scope. There is no per-order ownership check beyond "same tenant".

---

## Order statuses

Constants on [`Order`](../app/Models/Order.php):

| Constant | Value | Meaning |
|----------|-------|---------|
| `STATUS_ESTIMATE` | `estimate` | Quote only. No payment taken, no stock deducted. Numbered `EST-…`. |
| `STATUS_PENDING` | `pending` | Saved with zero payment. Awaiting money. |
| `STATUS_PARTIALLY_PAID` | `partially_paid` | Some money collected, balance remains. |
| `STATUS_PAID` | `paid` | Fully settled; `paid_at` stamped. |
| `STATUS_RETURNED` | `returned` | Every item refunded back to the customer. |

`paymentAwareStatus()` ([OrdersRepository](../app/Repositories/OrdersRepository.php#L887-L906)) re-derives the effective status from `payment_amount` vs `total_amount` at read time, so listings/details stay correct even if the stored `status` lags.

---

## Routes

[routes/employee.php](../routes/employee.php) — all under the `employee.order.` name prefix unless noted.

| Route | Handler | Permission (any of) | Purpose |
|-------|---------|---------------------|---------|
| `GET /employee/dashboard` | `PanelController@dashboard` | `dashboard.view` | Employee landing screen |
| `GET /employee/order` | `OrderController@index` | `orders.view` | Order listing page (tabs: Today / All / Pending / Estimates) |
| `GET /employee/order/listing` | `OrderController@listing` | `orders.view` | JSON: filtered/sorted/searched orders + tab counts |
| `GET /employee/order/new` | `OrderController@create` | `orders.create` \| `pos.bill` | Renders the new-order POS screen |
| `POST /employee/order/save` | `OrderController@store` | `orders.create` \| `pos.bill` | Persists the cart as an `Order` (or estimate) |
| `GET /employee/order/{order}` | `OrderController@show` | `orders.view` | Order detail / receipt view |
| `POST /employee/order/{order}/pay` | `OrderController@pay` | `orders.create` \| `pos.bill` | Add a payment / convert an estimate into an order |
| `GET /employee/order/{order}/print` | `OrderController@print` | `orders.view` | Printable receipt (auto-print Blade) |
| `GET /employee/order/{order}/pdf` | `OrderController@pdf` | `orders.view` | Download invoice/estimate PDF (DomPDF) |
| `POST /employee/order/{order}/share` | `OrderController@share` | `orders.view` | Email the PDF to a customer address |
| `GET /employee/order/returns` | `OrderController@returns` | `orders.view` | Returns screen |
| `GET /employee/order/returns/listing` | `OrderController@returnsListing` | `orders.view` | JSON: returnable orders inside the window |
| `GET /employee/order/returns/history` | `OrderController@returnsHistory` | `orders.view` | JSON: previously returned/refunded orders |
| `POST /employee/order/{order}/return` | `OrderController@processReturn` | `orders.create` \| `pos.bill` | Process a (partial/full) return + refund |

**Catalog feeds for the cart** ([`SharedDataController`](../app/Http/Controllers/SharedDataController.php), under `/employee/order/`, gated `orders.create|pos.bill`):

| Route | Handler | Purpose |
|-------|---------|---------|
| `GET /employee/order/categories` | `categories` | JSON: active categories (optional `?q=`) |
| `GET /employee/order/sub-categories` | `subCategories` | JSON: sub-categories by `?category_id=` |
| `GET /employee/order/products` | `products` | JSON: products by `?sub_category_id=` / `?category_id=` |
| `GET /employee/order/search` | `search` | JSON: union search across categories/sub-categories/products (limit 20/20/40) |

**In-POS product management** ([`Tenant\ProductController`](../app/Http/Controllers/Tenant/ProductController.php) reused under `/employee/products/`): `index`, `listing`, `{product}/edit`, `save`, `destroy` — gated on `product.*` / `products.*` so a cashier can fix catalog data without leaving the panel. See [catalog.md](catalog.md#products).

---

## Relevant tenant settings

Two [`Tenant`](../app/Models/Tenant.php#L223-L231) setting helpers shape this flow (see [settings.md](settings.md)):

| Helper | Setting key | Default | Effect |
|--------|-------------|---------|--------|
| `isVehicleRequired()` | `orders.vehicle_required` | `true` | When true, `vehicle_id` is **required** on save. |
| `returnDaysAfterPurchase()` | `orders.return_days_after_purchase` | `30`* | Return window measured from `paid_at`. |

\* The helper default is `30`, while the tenant defaults seed (`Tenant.php#L46`) sets `7` for new shops. Both `OrderController::create` and the return guards read the live value.

---

## Creating an order

### 1. The POS screen

`create()` ([OrderController](../app/Http/Controllers/Employee/OrderController.php#L31-L39)) renders [`employee/order/new-order.blade.php`](../resources/views/employee/order/new-order.blade.php), passing `vehicleRequired` and `returnDaysAfterPurchase` so the JS enforces them client-side. Layout: [`layouts/employee-portal.blade.php`](../resources/views/layouts/employee-portal.blade.php). It uses Select2 + axios to drill **category → sub-category → product** (or the free-text search box hitting `/employee/order/search`), and the cashier:

1. Selects a **customer** and (if required) a **vehicle**.
2. Adds product **line items** to the cart.
3. Optionally adds **service fees**: catalog services (`type: service`, priced from the service) or ad-hoc **manual** fees (`type: manual`, free-text name + amount).
4. Either **saves an estimate** or **proceeds to payment**.

### 2. Validation — `SaveOrderRequest`

[`SaveOrderRequest`](../app/Http/Requests/Employee/Orders/SaveOrderRequest.php) normalizes and validates:

- `customer_id` — required, must exist within the tenant, must have *real* details (not the default walk-in placeholder).
- `vehicle_id` — required only when `isVehicleRequired()`; if present must belong to the selected customer (checked in `withValidator`).
- `items[]` — 1–200 lines; each `product_id` an active tenant product; `quantity` 1–9999.
- `service_fees[]` — up to 50; `type` ∈ `service|manual`; duplicates rejected; manual fees require name + positive amount.
- `payment` — required unless `is_estimate`; `payment.method` ∈ `cash|card|check`, `payment.amount` ≥ 0.
- `tenant_id`, `created_by`, `updated_by` are **prohibited** in the request (set server-side).

When `is_estimate` is true, `prepareForValidation()` strips the `payment` block.

### 3. Persistence — `OrdersRepository::store()`

[`store()`](../app/Repositories/OrdersRepository.php#L59-L247) runs everything inside one `DB::transaction`:

1. **Load & lock products.** Active products by id; non-estimates use `lockForUpdate()` so concurrent sales can't oversell.
2. **Stock check.** For `track_inventory` products, `validateStockAvailability()` throws if requested qty exceeds `current_stock`. Skipped for estimates.
3. **Per-line maths.** `unit_price = product.sale_price`, line subtotal, **item discount** (`productDiscountAmount()` — only `Discount` rows with `applies_to = item`, active, within date window; percentage/fixed honouring `max_discount_amount`), and a per-line tax line.
4. **Service fees.** `serviceFees()` resolves catalog services (re-priced from `standard_price`) and manual fees, with their own tax lines.
5. **Customer discount group.** `customerDiscount()` applies the customer's `DiscountGroup` (percentage/fixed) **only if** the bill clears the group's `min_limit`.
6. **Progressive tax allocation.** `taxSummary()` spreads the customer-discount across taxable lines proportionally, then taxes the net of each line. The breakdown is stored in `orders.discount_details`.
7. **Status & change.** Real order → `paid` / `partially_paid` / `pending` from payment vs total; `change_amount` = overpayment; `paid_at` stamped when fully paid. Estimate → `estimate`, zero payment.
8. **Write.** Creates the `Order` (number via `makeOrderNumber('ORD'|'EST')` — `PREFIX-{Ymd-His}-{rand}`, uniqueness-checked), `OrderItem` rows (snapshotting `product_name`/`sku`/`unit`), an `OrderPayment` row when money is taken, and **deducts tracked stock** — all skipped appropriately for estimates.

```
new-order.blade  ──POST /save──▶  SaveOrderRequest  ──▶  OrdersRepository::store()
                                                            │  (one transaction)
                                                            ├─ lock + stock check
                                                            ├─ item discounts
                                                            ├─ service fees
                                                            ├─ customer-group discount
                                                            ├─ progressive tax
                                                            ├─ Order + OrderItems
                                                            ├─ OrderPayment (if paid)
                                                            └─ deduct tracked stock
```

---

## Estimates

An estimate is a saved quote (`status = estimate`, `EST-…` number) with **no payment and no stock movement**, shown under the listing's **Estimates** tab. To turn it into a real sale the cashier takes a payment via `pay` (below), which validates and deducts stock *at conversion time* and re-numbers it `ORD-…`.

---

## Payments & estimate conversion — `pay`

`POST /{order}/pay` → [`OrderController::pay`](../app/Http/Controllers/Employee/OrderController.php#L89-L101) validates `payment_method` ∈ `cash|card|check` and `payment_amount` > 0, then calls [`addPayment()`](../app/Repositories/OrdersRepository.php#L929-L1027). In one transaction:

- **Already paid?** → rejected.
- **Estimate?** → *converts*: re-locks products, re-validates & **deducts stock**, assigns a fresh `ORD-…` number, sets `paid`/`partially_paid` from the amount.
- **Otherwise** (pending/partial) → *tops up*: adds to `payment_amount`, recomputes status and `change_amount`.

Every call appends an `OrderPayment` row, building the payment history shown on the detail view (with collector name + method).

---

## Invoices & receipts: show / print / pdf / share

- **`show`** → [`employee/order/show.blade.php`](../resources/views/employee/order/show.blade.php) via `OrdersRepository::details()` — full breakdown: items, tax lines, discounts, service/manual fees, balance due, payment history.
- **`print`** → [`print.blade.php`](../resources/views/employee/order/print.blade.php) (auto-print wrapper).
- **`pdf`** → DomPDF download of [`pdf.blade.php`](../resources/views/employee/order/pdf.blade.php); filename is `estimate-…` or `invoice-…` by status.
- **`share`** → validates an email, updates the customer's email if changed, renders the same PDF, emails it via [`ShareOrderMail`](../app/Mail/ShareOrderMail.php).

---

## Returns & refunds

Returns support **partial** and **full** quantities. There is **no dedicated returns table** — each return is recorded as a JSON entry appended to `orders.notes`.

### Returns screen

`returns()` ([OrderController](../app/Http/Controllers/Employee/OrderController.php#L161-L169)) renders [`returns.blade.php`](../resources/views/employee/order/returns.blade.php) with the active `returnDays`.

**`returnsListing()`** ([repo](../app/Repositories/OrdersRepository.php#L1029-L1079)) lists candidates:
- status `paid` or `partially_paid`,
- `paid_at` within the last `returnDays`,
- not already fully returned (reconstructs already-returned quantities from `notes` and drops orders where everything is back).

Each returnable order exposes per-item `quantity`, `returned_quantity`, `available_quantity` so the UI can cap inputs.

**`returnsHistory()`** ([repo](../app/Repositories/OrdersRepository.php#L1081-L1128)) lists orders that are `returned` or carry return notes, surfacing refund amount/method/time from the negative payment record.

### Processing a return — `processReturn`

`POST /{order}/return` → [`OrderController::processReturn`](../app/Http/Controllers/Employee/OrderController.php#L192-L233) applies **guards** first:

1. Within the return window (`paid_at.diffInDays(now) ≤ returnDays`).
2. Not already `returned`.
3. Status must be `paid` or `partially_paid`.

Then validates `return_reason`, `refund_method` (`cash|card|check`), `return_items[]` (`order_item_id` + `quantity ≥ 1`), `refund_amount > 0`, and hands off to [`OrdersRepository::processReturn()`](../app/Repositories/OrdersRepository.php#L1154-L1277), which in one transaction:

1. Reconstructs already-returned quantities from `notes`; rejects any line exceeding `original − already_returned`.
2. Computes refund: `Σ(unit_price × qty)` **minus a proportional share of the order discount**. **Tax and service fees are not refunded.** The client-supplied `refund_amount` must match within ±0.05 (rounding), else rejected.
3. If cumulative returned quantity now equals the whole order, sets status → `returned`.
4. Records a **negative-amount `OrderPayment`** (`-refund_amount`) as the refund ledger entry.
5. Appends a return record to `notes` (JSON array): `{ return_reason, refund_method, refund_amount, returned_items: {item_id: qty}, returned_at }`.
6. **Restores stock** for tracked products by the returned quantities.

```
returns.blade  ──POST /{order}/return──▶  controller guards (window/status)
                                            └─▶ OrdersRepository::processReturn()
                                                  ├─ qty ≤ remaining (from notes)
                                                  ├─ refund = Σ(price·qty) − prop. discount
                                                  ├─ status → returned (if fully returned)
                                                  ├─ OrderPayment  (negative = refund)
                                                  ├─ append return record to notes JSON
                                                  └─ restore tracked stock
```

### Design notes & caveats

- **Returns live in `notes`.** `getAlreadyReturnedQuantities()` parses the JSON; `returnsHistory` even uses `whereJsonLength('notes', …)`. No `order_returns` table, so reporting on returns means walking JSON.
- **Refund excludes tax & service fees.** Only item prices (net of proportional discount) are refunded.
- **Refunds are negative payments.** Net collected for an order = `SUM(order_payments.amount)` (positive sales + negative refunds).
- **No partial-payment refund check.** A `partially_paid` order can be returned even if the refund exceeds what was actually collected — the guard checks status, not paid amount.
- **`refunds.manage` permission** is defined but unused; returns are currently gated by `orders.create|pos.bill`.

---

## Listing, search & sort

`listing()` ([repo](../app/Repositories/OrdersRepository.php#L22-L44)) powers the order index: tab counts (`today`, `all`, `pending`, `estimates`) plus up to 100 orders. Filters: free-text `q` (default search spans order number, status, customer, vehicle, items) or field-scoped search (`order_number`, `customer_id`, `paid_status`, `retailer`, `time`, `date`), date range, and sort options (`latest`, `oldest`, `amount_*`, `customer_name`, `date_opened`, `order_id`, `order_total`). Estimates are excluded from every non-estimate tab.

---

## Related schema

See [database.md](database.md#orders) for full column lists.

| Table | Role |
|-------|------|
| `orders` | The bill. Money totals, status, `discount_details` (JSON breakdown), `notes` (JSON — incl. return records). |
| `order_items` | Snapshotted line items (name/sku/unit frozen at sale time). |
| `order_payments` | Append-only payment ledger. Positive = collection, **negative = refund**. |

**Next in the journey:** [dashboards.md](dashboards.md) — how these orders roll up into analytics for staff, owners, and the platform admin.
</content>
