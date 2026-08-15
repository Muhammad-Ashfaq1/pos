<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Presentation context for products, mounted on both the employee panel
 * and the tenant admin portal (same controller + shared listing view).
 */
final class ProductSurface
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

        $prefix = $isEmployee ? 'employee.products.' : 'tenant.ecommerce.products.';

        return [
            'is_employee' => $isEmployee,
            'layout' => $isEmployee ? 'layouts.employee-portal' : 'layouts.app',
            'dashboard_route' => $isEmployee ? 'employee.dashboard' : 'tenant.dashboard',
            'routes' => [
                'index' => $prefix.'index',
                'listing' => $prefix.'listing',
                'edit' => $prefix.'edit',
                'save' => $prefix.'save',
                'destroy' => $prefix.'destroy',
            ],
        ];
    }

    public static function route(string $key, mixed $parameters = [], bool $absolute = true): string
    {
        $surface = self::fromRequest();
        $name = $surface['routes'][$key] ?? null;

        if (! is_string($name) || $name === '') {
            throw new \InvalidArgumentException("Unknown product surface route [{$key}].");
        }

        return route($name, $parameters, $absolute);
    }
}
