# Dashboards & Super-Admin

Where the data created across the app surfaces back as insight: the **employee** panel landing, the **tenant** analytics dashboard for owners/managers, and the **super-admin** platform view that oversees every shop. All figures are tenant-scoped (except the super-admin tier) via the `BelongsToTenant` global scope.

---

## Employee dashboard

`GET /employee/dashboard` ([`Employee\PanelController@dashboard`](../app/Http/Controllers/Employee/PanelController.php), perm `dashboard.view`) is the cashier/technician landing screen inside the POS panel. It sits behind the same `employee.panel` middleware stack as the rest of the order flow — see [orders.md](orders.md#access--middleware).

---

## Tenant dashboard

`GET /tenant/dashboard` ([`Tenant\DashboardController`](../app/Http/Controllers/Tenant/DashboardController.php)) renders a data-driven analytics dashboard for shop owners/managers (employees are redirected to their own panel). Date-range filtering is applied via AJAX.

- **Aggregation**: [`TenantDashboardService`](../app/Services/TenantDashboardService.php) builds the payload — stat cards (total sales, collected, outstanding, orders, customers, products, low-stock, avg order, items sold), a 12-month revenue/orders trend, orders-by-status, payment-method split, top products, sales-by-category, customers-by-type, a revenue breakdown (net/tax/fees/discounts), recent orders, low-stock alerts, and an application overview (shop status, onboarding, currency, timezone, catalog/record counts).
- **Charts**: rendered with **ApexCharts** (bundled in [layouts/app.blade.php](../resources/views/layouts/app.blade.php)) by [public/assets/js/tenant/dashboard.js](../public/assets/js/tenant/dashboard.js), reading the server payload from `window.dashboardData` and theme colours from `window.config.colors`.
- **View**: [resources/views/tenant/dashboard.blade.php](../resources/views/tenant/dashboard.blade.php) (Vuexy cards + chart containers).

The revenue figures here are the roll-up of the [order flow](orders.md): collected = sum of positive payments, outstanding = unpaid balances, and the net/tax/fees/discounts split comes from each order's stored `discount_details`.

---

## Super-admin module

The platform-operator tier. Routes live under `/admin/*` ([routes/admin.php](../routes/admin.php)):

| Route | Handler | Purpose |
|-------|---------|---------|
| `GET /admin/dashboard` | [`Admin\DashboardController`](../app/Http/Controllers/Admin/DashboardController.php) | Platform overview across all tenants |
| `GET /admin/shops` | [`Admin\TenantController@index`](../app/Http/Controllers/Admin/TenantController.php#L19-L29) | List all tenants with their admin user |
| `POST /admin/shops/{tenant}/status/{action}` | [`@changeStatus`](../app/Http/Controllers/Admin/TenantController.php#L31-L42) | approve / reject / suspend / reactivate |
| `GET /admin/shops/impersonate/{tenant}` | [`@impersonate`](../app/Http/Controllers/Admin/TenantController.php#L44-L65) | Log in as the tenant admin |
| `GET /admin/impersonate/stop` | [`@stopImpersonate`](../app/Http/Controllers/Admin/TenantController.php#L67-L77) | Restore the original session |

The status transitions, approval flow, and impersonation mechanics are described in detail in [auth-and-onboarding.md](auth-and-onboarding.md#super-admin-approval).

---

## Quick reference

| Surface | Route | Controller | Audience |
|---------|-------|------------|----------|
| Employee dashboard | `/employee/dashboard` | `Employee\PanelController` | staff (cashier/technician/…) |
| Tenant dashboard | `/tenant/dashboard` | `Tenant\DashboardController` | shop owner/manager |
| Admin dashboard | `/admin/dashboard` | `Admin\DashboardController` | super admin |
| Admin shops | `/admin/shops` | `Admin\TenantController` | super admin |

For the **filterable, row-level, exportable** view of this same data (sales, payments, products, customers), see **[reports.md](reports.md)**.

This is the end of the journey. For the underlying schema behind every figure here, see **[database.md](database.md)**.
</content>
