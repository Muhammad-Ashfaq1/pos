<?php

namespace App\Support;

use App\Models\User;
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
                    ->map(fn (array $item): array => $item)
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
            ['key' => 'time_clock', 'label' => 'Time Clock', 'icon' => 'tabler-clock-hour-4', 'url' => null, 'permission' => null],
            ['key' => 'new_order', 'label' => 'Create New Order', 'icon' => 'tabler-shopping-bag', 'route' => 'employee.order.new-order', 'permission' => ['orders.create', 'pos.bill']],
            ['key' => 'reports', 'label' => 'Reports', 'icon' => 'tabler-report-search', 'route' => 'employee.reports.index', 'routeParams' => ['report' => 'sales'], 'permission' => 'reports.view'],
            ['key' => 'orders', 'label' => 'Orders', 'icon' => 'tabler-clipboard-data', 'route' => 'employee.order.index', 'permission' => 'orders.view'],
            ['key' => 'returns', 'label' => 'Returns', 'icon' => 'tabler-arrow-back-up', 'route' => 'employee.order.returns', 'permission' => 'orders.view'],
            ['key' => 'products', 'label' => 'Product Setup', 'icon' => 'tabler-package-import', 'route' => 'employee.products.index', 'permission' => ['product.create', 'product.view', 'products.view']],
            ['key' => 'invoices', 'label' => 'Invoices', 'icon' => 'tabler-file-invoice', 'route' => 'employee.invoices.index', 'permission' => 'orders.view'],
            ['key' => 'discounts', 'label' => 'Discounts', 'icon' => 'tabler-ticket', 'route' => 'employee.cards.type', 'routeParams' => ['type' => 'discount'], 'permission' => ['cards.view', 'cards.manage']],
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
        return $items->filter(function (array $item) use ($user): bool {
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
                'route' => 'account.profile',
                'permissions' => null,
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function sidebarGroupsConfig(): Collection
    {
        return collect([
            [
                'label' => 'Workspace',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'employee.dashboard', 'pattern' => 'employee.dashboard', 'icon' => 'tabler-layout-dashboard', 'permissions' => null],
                    ['label' => 'POS / Workspace', 'route' => 'employee.order.new-order', 'pattern' => 'employee.order.*|employee.pos|employee.workspace', 'icon' => 'tabler-cash-register', 'permissions' => null],
                    ['label' => 'Invoices', 'route' => 'employee.invoices.index', 'pattern' => 'employee.invoices.*', 'icon' => 'tabler-file-invoice', 'permissions' => ['orders.view']],
                    ['label' => 'Discounts', 'route' => 'employee.cards.type', 'routeParams' => ['type' => 'discount'], 'pattern' => 'employee.cards.*', 'icon' => 'tabler-ticket', 'permissions' => ['cards.view', 'cards.manage']],
                ],
            ],
            [
                'label' => 'Catalog',
                'items' => [
                    ['label' => 'Products', 'route' => 'tenant.ecommerce.products.index', 'pattern' => 'tenant.ecommerce.products.*', 'icon' => 'tabler-package', 'permissions' => ['product.view', 'products.view', 'products.manage']],
                    ['label' => 'Manage Products', 'route' => 'employee.products.index', 'pattern' => 'employee.products.*', 'icon' => 'tabler-package-import', 'permissions' => ['product.create', 'product.update']],
                    ['label' => 'Services', 'route' => 'tenant.ecommerce.services.index', 'pattern' => 'tenant.ecommerce.services.*', 'icon' => 'tabler-tool', 'permissions' => ['service.view', 'services.view']],
                ],
            ],
            [
                'label' => 'Lookup',
                'items' => [
                    ['label' => 'Customers', 'route' => 'employee.customers.index', 'pattern' => 'employee.customers.*', 'icon' => 'tabler-users', 'permissions' => ['customer.view', 'customers.view']],
                    ['label' => 'Vehicles', 'route' => 'employee.vehicles.index', 'pattern' => 'employee.vehicles.*', 'icon' => 'tabler-car', 'permissions' => ['vehicle.view', 'vehicles.view']],
                ],
            ],
        ]);
    }
}
