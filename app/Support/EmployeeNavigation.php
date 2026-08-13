<?php

namespace App\Support;

use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Collection;

class EmployeeNavigation
{
    public const MODE_BOTTOM = 'bottom';

    public const MODE_SIDEBAR = 'sidebar';

    public static function navMode(?User $user): string
    {
        if (! $user?->isEmployee()) {
            return self::MODE_BOTTOM;
        }

        $mode = (string) ($user->employee_nav_mode ?? self::MODE_BOTTOM);

        return in_array($mode, [self::MODE_BOTTOM, self::MODE_SIDEBAR], true)
            ? $mode
            : self::MODE_BOTTOM;
    }

    public static function usesSidebar(?User $user): bool
    {
        return self::navMode($user) === self::MODE_SIDEBAR;
    }

    /**
     * @return list<array{label: string, icon: string, url: string}>
     */
    public static function bottomNav(?User $user): array
    {
        return self::filterItems(collect(self::bottomNavItems()), $user)
            ->map(fn (array $item): array => [
                'label' => $item['label'],
                'icon' => $item['icon'],
                'url' => route($item['route'], $item['routeParams'] ?? []),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public static function sidebarGroups(?User $user): array
    {
        return self::sidebarGroupsConfig()
            ->map(function (array $group) use ($user): array {
                $group['items'] = self::filterItems(collect($group['items']), $user)
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group): bool => ! empty($group['items']))
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, icon: string, url: string|null, permission: string|array|null}>
     */
    public static function dashboardTiles(?User $user): array
    {
        $items = [
            ['key' => 'time_clock', 'label' => 'Time Clock', 'icon' => 'tabler-clock-hour-4', 'url' => null, 'permission' => null, 'tone' => 'secondary'],
            ['key' => 'new_order', 'label' => 'Create New Order', 'icon' => 'tabler-shopping-bag', 'route' => 'employee.order.new-order', 'permission' => ['orders.create', 'pos.bill'], 'tone' => 'primary'],
            ['key' => 'reports', 'label' => 'Reports', 'icon' => 'tabler-report-search', 'route' => 'employee.reports.index', 'routeParams' => ['report' => 'sales'], 'permission' => 'reports.view', 'tone' => 'success'],
            ['key' => 'orders', 'label' => 'Orders', 'icon' => 'tabler-clipboard-data', 'route' => 'employee.order.index', 'permission' => 'orders.view', 'tone' => 'warning'],
            ['key' => 'returns', 'label' => 'Returns', 'icon' => 'tabler-arrow-back-up', 'route' => 'employee.order.returns', 'permission' => 'orders.view', 'tone' => 'info'],
            ['key' => 'products', 'label' => 'Product Setup', 'icon' => 'tabler-package-import', 'route' => 'employee.products.index', 'permission' => ['product.create', 'product.view', 'products.view'], 'tone' => 'info'],
            ['key' => 'invoices', 'label' => 'Invoices', 'icon' => 'tabler-file-invoice', 'route' => 'employee.invoices.index', 'permission' => 'orders.view', 'tone' => 'success'],
            ['key' => 'discounts', 'label' => 'Discounts', 'icon' => 'tabler-ticket', 'route' => 'employee.cards.type', 'routeParams' => ['type' => 'discount'], 'permission' => ['cards.view', 'cards.manage'], 'tone' => 'warning'],
        ];

        return collect($items)
            ->filter(function (array $item) use ($user): bool {
                if ($item['permission'] === null) {
                    return true;
                }

                return self::canAny($user, (array) $item['permission']);
            })
            ->map(function (array $item): array {
                return [
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'tone' => $item['tone'] ?? 'primary',
                    'url' => isset($item['route']) ? route($item['route'], $item['routeParams'] ?? []) : null,
                    'permission' => $item['permission'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function filterItems(Collection $items, ?User $user): Collection
    {
        $vehicleRequired = app(TenantContext::class)->current()?->isVehicleRequired() ?? true;

        return $items->filter(function (array $item) use ($user, $vehicleRequired): bool {
            if (! empty($item['requires_vehicles']) && ! $vehicleRequired) {
                return false;
            }

            $permissions = $item['permissions'] ?? null;

            if ($permissions === null) {
                return true;
            }

            return self::canAny($user, $permissions);
        });
    }

    /**
     * @param  list<string>|null  $permissions
     */
    private static function canAny(?User $user, ?array $permissions): bool
    {
        if ($permissions === null || $permissions === []) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return collect($permissions)->contains(fn (string $permission): bool => $user->can($permission));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function bottomNavItems(): array
    {
        return [
            [
                'label' => 'POS',
                'icon' => 'tabler-device-desktop',
                'route' => 'employee.order.new-order',
                'permissions' => ['orders.create', 'pos.bill'],
            ],
            [
                'label' => 'Customers',
                'icon' => 'tabler-users',
                'route' => 'employee.customers.index',
                'permissions' => ['customer.view', 'customers.view'],
            ],
            [
                'label' => 'Inventory',
                'icon' => 'tabler-package',
                'route' => 'employee.products.index',
                'permissions' => ['product.view', 'products.view', 'product.create'],
            ],
            [
                'label' => 'Settings',
                'icon' => 'tabler-settings',
                'route' => 'employee.settings.product-mix',
                'permissions' => null,
            ],
        ];
    }

    /**
     * Sidebar shows only employee-portal routes, filtered by permissions.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private static function sidebarGroupsConfig(): Collection
    {
        return collect([
            [
                'label' => 'Workspace',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'employee.dashboard',
                        'pattern' => 'employee.dashboard',
                        'icon' => 'tabler-layout-dashboard',
                        'permissions' => null,
                    ],
                    [
                        'label' => 'Create Order',
                        'route' => 'employee.order.new-order',
                        'pattern' => 'employee.order.new-order|employee.pos',
                        'icon' => 'tabler-cash-register',
                        'permissions' => ['orders.create', 'pos.bill'],
                    ],
                    [
                        'label' => 'Orders',
                        'route' => 'employee.order.index',
                        'pattern' => 'employee.order.index|employee.order.show|employee.order.listing',
                        'icon' => 'tabler-clipboard-list',
                        'permissions' => ['orders.view'],
                    ],
                    [
                        'label' => 'Invoices',
                        'route' => 'employee.invoices.index',
                        'pattern' => 'employee.invoices.*',
                        'icon' => 'tabler-file-invoice',
                        'permissions' => ['orders.view'],
                    ],
                    [
                        'label' => 'Returns',
                        'route' => 'employee.order.returns',
                        'pattern' => 'employee.order.returns*',
                        'icon' => 'tabler-rotate-2',
                        'permissions' => ['orders.view'],
                    ],
                    [
                        'label' => 'Reports',
                        'route' => 'employee.reports.index',
                        'routeParams' => ['report' => 'sales'],
                        'pattern' => 'employee.reports.*',
                        'icon' => 'tabler-chart-bar',
                        'permissions' => ['reports.view'],
                    ],
                    [
                        'label' => 'Discounts',
                        'route' => 'employee.cards.type',
                        'routeParams' => ['type' => 'discount'],
                        'pattern' => 'employee.cards.*',
                        'icon' => 'tabler-ticket',
                        'permissions' => ['cards.view', 'cards.create', 'cards.manage'],
                    ],
                ],
            ],
            [
                'label' => 'Catalog',
                'items' => [
                    [
                        'label' => 'Products',
                        'route' => 'employee.products.index',
                        'pattern' => 'employee.products.*',
                        'icon' => 'tabler-package',
                        'permissions' => ['product.view', 'products.view', 'product.create'],
                    ],
                ],
            ],
            [
                'label' => 'Lookup',
                'items' => [
                    [
                        'label' => 'Customers',
                        'route' => 'employee.customers.index',
                        'pattern' => 'employee.customers.*',
                        'icon' => 'tabler-users',
                        'permissions' => ['customer.view', 'customers.view'],
                    ],
                    [
                        'label' => 'Vehicles',
                        'route' => 'employee.vehicles.index',
                        'pattern' => 'employee.vehicles.*',
                        'icon' => 'tabler-car',
                        'permissions' => ['vehicle.view', 'vehicles.view'],
                        'requires_vehicles' => true,
                    ],
                ],
            ],
        ]);
    }
}
