<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ceil any fractional values first, while the column is still decimal(12,3).
        // Doing this *before* changing the column type guarantees no stock is silently
        // truncated (e.g. 0.4 -> 1 instead of 0). max(0, ...) defends against any
        // legacy negatives that may have leaked in via prior bugs.
        if (Schema::hasTable('products')) {
            DB::table('products')->update([
                'opening_stock' => DB::raw('GREATEST(0, CEIL(opening_stock))'),
                'current_stock' => DB::raw('GREATEST(0, CEIL(current_stock))'),
                'minimum_stock_level' => DB::raw('GREATEST(0, CEIL(minimum_stock_level))'),
                'reorder_level' => DB::raw('GREATEST(0, CEIL(reorder_level))'),
            ]);

            Schema::table('products', function (Blueprint $table): void {
                $table->unsignedInteger('opening_stock')->default(0)->change();
                $table->unsignedInteger('current_stock')->default(0)->change();
                $table->unsignedInteger('minimum_stock_level')->default(0)->change();
                $table->unsignedInteger('reorder_level')->default(0)->change();
            });
        }

        if (Schema::hasTable('service_products')) {
            DB::table('service_products')->update([
                'quantity' => DB::raw('GREATEST(0, CEIL(quantity))'),
            ]);

            // Service product mappings must have a positive quantity to make sense;
            // any zero rows that survived the ceil were already meaningless.
            DB::table('service_products')->where('quantity', '<=', 0)->delete();

            Schema::table('service_products', function (Blueprint $table): void {
                $table->unsignedInteger('quantity')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('opening_stock', 12, 3)->default(0)->change();
                $table->decimal('current_stock', 12, 3)->default(0)->change();
                $table->decimal('minimum_stock_level', 12, 3)->default(0)->change();
                $table->decimal('reorder_level', 12, 3)->default(0)->change();
            });
        }

        if (Schema::hasTable('service_products')) {
            Schema::table('service_products', function (Blueprint $table): void {
                $table->decimal('quantity', 12, 3)->change();
            });
        }
    }
};
