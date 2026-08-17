<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Presentation context for customers/vehicles, mounted on both the
 * employee panel and the tenant admin portal (same controllers + views).
 */
final class CustomerVehicleSurface
{
    /**
     * @return array{
     *     is_employee: bool,
     *     layout: string,
     *     dashboard_route: string,
     *     routes: array<string, string>
     * }
     */
    public static function fromRequest(?Request $request = null): array
    {
        $request ??= request();
        $name = (string) $request->route()?->getName();
        $isEmployee = str_starts_with($name, 'employee.');

        $customerPrefix = $isEmployee ? 'employee.customers.' : 'tenant.ecommerce.customers.';
        $vehiclePrefix = $isEmployee ? 'employee.vehicles.' : 'tenant.ecommerce.vehicles.';

        return [
            'is_employee' => $isEmployee,
            'layout' => $isEmployee ? 'layouts.employee-portal' : 'layouts.app',
            'dashboard_route' => $isEmployee ? 'employee.dashboard' : 'tenant.dashboard',
            'routes' => [
                'customers_index' => $customerPrefix.'index',
                'customers_listing' => $customerPrefix.'listing',
                'customers_edit' => $customerPrefix.'edit',
                'customers_save' => $customerPrefix.'save',
                'customers_destroy' => $customerPrefix.'destroy',
                'customers_invite_portal' => $customerPrefix.'invite-portal',
                'customers_adjust_credit' => $customerPrefix.'adjust-credit',
                'customers_credit_history' => $customerPrefix.'credit-history',
                'vehicles_index' => $vehiclePrefix.'index',
                'vehicles_listing' => $vehiclePrefix.'listing',
                'vehicles_edit' => $vehiclePrefix.'edit',
                'vehicles_save' => $vehiclePrefix.'save',
                'vehicles_destroy' => $vehiclePrefix.'destroy',
            ],
        ];
    }

    public static function route(string $key, mixed $parameters = [], bool $absolute = true): string
    {
        $surface = self::fromRequest();
        $name = $surface['routes'][$key] ?? null;

        if (! is_string($name) || $name === '') {
            throw new \InvalidArgumentException("Unknown customer/vehicle surface route [{$key}].");
        }

        return route($name, $parameters, $absolute);
    }
}
