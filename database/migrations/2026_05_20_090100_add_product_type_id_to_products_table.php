<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Default product types, keyed by the legacy `products.product_type` string value.
     */
    private const DEFAULTS = [
        'inventory' => 'Inventory Item',
        'oil' => 'Oil',
        'filter' => 'Filter',
        'part' => 'Part',
        'additive' => 'Additive',
        'other' => 'Other',
    ];

    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('product_type_id')
                ->nullable()
                ->after('sub_category_id')
                ->constrained('product_types')
                ->nullOnDelete();
            $table->index(['tenant_id', 'product_type_id'], 'products_tenant_type_id_index');
        });

        $this->backfill();
    }

    /**
     * For every tenant that already has products, materialise the default product
     * types (plus any custom legacy strings in use) as rows and point each product
     * at the matching row via product_type_id.
     */
    private function backfill(): void
    {
        $tenantIds = DB::table('products')->distinct()->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            $usedSlugs = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('product_type')
                ->distinct()
                ->pluck('product_type')
                ->all();

            $slugs = array_values(array_unique(array_merge(array_keys(self::DEFAULTS), $usedSlugs)));
            $sortOrder = 1;

            foreach ($slugs as $slug) {
                if ($slug === null || $slug === '') {
                    continue;
                }

                $typeId = DB::table('product_types')
                    ->where('tenant_id', $tenantId)
                    ->where('slug', $slug)
                    ->value('id');

                if (! $typeId) {
                    $typeId = DB::table('product_types')->insertGetId([
                        'tenant_id' => $tenantId,
                        'name' => self::DEFAULTS[$slug] ?? Str::headline($slug),
                        'slug' => $slug,
                        'code' => Str::upper(Str::slug($slug, '_')),
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('products')
                    ->where('tenant_id', $tenantId)
                    ->where('product_type', $slug)
                    ->update(['product_type_id' => $typeId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['product_type_id']);
            $table->dropIndex('products_tenant_type_id_index');
            $table->dropColumn('product_type_id');
        });
    }
};
