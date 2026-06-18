# Shop Settings

Once a shop is [approved and its admin logs in](auth-and-onboarding.md), the first thing they configure is the shop itself: currency, tax, business hours, and operational toggles. These settings shape money formatting, the [POS checkout](orders.md), and notifications across the whole tenant.

All settings routes are gated by `permission:settings.manage` and live in [routes/tenant.php:204-227](../routes/tenant.php#L204-L227).

---

## Sections

Tenant admins configure their shop in four sections:

| Section | URL | Edits |
|---------|-----|-------|
| **General** | `/tenant/settings/shop-profile/general` | shop name, business email/phone, address |
| **Regional** | `/tenant/settings/shop-profile/regional` | currency, timezone, locale, tax name & percentage, invoice prefix & next number |
| **Operations** | `/tenant/settings/shop-profile/operations` | inventory low-stock threshold, business hours per day, loyalty enabled + ratio |
| **Notifications** | `/tenant/settings/shop-profile/notifications` | reminder & receipt email toggles |

- **Controller**: [`Tenant\ShopSettingsController`](../app/Http/Controllers/Tenant/ShopSettingsController.php)
- **Repository**: [`ShopSettingsRepository`](../app/Repositories/ShopSettingsRepository.php) implementing [`ShopSettingsRepositoryInterface`](../app/Repositories/Interface/ShopSettingsRepositoryInterface.php)
- **Validation**: [`SaveShopGeneralSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopGeneralSettingsRequest.php), [`SaveShopRegionalSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopRegionalSettingsRequest.php), [`SaveShopOperationsSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopOperationsSettingsRequest.php), [`SaveShopNotificationsSettingsRequest`](../app/Http/Requests/Tenant/Settings/SaveShopNotificationsSettingsRequest.php)
- **Storage**: settings are a JSON column on `tenants.settings`. Defaults are merged on read via [`Tenant::mergedSettings()`](../app/Models/Tenant.php#L204-L207); see the `DEFAULT_SETTINGS` constant for the full tree.
- **Views**: [resources/views/tenant/settings/shop-profile/](../resources/views/tenant/settings/shop-profile/)

---

## Currency

The `regional.currency` setting drives money formatting across the whole app via [`App\Support\Currency`](../app/Support/Currency.php) (default `USD` → `$`):

- **Blade**: `@money($amount)` (e.g. `@money(1234.5)` → `$1,234.50`) or `@currency` for the bare symbol.
- **JS**: read `window.appCurrency.symbol` (injected by both layouts).
- **PHP**: services/repositories call `Currency::format()` / `Currency::symbol()`.
- Unknown/unmapped codes fall back to `$`.

---

## Order-related settings

Two keys directly control the [POS flow](orders.md):

| Setting key | Default | Effect |
|-------------|---------|--------|
| `orders.vehicle_required` | `true` | Whether a `vehicle_id` is mandatory when saving an order. Read via `Tenant::isVehicleRequired()`. |
| `orders.return_days_after_purchase` | `30`* | Return window (days from `paid_at`). Read via `Tenant::returnDaysAfterPurchase()`. |

\* The helper default is `30`; the new-shop seed (`Tenant.php#L46`) sets `7`.

---

## Default settings tree

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

## Roles & staff

Staff accounts and custom roles are managed under `/tenant/settings/roles-permissions/` (gated `roles.manage|settings.manage`), but that surface is documented with the rest of the access model in **[rbac.md](rbac.md#roles--permissions-ui)**.

**Next in the journey:** [catalog.md](catalog.md) — stocking the shop with what it sells.
</content>
