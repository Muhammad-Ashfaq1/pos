<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;

class ShopSettingsRepository implements ShopSettingsRepositoryInterface
{
    private const LOCALE_OPTIONS = [
        'en' => 'English',
        'ar' => 'Arabic',
    ];

    private const CURRENCY_OPTIONS = [
        'USD' => 'US Dollar (USD)  $',
        'GBP' => 'British Pound (GBP)  £',
        'PKR' => 'Pakistani Rupee (PKR)  ₨',
        'AED' => 'UAE Dirham (AED)  د.إ',
        'SAR' => 'Saudi Riyal (SAR)  ﷼',
        'CAD' => 'Canadian Dollar (CAD)  C$',
        'AUD' => 'Australian Dollar (AUD)  A$',
        'EUR' => 'Euro (EUR)  €',
    ];

    private const WEEKDAYS = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    public function sharedViewData(Tenant $tenant): array
    {
        return [
            'tenant' => $tenant,
            'form' => $this->buildFormData($tenant),
            'currencyOptions' => self::CURRENCY_OPTIONS,
            'localeOptions' => $this->localeOptions(),
            'timezoneOptions' => array_combine(
                \DateTimeZone::listIdentifiers(),
                \DateTimeZone::listIdentifiers(),
            ),
            'weekdayOptions' => $this->weekdayOptions(),
            'readiness' => $this->buildReadinessChecklist($tenant),
            'settingsSections' => $this->settingsSections(),
        ];
    }

    public function saveGeneralSettings(Tenant $tenant, array $data): array
    {
        $shopName = trim((string) $data['shop_name']);
        $businessName = trim((string) $data['business_name']);
        $businessEmail = $this->normalizeNullableString($data['business_email'] ?? null);
        $businessPhone = $this->normalizeNullableString($data['business_phone'] ?? null);

        $tenant->forceFill([
            'name' => $shopName !== '' ? $shopName : $businessName,
            'shop_name' => $shopName,
            'business_name' => $businessName,
            'owner_name' => trim((string) $data['owner_name']),
            'business_email' => $businessEmail,
            'business_phone' => $businessPhone,
            'email' => $businessEmail,
            'phone' => $businessPhone,
            'website_url' => $this->normalizeNullableString($data['website_url'] ?? null),
            'address' => $this->normalizeNullableString($data['address'] ?? null),
            'city' => $this->normalizeNullableString($data['city'] ?? null),
            'state' => $this->normalizeNullableString($data['state'] ?? null),
            'country' => $this->normalizeNullableString($data['country'] ?? null),
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => __('admin.settings.general_updated'),
        ];
    }

    public function saveRegionalSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();

        $settings['regional'] = [
            'currency' => $data['currency'],
            'timezone' => $data['timezone'],
            'locale' => $data['locale'],
        ];

        $settings['tax'] = [
            'name' => $data['tax_name'],
            'percentage' => number_format((float) $data['tax_percentage'], 2, '.', ''),
        ];

        $settings['invoice'] = [
            'prefix' => strtoupper(trim((string) $data['invoice_prefix'])),
            'next_number' => (int) $data['invoice_next_number'],
        ];

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => __('admin.settings.regional_updated'),
        ];
    }

    public function saveOperationsSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();

        $settings['inventory'] = [
            'low_stock_threshold' => (int) $data['low_stock_threshold'],
        ];

        $settings['business_hours'] = collect(self::WEEKDAYS)
            ->mapWithKeys(function (string $label, string $day) use ($data): array {
                return [
                    $day => [
                        'is_closed' => (bool) data_get($data, "business_hours.{$day}.is_closed", false),
                        'open' => data_get($data, "business_hours.{$day}.open"),
                        'close' => data_get($data, "business_hours.{$day}.close"),
                    ],
                ];
            })
            ->all();

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => __('admin.settings.operations_updated'),
        ];
    }

    public function saveNotificationsSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();

        $settings['notifications'] = [
            'reminder_email_enabled' => (bool) ($data['reminder_email_enabled'] ?? false),
            'receipt_email_enabled' => (bool) ($data['receipt_email_enabled'] ?? false),
        ];

        $settings['loyalty'] = [
            'enabled' => (bool) ($data['loyalty_enabled'] ?? false),
            'points_per_currency' => number_format((float) $data['loyalty_points_per_currency'], 2, '.', ''),
        ];

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => __('admin.settings.notifications_updated'),
        ];
    }

    public function saveOrderInvoiceSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();

        $settings['orders'] = [
            'vehicle_required' => (bool) ($data['vehicle_required'] ?? false),
            'return_days_after_purchase' => (int) $data['return_days_after_purchase'],
        ];

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => __('admin.settings.order_invoice_updated'),
        ];
    }

    public function getSettingsSections(): array
    {
        return $this->settingsSections();
    }

    private function settingsSections(): array
    {
        $sections = [
            [
                'label' => __('admin.settings.shop_profile'),
                'route' => 'tenant.settings.shop-profile.general',
                'pattern' => 'tenant.settings.shop-profile.general',
                'icon' => 'tabler-building-store',
                'description' => __('admin.settings.shop_profile_desc'),
            ],
            [
                'label' => __('admin.settings.regional_billing'),
                'route' => 'tenant.settings.shop-profile.regional',
                'pattern' => 'tenant.settings.shop-profile.regional',
                'icon' => 'tabler-world',
                'description' => __('admin.settings.regional_billing_desc'),
            ],
            [
                'label' => __('admin.settings.operations'),
                'route' => 'tenant.settings.shop-profile.operations',
                'pattern' => 'tenant.settings.shop-profile.operations',
                'icon' => 'tabler-settings-cog',
                'description' => __('admin.settings.operations_desc'),
            ],
            [
                'label' => __('admin.settings.notifications_loyalty'),
                'route' => 'tenant.settings.shop-profile.notifications',
                'pattern' => 'tenant.settings.shop-profile.notifications',
                'icon' => 'tabler-bell',
                'description' => __('admin.settings.notifications_loyalty_desc'),
            ],
            [
                'label' => __('admin.settings.order_invoice'),
                'route' => 'tenant.settings.shop-profile.order-invoice',
                'pattern' => 'tenant.settings.shop-profile.order-invoice',
                'icon' => 'tabler-receipt',
                'description' => __('admin.settings.order_invoice_desc'),
            ],
        ];

        $user = auth()->user();
        if ($user?->isTenantAdmin() || $user?->can('roles.manage')) {
            $sections[] = [
                'label' => __('admin.settings.roles_permissions'),
                'route' => 'tenant.settings.roles-permissions.index',
                'pattern' => 'tenant.settings.roles-permissions.index',
                'icon' => 'tabler-shield-lock',
                'description' => __('admin.settings.roles_permissions_desc'),
            ];
        }

        return $sections;
    }

    private function persistTenant(Tenant $tenant): void
    {
        if (! $tenant->onboarding_completed_at && $this->isReadyForCompletion($tenant)) {
            $tenant->forceFill([
                'onboarding_status' => 'completed',
                'onboarding_completed_at' => now(),
            ]);
        }

        $tenant->save();
    }

    private function buildFormData(Tenant $tenant): array
    {
        $settings = $tenant->mergedSettings();

        return [
            'shop_name' => $tenant->shop_name ?: $tenant->name ?: $tenant->business_name,
            'business_name' => $tenant->business_name ?: $tenant->shop_name ?: $tenant->name,
            'owner_name' => $tenant->owner_name,
            'business_email' => $tenant->business_email ?: $tenant->email,
            'business_phone' => $tenant->business_phone ?: $tenant->phone,
            'website_url' => $tenant->website_url,
            'address' => $tenant->address,
            'city' => $tenant->city,
            'state' => $tenant->state,
            'country' => $tenant->country,
            'currency' => data_get($settings, 'regional.currency'),
            'timezone' => data_get($settings, 'regional.timezone'),
            'locale' => data_get($settings, 'regional.locale'),
            'tax_name' => data_get($settings, 'tax.name'),
            'tax_percentage' => data_get($settings, 'tax.percentage'),
            'invoice_prefix' => data_get($settings, 'invoice.prefix'),
            'invoice_next_number' => data_get($settings, 'invoice.next_number'),
            'low_stock_threshold' => data_get($settings, 'inventory.low_stock_threshold'),
            'reminder_email_enabled' => data_get($settings, 'notifications.reminder_email_enabled', true),
            'receipt_email_enabled' => data_get($settings, 'notifications.receipt_email_enabled', true),
            'loyalty_enabled' => data_get($settings, 'loyalty.enabled', false),
            'loyalty_points_per_currency' => data_get($settings, 'loyalty.points_per_currency', '1.00'),
            'vehicle_required' => data_get($settings, 'orders.vehicle_required', true),
            'return_days_after_purchase' => data_get($settings, 'orders.return_days_after_purchase', 30),
            'business_hours' => data_get($settings, 'business_hours', Tenant::defaultSettings()['business_hours']),
        ];
    }

    private function buildReadinessChecklist(Tenant $tenant): array
    {
        $form = $this->buildFormData($tenant);

        $checks = [
            [
                'label' => __('admin.settings.business_identity'),
                'completed' => ! blank($form['shop_name']) && ! blank($form['business_name']) && ! blank($form['owner_name']),
            ],
            [
                'label' => __('admin.settings.contact_details_check'),
                'completed' => ! blank($form['business_email']) && ! blank($form['business_phone']),
            ],
            [
                'label' => __('admin.settings.address_region'),
                'completed' => ! blank($form['address']) && ! blank($form['city']) && ! blank($form['country']) && ! blank($form['timezone']),
            ],
            [
                'label' => __('admin.settings.billing_defaults_check'),
                'completed' => ! blank($form['currency']) && ! blank($form['tax_name']) && ! blank($form['invoice_prefix']),
            ],
        ];

        $completed = collect($checks)->where('completed', true)->count();

        return [
            'items' => $checks,
            'completed' => $completed,
            'total' => count($checks),
            'percentage' => (int) round(($completed / count($checks)) * 100),
        ];
    }

    private function isReadyForCompletion(Tenant $tenant): bool
    {
        $readiness = $this->buildReadinessChecklist($tenant);

        return $readiness['completed'] === $readiness['total'];
    }

    private function localeOptions(): array
    {
        return [
            'en' => __('app.language_english'),
            'ar' => __('app.language_arabic'),
        ];
    }

    private function weekdayOptions(): array
    {
        return collect(self::WEEKDAYS)
            ->mapWithKeys(fn (string $label, string $day): array => [$day => __("admin.settings.weekdays.{$day}")])
            ->all();
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
