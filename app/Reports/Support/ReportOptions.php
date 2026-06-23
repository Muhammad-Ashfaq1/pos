<?php

namespace App\Reports\Support;

use App\Models\Category;
use App\Models\DiscountGroup;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * Shared, tenant-scoped option lists for report filter dropdowns. Kept in one
 * place so several report definitions can reuse the same bounded selects.
 */
class ReportOptions
{
    /** Staff users of the current tenant: id => name. */
    public static function staff(): array
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return [];
        }

        return User::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** Active categories of the current tenant: id => name. */
    public static function categories(): array
    {
        return Category::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** Discount groups of the current tenant: id => name. */
    public static function discountGroups(): array
    {
        return DiscountGroup::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
