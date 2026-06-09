# Customer Portal & Store-Credit Loyalty

The customer portal lets a shop's customers sign in (web **and** future Flutter app) to view their service history and a **store-credit wallet**. Credit is **earned** automatically when an order is paid and can be **redeemed** against a future order's total. Everything is tenant-scoped: a customer belongs to the shop where they are serviced, and credit is earned/spent at that shop only.

## Reward model: store credit (not direct discount)

A customer's **discount group** (`DiscountGroup`, the existing "customer group") can now do two independent things:

1. **Direct discount** on the order total at checkout — unchanged (`type`, `value`, `min_limit`).
2. **Earn store credit** on each paid visit — new (`earns_credit`, `credit_earn_type`, `credit_earn_rate`, `credit_min_spend`).

Store credit is **monetary** and lives on `customers.credit_balance`. Every change is written to an append-only ledger, `customer_credit_transactions` (`earn | redeem | adjust | expire`, signed `amount`, `balance_after`).

- **Earning**: when an order first reaches `paid`, [`OrdersRepository::applyVisitAndEarn()`](../app/Repositories/OrdersRepository.php) computes pre-tax net spend (`subtotal + service_fee − discount`) and grants `DiscountGroup::creditEarnedFor()` via [`CreditService::earn()`](../app/Services/CreditService.php). It also bumps `total_visits` / `lifetime_value` / `last_visit_at`. Earning is once-per-order.
- **Redeeming**: at checkout or on a later payment, `payment.credits_applied` (or `credits_applied` on the pay route) is capped at the wallet balance and the balance due, recorded as an `OrderPayment` with `payment_method = store_credit` plus `orders.credit_applied`, and deducted via [`CreditService::redeem()`](../app/Services/CreditService.php). It counts toward `payment_amount`, so the existing balance-due/status logic is unchanged.

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

[routes/customer.php](../routes/customer.php) serves server-rendered Blade pages under `/portal` (views in `resources/views/customer/`, layout [customer-portal.blade.php](../resources/views/layouts/customer-portal.blade.php)), protected by `auth:customer` + `customer.tenant.init`. Pages reuse the same repositories/services as everything else (`OrdersRepository::details()`, `CustomerCreditTransaction`), so there is no parallel data layer. The token API is **only** for Flutter; the web portal does not use it.

> The `shop` slug is still required by the **token API** (`/login`, `/register`, `/reset-password`) because those are stateless and cross-origin. The web portal never needs it.

## Staff side (tenant portal)

- **Customer form** ([save-modal.blade.php](../resources/views/tenant/ecommerce/customers/partials/save-modal.blade.php)): a "Portal & Store Credit" panel (edit mode) shows the balance, **Send/Resend Invite**, an **Adjust credit** action, and the **credit history** — backed by `customers/{customer}/invite-portal`, `/adjust-credit`, `/credit-history`.
- **Discount group modal**: the credit-earn fields (toggle + type + rate + min spend).
- **Order detail** ([show.blade.php](../resources/views/employee/order/show.blade.php)): the payment modal offers **Use Store Credit** (with a Max button) when the customer has a balance; the summary shows credit used and credit earned.

## Schema additions

- `customers`: `password`, `remember_token`, `portal_enabled`, `email_verified_at`, `password_set_at`, `reset_token`, `reset_token_expires_at`; unique (`tenant_id`, `email`).
- `customer_credit_transactions` (new ledger).
- `discount_groups`: `earns_credit`, `credit_earn_type`, `credit_earn_rate`, `credit_min_spend`.
- `orders`: `credit_applied`, `credit_earned`.
- `personal_access_tokens` (Sanctum).

## Tests

- [tests/Unit/DiscountGroupCreditTest.php](../tests/Unit/DiscountGroupCreditTest.php) — earn calculation.
- [tests/Feature/Customer/CustomerPortalCreditTest.php](../tests/Feature/Customer/CustomerPortalCreditTest.php) — `CreditService` earn/redeem/adjust + ledger, over-redeem guard, tenant-scoped login isolation, authenticated endpoints, registration.

> Note: the configured sqlite test runner previously could not boot because two pre-existing migrations used MySQL-only SQL; both now guard for sqlite (no-ops on the empty test DB). The remaining `ShopSettingsTest` failures are pre-existing route-name drift, unrelated to this feature.
