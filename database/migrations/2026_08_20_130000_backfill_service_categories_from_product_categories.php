<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_categories') || ! Schema::hasTable('services')) {
            return;
        }

        $now = now();
        $tenantIds = DB::table('services')->distinct()->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            $codes = DB::table('services')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('code')
                ->pluck('code')
                ->map(function (string $code): ?string {
                    if (preg_match('/^SVC-([A-Z0-9_]+)-\d+$/i', $code, $matches) !== 1) {
                        return null;
                    }

                    return strtoupper($matches[1]);
                })
                ->filter()
                ->unique()
                ->values();

            $codeToServiceCategoryId = [];

            foreach ($codes as $code) {
                $productCategory = DB::table('categories')
                    ->where('tenant_id', $tenantId)
                    ->where('code', $code)
                    ->first();

                $name = $productCategory->name ?? Str::title(str_replace('_', ' ', strtolower($code)));
                $slug = $productCategory->slug ?? Str::slug($name);
                $description = $productCategory->description ?? "{$name} services";

                $existing = DB::table('service_categories')
                    ->where('tenant_id', $tenantId)
                    ->where(function ($query) use ($code, $slug, $name): void {
                        $query->where('code', $code)
                            ->orWhere('slug', $slug)
                            ->orWhere('name', $name);
                    })
                    ->first();

                if ($existing) {
                    $codeToServiceCategoryId[$code] = (int) $existing->id;

                    continue;
                }

                $id = DB::table('service_categories')->insertGetId([
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'slug' => $slug,
                    'code' => $code,
                    'description' => $description,
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_by' => $productCategory->created_by ?? null,
                    'updated_by' => $productCategory->updated_by ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $codeToServiceCategoryId[$code] = $id;
            }

            foreach ($codeToServiceCategoryId as $code => $serviceCategoryId) {
                DB::table('services')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('category_id')
                    ->where('code', 'like', 'SVC-'.$code.'-%')
                    ->update([
                        'category_id' => $serviceCategoryId,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Data backfill only — no reverse.
    }
};
