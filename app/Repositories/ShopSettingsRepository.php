<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;
use App\Support\Currency;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ShopSettingsRepository implements ShopSettingsRepositoryInterface
{
    private const LOCALE_OPTIONS = [
        'en'    => 'English',
        'en_US' => 'English (United States)',
        'en_GB' => 'English (United Kingdom)',
        'en_AU' => 'English (Australia)',
        'en_CA' => 'English (Canada)',
        'ar'    => 'Arabic',
        'ur'    => 'Urdu',
        'de'    => 'German',
        'es'    => 'Spanish',
        'fr'    => 'French',
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
            'currencyOptions' => Currency::options(),
            'localeOptions' => self::LOCALE_OPTIONS,
            'timezoneOptions' => array_combine(
                \DateTimeZone::listIdentifiers(),
                \DateTimeZone::listIdentifiers(),
            ),
            'weekdayOptions' => self::WEEKDAYS,
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

        $settings = $tenant->mergedSettings();
        $rawBranding = (array) data_get($tenant->settings ?? [], 'branding', []);
        $existingColor = trim((string) ($rawBranding['primary_color'] ?? ''));
        $normalizedColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $existingColor) ? strtoupper($existingColor) : null;
        if (Tenant::isThemeDefaultBrandColor($normalizedColor)) {
            $normalizedColor = null;
        }
        $settings['branding'] = [
            'primary_color' => $normalizedColor,
            'tagline' => $this->normalizeNullableString($data['tagline'] ?? null),
        ];

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
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => 'Shop profile updated successfully.',
        ];
    }

    public function saveBrandingSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();
        $color = strtoupper(trim((string) ($data['primary_color'] ?? '')));

        if ($color === '' || ! preg_match('/^#[0-9A-F]{6}$/', $color) || Tenant::isThemeDefaultBrandColor($color)) {
            $color = null;
        }

        $settings['branding'] = array_merge(
            (array) ($settings['branding'] ?? []),
            [
                'primary_color' => $color,
            ]
        );

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        if (! empty($data['remove_logo'])) {
            $this->deleteLogoFile($tenant->logo);
            $tenant->logo = null;
        }

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            $this->storeLogo($tenant, $data['logo']);
        }

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => 'Branding saved successfully.',
            'logo_url' => $tenant->logoUrl(),
            'primary_color' => $tenant->brandPrimaryColorOrDefault(),
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

        Currency::flushCache();

        return [
            'success' => true,
            'message' => 'Regional and billing settings updated successfully.',
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
            'message' => 'Operations settings updated successfully.',
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
            'message' => 'Notification and loyalty settings updated successfully.',
        ];
    }

    public function saveOrderInvoiceSettings(Tenant $tenant, array $data): array
    {
        $settings = $tenant->mergedSettings();

        $settings['orders'] = [
            'vehicle_required' => (bool) ($data['vehicle_required'] ?? false),
            'return_days_after_purchase' => (int) $data['return_days_after_purchase'],
            'credit_min_redeem_balance' => round((float) $data['credit_min_redeem_balance'], 2),
        ];

        $tenant->forceFill([
            'settings' => $settings,
        ]);

        $this->persistTenant($tenant);

        return [
            'success' => true,
            'message' => 'Order and invoice settings updated successfully.',
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
                'label' => 'Shop Profile',
                'route' => 'tenant.settings.shop-profile.general',
                'pattern' => 'tenant.settings.shop-profile.general',
                'icon' => 'tabler-building-store',
                'description' => 'Business identity, contact details, address, and tagline.',
            ],
            [
                'label' => 'Branding',
                'route' => 'tenant.settings.shop-profile.branding',
                'pattern' => 'tenant.settings.shop-profile.branding',
                'icon' => 'tabler-palette',
                'description' => 'Company logo and primary color.',
            ],
            [
                'label' => 'Regional & Billing',
                'route' => 'tenant.settings.shop-profile.regional',
                'pattern' => 'tenant.settings.shop-profile.regional',
                'icon' => 'tabler-world',
                'description' => 'Currency, timezone, tax defaults, and invoice numbering.',
            ],
            [
                'label' => 'Operations',
                'route' => 'tenant.settings.shop-profile.operations',
                'pattern' => 'tenant.settings.shop-profile.operations',
                'icon' => 'tabler-settings-cog',
                'description' => 'Inventory thresholds and business hours for this tenant.',
            ],
            [
                'label' => 'Notifications & Loyalty',
                'route' => 'tenant.settings.shop-profile.notifications',
                'pattern' => 'tenant.settings.shop-profile.notifications',
                'icon' => 'tabler-bell',
                'description' => 'Communication defaults and loyalty behavior.',
            ],
            [
                'label' => 'Order & Invoice',
                'route' => 'tenant.settings.shop-profile.order-invoice',
                'pattern' => 'tenant.settings.shop-profile.order-invoice',
                'icon' => 'tabler-receipt',
                'description' => 'Vehicle requirements and return policy for orders.',
            ],
        ];

        $user = auth()->user();
        if ($user?->isTenantAdmin() || $user?->can('roles.manage')) {
            $sections[] = [
                'label' => 'Roles & Permissions',
                'route' => 'tenant.settings.roles-permissions.index',
                'pattern' => 'tenant.settings.roles-permissions.index',
                'icon' => 'tabler-shield-lock',
                'description' => 'Manage staff roles, permissions, and impersonation.',
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
            'credit_min_redeem_balance' => data_get($settings, 'orders.credit_min_redeem_balance', 50),
            'business_hours' => data_get($settings, 'business_hours', Tenant::defaultSettings()['business_hours']),
            'logo_url' => $tenant->logoUrl(),
            'primary_color' => $tenant->brandPrimaryColorOrDefault(),
            'tagline' => $tenant->brandTagline(),
        ];
    }

    private function storeLogo(Tenant $tenant, UploadedFile $file): void
    {
        $this->deleteLogoFile($tenant->logo);

        $path = $file->store('tenants/'.$tenant->getKey().'/logo', 'public');
        $tenant->logo = $path;
    }

    private function deleteLogoFile(mixed $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function buildReadinessChecklist(Tenant $tenant): array
    {
        $form = $this->buildFormData($tenant);

        $checks = [
            [
                'label' => 'Business identity',
                'completed' => ! blank($form['shop_name']) && ! blank($form['business_name']) && ! blank($form['owner_name']),
            ],
            [
                'label' => 'Contact details',
                'completed' => ! blank($form['business_email']) && ! blank($form['business_phone']),
            ],
            [
                'label' => 'Address & region',
                'completed' => ! blank($form['address']) && ! blank($form['city']) && ! blank($form['country']) && ! blank($form['timezone']),
            ],
            [
                'label' => 'Billing defaults',
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

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
