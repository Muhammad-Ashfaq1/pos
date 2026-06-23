# Reports

Dynamic, filterable, **Excel-exportable** reports that turn the operational data (orders, payments, products, customers) into analysis-ready tables. One report engine is shared by **both** staff surfaces — the tenant admin portal (`/tenant/reports/*`) and the employee panel (`/employee/reports/*`) — gated by the `reports.view` permission. Every figure is tenant-scoped through the `BelongsToTenant` global scope.

This complements [dashboards.md](dashboards.md): dashboards give a fixed at-a-glance roll-up; reports give a filterable, row-level, exportable view of the same underlying data.

---

## What ships today

| Report | Key | Model | Highlights |
|--------|-----|-------|-----------|
| Sales / Orders | `sales` | [`Order`](../app/Models/Order.php) | Real sales only (estimates excluded); status / payment-method / retailer / amount filters; gross, collected, outstanding summary. |
| Payments / Collections | `payments` | [`OrderPayment`](../app/Models/OrderPayment.php) | The append-only ledger — collections (+) and refunds (−); method / collector / type filters; collected, refunded, net summary. |
| Products / Inventory | `products` | [`Product`](../app/Models/Product.php) | Stock levels, low-stock flag; category / status / tracked / low-stock filters; product count, low-stock count, stock value. |
| Customers | `customers` | [`Customer`](../app/Models/Customer.php) | Visits, lifetime value, credit balance; type / discount-group / has-credit filters; count, total LTV, outstanding credit. |

Each report supports a **date-range** filter (presets Today / Yesterday / Last 7 Days / This Month / This Year / Custom) on a selectable date column (e.g. Sales: order date vs paid date; Customers: created vs last visit). The default date column is `created_at`.

---

## Architecture

```
Request (tenant OR employee)
  → ReportController (single, surface-agnostic)
     → ReportsRepository::data() / ::query()      ← DataTables JSON + shared filtered query
        → ReportRegistry → ReportDefinition         ← per-dataset config
           → model query + FiltersByDateRange scope + declared per-field filters
  → ReportExport (one Maatwebsite export, reuses the SAME definition + query)
```

A **report definition** is the single source of truth for a dataset — its base query, date columns, filters, sortable columns, the column map (key → label → value resolver, used by both the table and the export) and summary aggregates. Adding a new report touches **one class plus one registry line**; the controller, repository, exporter, routes and JS are all generic.

### Key files

| Concern | File |
|---------|------|
| Reusable date-range scope (global) | [`FiltersByDateRange`](../app/Models/Concerns/FiltersByDateRange.php) — `scopeDateRange()`, `scopeWithinRange()` |
| Report base class | [`ReportDefinition`](../app/Reports/ReportDefinition.php) — builds the filtered query, ordering, row/heading maps |
| Report datasets | [`SalesReport`](../app/Reports/SalesReport.php), [`PaymentsReport`](../app/Reports/PaymentsReport.php), [`ProductsReport`](../app/Reports/ProductsReport.php), [`CustomersReport`](../app/Reports/CustomersReport.php) |
| Filter-option lookups | [`ReportOptions`](../app/Reports/Support/ReportOptions.php) — tenant-scoped staff / categories / discount-groups |
| Registry | [`ReportRegistry`](../app/Reports/ReportRegistry.php) — key ⇒ definition, tabs, default key |
| Repository (DataTables) | [`ReportsRepository`](../app/Repositories/ReportsRepository.php) ⇐ [`ReportRepositoryInterface`](../app/Repositories/Interface/ReportRepositoryInterface.php), bound in [`AppServiceProvider`](../app/Providers/AppServiceProvider.php) |
| Excel export | [`ReportExport`](../app/Exports/ReportExport.php) — `maatwebsite/excel` `FromQuery`/`WithHeadings`/`WithMapping` |
| Controller (both ends) | [`ReportController`](../app/Http/Controllers/ReportController.php) |
| Request validation | [`ReportFilterRequest`](../app/Http/Requests/Reports/ReportFilterRequest.php) |
| View + JS | [`reports/index.blade.php`](../resources/views/reports/index.blade.php), [`reports/index.js`](../public/assets/js/reports/index.js) |

### The reusable date-range scope

`FiltersByDateRange` is intentionally generic — `use FiltersByDateRange;` on any model adds:

```php
Order::dateRange('2026-01-01', '2026-01-31')->get();   // explicit bounds
Order::dateRange($from, $to, 'paid_at')->get();        // any date column
Order::withinRange($range)->get();                     // a DashboardDateRange value object
```

It reuses the same [`DashboardDateRange`](../app/Support/DashboardDateRange.php) value object that powers the dashboard date filter. Applied to `Order`, `OrderPayment`, `Product`, `Customer`.

---

## Routes

Registered identically under both groups — same controller, gated `permission:reports.view`:

| Method | Tenant | Employee | Handler | Purpose |
|--------|--------|----------|---------|---------|
| GET | `/tenant/reports/{report}` | `/employee/reports/{report}` | `index` | Report screen (filter bar + DataTable) |
| GET | `/tenant/reports/{report}/data` | `/employee/reports/{report}/data` | `data` | Server-side DataTables JSON + summary |
| GET | `/tenant/reports/{report}/export` | `/employee/reports/{report}/export` | `export` | `.xlsx` download of the filtered set |

The controller derives the surface (and thus the Blade layout — `layouts.app` vs `layouts.employee-portal` — and the listing/export URLs) from the matched **route-name prefix** (`tenant.` vs `employee.`). Defined in [routes/tenant.php](../routes/tenant.php) and [routes/employee.php](../routes/employee.php).

> **Param-name caveat:** the date-range bounds are sent as `date_from` / `date_to`, **not** `start` / `end`, because DataTables already uses `start` (paging offset) and `length` in the same request. Mixing them silently breaks paging.

---

## Filtering & export parity

- The DataTable request carries `period`, `date_from`/`date_to`, `date_column`, the per-report filter keys, plus DataTables' own `draw`/`start`/`length`/`order`/`columns`/`search`.
- The repository's `query()` builds the fully-filtered query **once**; the listing and the export both consume it, so the downloaded `.xlsx` matches the on-screen, filtered table exactly. The export's headings and each cell come from the same column resolvers as the table.
- The front-end ([reports/index.js](../public/assets/js/reports/index.js)) renders dynamic columns/filters from `window.reportConfig`, applies Select2 to the filter dropdowns (matching the app-wide dropdown UI), drives a debounced search box, syncs the active filters onto the Export button's href, and only **enables Export when the filtered set has rows**.

---

## Permissions

Gated by the existing `reports.view` permission. As seeded in [RolePermissionSeeder](../database/seeders/RolePermissionSeeder.php), it is granted to **super_admin, tenant_admin, manager, cashier, technician, inventory_clerk, and the generic employee** role (not `customer`). Changes propagate on merge to `develop` via the `app:sync-permissions` step in [deploy.yml](../.github/workflows/deploy.yml) — see [rbac.md](rbac.md).

---

## Adding a new report

1. Create `app/Reports/MyReport.php` extending [`ReportDefinition`](../app/Reports/ReportDefinition.php); implement `key()`, `label()`, `model()`, `columns()`, and override `filters()` / `sortable()` / `dateColumnOptions()` / `summary()` / `applySearch()` as needed.
2. Add the class to the `REPORTS` list in [`ReportRegistry`](../app/Reports/ReportRegistry.php).

That's it — routes, controller, repository, exporter, view and JS are all driven off the definition. For bounded filter dropdowns that need DB lookups (staff, categories, …), add a tenant-scoped helper to [`ReportOptions`](../app/Reports/Support/ReportOptions.php).

---

## Verification

Feature coverage lives in [tests/Feature/Tenant/ReportsTest.php](../tests/Feature/Tenant/ReportsTest.php): page render, permission gating, date-range filtering, a status filter, estimate exclusion, the `.xlsx` download, and unknown-key 404. Run with:

```bash
php artisan test tests/Feature/Tenant/ReportsTest.php
```

**Next in the journey:** [database.md](database.md) — the schema behind every figure here.
