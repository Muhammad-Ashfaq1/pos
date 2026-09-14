<?php

namespace App\Enums;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

enum DefaultProductType: string
{
    case Inventory = 'inventory';
    case Oil = 'oil';
    case Filter = 'filter';
    case Part = 'part';
    case Additive = 'additive';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'Inventory Item',
            self::Oil => 'Oil',
            self::Filter => 'Filter',
            self::Part => 'Part',
            self::Additive => 'Additive',
            self::Other => 'Other',
        };
    }

    public function code(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Seeds the standard default product types for a tenant if they do not already exist.
     * Checks slug, name, and code to guarantee no duplicates are inserted.
     */
    public static function seedDefaultsForTenant(int|Tenant $tenant, ?int $userId = null): void
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        if (! $tenantId) {
            return;
        }

        $sortOrder = 1;

        foreach (self::cases() as $case) {
            $slug = $case->value;
            $name = $case->label();
            $code = $case->code();

            $exists = DB::table('product_types')
                ->where('tenant_id', $tenantId)
                ->where(function ($query) use ($slug, $name, $code) {
                    $query->where('slug', $slug)
                        ->orWhere('name', $name)
                        ->orWhere('code', $code);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('product_types')->insert([
                'tenant_id' => $tenantId,
                'name' => $name,
                'slug' => $slug,
                'code' => $code,
                'description' => "{$name} product type",
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Return all default slug values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
