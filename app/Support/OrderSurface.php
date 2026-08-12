<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Shared presentation context for the order/invoice workspace, which is
 * mounted on both the employee panel and the tenant admin portal.
 */
final class OrderSurface
{
    /**
     * @return array{
     *     is_employee: bool,
     *     layout: string,
     *     dashboard_route: string,
     *     order_prefix: string,
     *     invoice_prefix: string,
     *     routes: array<string, string>
     * }
     */
    public static function fromRequest(?Request $request = null): array
    {
        $request ??= request();
        $name = (string) $request->route()?->getName();
        $isEmployee = str_starts_with($name, 'employee.');

        $orderPrefix = $isEmployee ? 'employee.order.' : 'tenant.order.';
        $invoicePrefix = $isEmployee ? 'employee.invoices.' : 'tenant.invoices.';

        return [
            'is_employee' => $isEmployee,
            'layout' => $isEmployee ? 'layouts.employee-portal' : 'layouts.app',
            'dashboard_route' => $isEmployee ? 'employee.dashboard' : 'tenant.dashboard',
            'order_prefix' => $orderPrefix,
            'invoice_prefix' => $invoicePrefix,
            'routes' => [
                'index' => $orderPrefix.'index',
                'listing' => $orderPrefix.'listing',
                'new' => $orderPrefix.'new-order',
                'save' => $orderPrefix.'save',
                'show' => $orderPrefix.'show',
                'pay' => $orderPrefix.'pay',
                'print' => $orderPrefix.'print',
                'pdf' => $orderPrefix.'pdf',
                'share' => $orderPrefix.'share',
                'returns' => $orderPrefix.'returns',
                'returns_listing' => $orderPrefix.'returns.listing',
                'returns_history' => $orderPrefix.'returns.history',
                'return' => $orderPrefix.'return',
                'categories' => $orderPrefix.'categories',
                'sub_categories' => $orderPrefix.'sub-categories',
                'products' => $orderPrefix.'products',
                'search' => $orderPrefix.'search',
                'cart_show' => $orderPrefix.'cart.show',
                'cart_save' => $orderPrefix.'cart.save',
                'cart_destroy' => $orderPrefix.'cart.destroy',
                'invoices_index' => $invoicePrefix.'index',
                'invoices_listing' => $invoicePrefix.'listing',
                'invoices_create' => $invoicePrefix.'create',
            ],
        ];
    }

    public static function route(string $key, mixed $parameters = [], bool $absolute = true): string
    {
        $surface = self::fromRequest();
        $name = $surface['routes'][$key] ?? null;

        if (! is_string($name) || $name === '') {
            throw new \InvalidArgumentException("Unknown order surface route [{$key}].");
        }

        return route($name, $parameters, $absolute);
    }
}
