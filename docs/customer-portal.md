# Customer Portal & Store-Credit Loyalty

The customer portal lets a shop's customers sign in (web **and** future Flutter app) to view their service history and a **store-credit wallet**. Credit is **earned** automatically when an order is paid and can be **redeemed** against a future order's total. Everything is tenant-scoped: a customer belongs to the shop where they are serviced, and credit is earned/spent at that shop only.

## Reward model: store credit (not direct discount)

A customer's **discount group** (`DiscountGroup`, the existing "customer group") can now do two independent things:

1. **Direct discount** on the order total at checkout — unchanged (`type`, `value`, `min_limit`).
2. **Earn store credit** on each paid visit — new (`earns_credit`, `credit_earn_type`, `credit_earn_rate`, `credit_min_spend`).

Store credit is **monetary** and lives on `customers.credit_balance`. Every change is written to an append-only ledger, `customer_credit_transactions` (`earn | redeem | adjust | expire`, signed `amount`, `balance_after`).

- **Earning**: when an order first reaches `paid`, [`OrdersRepository::applyVisitAndEarn()`](../app/Repositories/OrdersRepository.php) computes pre-tax net spend (`subtotal + service_fee − discount`) and grants `DiscountGroup::creditEarnedFor()` via [`CreditService::earn()`](../app/Services/CreditService.php). It also bumps `total_visits` / `lifetime_value` / `last_visit_at`. Earning is once-per-order.
- **Redeeming**: at checkout or on a later payment, `payment.credits_applied` (or `credits_applied` on the pay route) is capped at the wallet balance and the balance due, recorded as an `OrderPayment` with `payment_method = store_credit` plus `orders.credit_applied`, and deducted via [`CreditService::redeem()`](../app/Services/CreditService.php). It counts toward `payment_amount`, so the existing balance-due/status logic is unchanged.
- **Unlock threshold**: redeem is allowed only when `customers.credit_balance >=` tenant setting `orders.credit_min_redeem_balance` (default **50**, shop currency). After unlock, partial redeem is OK. Enforced in `CreditService::redeem`, `SaveOrderRequest`, new-order checkout, and order-detail Pay Balance.

> `CreditService` is the **only** place the wallet mutates — it locks the customer row and writes balance + ledger in one transaction. Never write `credit_balance` directly (the staff customer form's raw field is legacy; prefer the **Adjust** action which goes through `CreditService::adjust`).

## Authentication (Sanctum, tenant-scoped)

`Customer` is now an `Authenticatable` with `HasApiTokens`. Login is **per shop** because the same email may exist at multiple shops:

- A guest request includes `shop` = the tenant slug. The API resolves the tenant (central, unscoped), finds that tenant's customer, verifies the password + `portal_enabled`, then issues a Sanctum Bearer token.
- Authenticated requests run through [`InitializeTenancyForCustomer`](../app/Http/Middleware/InitializeTenancyForCustomer.php) (`customer.tenant.init`), which initializes tenancy from the token's customer so `BelongsToTenant` scopes every query to that shop.

Account creation supports **both** self-registration (`POST /register`) and staff **invite** (toggle in the customer form → emailed set-password link). Invites and forgot-password share a hashed, expiring token on the customer row, issued by [`CustomerPortalService`](../app/Services/CustomerPortalService.php).

## API (`/api/v1/customer`) — reused by web + Flutter

Defined in [routes/api.php](../routes/api.php); controllers in `app/Http/Controllers/Api/Customer/`; JSON shapes in `app/Http/Resources/Customer/`. **The web portal and the Flutter app call these identical endpoints — no logic is duplicated.**

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| POST | `/login` | guest | Token login (`shop`, `email`, `password`) |
| POST | `/register` | guest | Self-registration |
| POST | `/forgot-password` | guest | Email a reset link |
| POST | `/reset-password` | guest | Set password from invite/reset token |
| POST | `/logout` | token | Revoke current token |
| GET | `/me` / PATCH `/me` | token | Profile + balance |
| GET | `/orders` / `/orders/{id}` | token | Service history (detail reuses `OrdersRepository::details()`) |
| GET | `/credits` | token | Wallet balance + ledger |
| GET | `/vehicles` | token | Customer vehicles |

Guest routes are throttled (`throttle:10,1`).

## Web portal (session, server-rendered)

The web portal uses the **same `/login` form as staff** — there is no separate customer login page and no shop code on the web. [`AuthController::loginSubmit`](../app/Http/Controllers/Auth/AuthController.php) tries the staff `web` guard first, then falls back to the `customer` session guard (`portal_enabled` only); a successful customer login redirects to `/portal`. Logout and the `/` root route are customer-guard aware.

[routes/customer.php](../routes/customer.php) serves server-rendered Blade pages under `/portal` (views in `resources/views/customer/`, layout [customer-portal.blade.php](../resources/views/layouts/customer-portal.blade.php), styles [customer-portal.css](../public/assets/css/customer-portal.css)), protected by `auth:customer` + `customer.tenant.init`. Pages:

| Path | Purpose |
|------|---------|
| `/portal` | Overview — credit balance, unlock messaging, visits / lifetime spend, recent orders |
| `/portal/orders` | Service history |
| `/portal/orders/{id}` | Order detail + **Download PDF** |
| `/portal/orders/{id}/pdf` | Invoice PDF (own orders only; same template as employee) |
| `/portal/credits` | Wallet + ledger filters (All / Earned / Redeemed / Adjusted / Expired) |
| `/portal/vehicles` | Vehicles on file (read-only) |
| `/portal/profile` | Redirects to shared `/account/profile` |
| `/account/profile` | Profile (shared UI for admin, employee, customer) |
| `/account/password` | Change password (shared UI) |
| `/portal/reset` | Public set-password (invite / forgot links) |

Pages reuse the same repositories/services as everything else (`OrdersRepository::details()`, `CustomerCreditTransaction`, `CustomerPortalService`), so there is no parallel data layer. The token API is **only** for Flutter; the web portal does not use it.

> The `shop` slug is still required by the **token API** (`/login`, `/register`, `/reset-password`) because those are stateless and cross-origin. The web portal never needs it.

## Demo accounts (local)

After `CustomerPortalDemoSeeder`, password for every demo customer is `password`:

| Email | Notes |
|-------|--------|
| `olivia@obtainsolutions.com` | Credit usually unlocked (≥ $50) |
| `marcus@obtainsolutions.com` | Often below unlock threshold |
| `priya@obtainsolutions.com` | Often below unlock threshold |

## QA checklist (E2)

Use a private/incognito window. Base URL: `/login` → customer lands on `/portal`.

### Auth & access
- [ ] Customer with `portal_enabled` + password signs in via shared `/login` and reaches Overview
- [ ] Customer without portal / wrong password sees login error (not staff dashboard)
- [ ] Guest hitting `/portal` redirects to login
- [ ] Sign out returns to login; `/portal` no longer accessible

### Portal UI chrome
- [ ] Sticky header, AutoServe brand, active tab highlight (Overview / Service History / Store Credit / Vehicles / Profile)
- [ ] Mobile: tabs remain usable (icons); sign-out works

### Overview & credits
- [ ] Balance matches staff customer credit panel
- [ ] If balance **&lt;** shop unlock setting (default $50): message “Usable when balance reaches …” — no redeem on POS
- [ ] If balance **≥** unlock: “Ready to use at checkout”
- [ ] Credits page filters (All / Earned / Redeemed / Adjusted / Expired) change the list
- [ ] Earn rows show order link + running balance

### Vehicles
- [ ] Vehicles tab lists that customer’s vehicles (plate / year make model / default badge)
- [ ] Empty state when none on file

### Profile
- [ ] Name / phone / address save and persist after refresh
- [ ] Email is read-only
- [ ] Change password: wrong current password errors; correct current + matching new password succeeds; can sign in with new password

### Orders & PDF
- [ ] Service history lists non-estimate orders; detail shows items, totals, credit earned/applied
- [ ] **Download PDF** downloads `invoice-{order_number}.pdf` and opens with shop + line items
- [ ] Another customer’s order id / PDF URL returns **404**

### POS store credit (staff)
- [ ] Shop setting **Order & Invoice → Store Credit Unlock Balance** saves
- [ ] New order: store credit control hidden/disabled below unlock; enabled when unlocked; Max applies; checkout deducts ledger
- [ ] Order Pay Balance: same unlock gate + Max; partial redeem OK after unlock
- [ ] Over-redeem beyond balance or balance due is rejected

### Automated smoke
```bash
php artisan test --filter=CustomerPortalCreditTest
```

## Staff side (tenant portal)

- **Customer form** ([save-modal.blade.php](../resources/views/tenant/ecommerce/customers/partials/save-modal.blade.php)): a "Portal & Store Credit" panel (edit mode) shows the balance, **Send/Resend Invite**, an **Adjust credit** action, and the **credit history** — backed by `customers/{customer}/invite-portal`, `/adjust-credit`, `/credit-history`.
- **Discount group modal**: the credit-earn fields (toggle + type + rate + min spend).
- **Order detail** ([show.blade.php](../resources/views/employee/order/show.blade.php)): the payment modal offers **Use Store Credit** (with a Max button) when the wallet is unlocked; below the minimum it shows an unlock message.
- **New order** ([new-order.js](../public/assets/js/employee/new-order.js)): payment screen can apply store credit and sends `payment.credits_applied` when unlocked.
- **Shop setting**: Order & Invoice → **Store Credit Unlock Balance** (`orders.credit_min_redeem_balance`).

## Schema additions

- `customers`: `password`, `remember_token`, `portal_enabled`, `email_verified_at`, `password_set_at`, `reset_token`, `reset_token_expires_at`; unique (`tenant_id`, `email`).
- `customer_credit_transactions` (new ledger).
- `discount_groups`: `earns_credit`, `credit_earn_type`, `credit_earn_rate`, `credit_min_spend`.
- `orders`: `credit_applied`, `credit_earned`.
- `personal_access_tokens` (Sanctum).

## Tests

- [tests/Unit/DiscountGroupCreditTest.php](../tests/Unit/DiscountGroupCreditTest.php) — earn calculation.
- [tests/Feature/Customer/CustomerPortalCreditTest.php](../tests/Feature/Customer/CustomerPortalCreditTest.php) — `CreditService` earn/redeem/adjust + ledger, unlock threshold, portal login, vehicles, change password, own-order PDF only, registration.

> Note: the configured sqlite test runner previously could not boot because two pre-existing migrations used MySQL-only SQL; both now guard for sqlite (no-ops on the empty test DB). The remaining `ShopSettingsTest` failures are pre-existing route-name drift, unrelated to this feature.
