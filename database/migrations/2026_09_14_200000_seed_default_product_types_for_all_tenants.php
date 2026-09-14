<?php

use App\Enums\DefaultProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            DefaultProductType::seedDefaultsForTenant((int) $tenantId);

            // Backfill any products where product_type_id is null
            $unlinkedProducts = DB::table('products')
                ->where('tenant_id', $tenantId)
                ->whereNull('product_type_id')
                ->get(['id', 'product_type']);

            if ($unlinkedProducts->isNotEmpty()) {
                $tenantTypes = DB::table('product_types')
                    ->where('tenant_id', $tenantId)
                    ->pluck('id', 'slug')
                    ->all();

                $fallbackTypeId = $tenantTypes[DefaultProductType::Part->value]
                    ?? $tenantTypes[DefaultProductType::Inventory->value]
                    ?? $tenantTypes[DefaultProductType::Other->value]
                    ?? (reset($tenantTypes) ?: null);

                foreach ($unlinkedProducts as $prod) {
                    $matchedTypeId = ($prod->product_type && isset($tenantTypes[$prod->product_type]))
                        ? $tenantTypes[$prod->product_type]
                        : $fallbackTypeId;

                    if ($matchedTypeId) {
                        DB::table('products')
                            ->where('id', $prod->id)
                            ->update(['product_type_id' => $matchedTypeId]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: do not delete product types on rollback as they may be referenced by products
    }
};
