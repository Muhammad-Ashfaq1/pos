<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('discount_groups', 'min_limit')) {
                $table->decimal('min_limit', 12, 2)->nullable();
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'discount_id')) {
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'discount_group_id')) {
                $table->foreignId('discount_group_id')->nullable()->constrained('discount_groups')->nullOnDelete();
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'customer_discount_group_id')) {
                $table->foreignId('customer_discount_group_id')->nullable()->constrained('discount_groups')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'item_discount_amount')) {
                $table->decimal('item_discount_amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('orders', 'customer_discount_amount')) {
                $table->decimal('customer_discount_amount', 12, 2)->default(0);
            }
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'discount_id')) {
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'discount_name')) {
                $table->string('discount_name', 150)->nullable();
            }

            if (! Schema::hasColumn('order_items', 'discount_type')) {
                $table->string('discount_type', 20)->nullable();
            }

            if (! Schema::hasColumn('order_items', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('order_items', 'line_subtotal')) {
                $table->decimal('line_subtotal', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('order_items', 'unit_discount_amount')) {
                $table->decimal('unit_discount_amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('order_items', 'line_discount_amount')) {
                $table->decimal('line_discount_amount', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'discount_id')) {
                $table->dropConstrainedForeignId('discount_id');
            }

            foreach (['discount_name', 'discount_type', 'discount_value', 'line_subtotal', 'unit_discount_amount', 'line_discount_amount'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'customer_discount_group_id')) {
                $table->dropConstrainedForeignId('customer_discount_group_id');
            }

            foreach (['item_discount_amount', 'customer_discount_amount'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'discount_group_id')) {
                $table->dropConstrainedForeignId('discount_group_id');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'discount_id')) {
                $table->dropConstrainedForeignId('discount_id');
            }
        });

        Schema::table('discount_groups', function (Blueprint $table): void {
            if (Schema::hasColumn('discount_groups', 'min_limit')) {
                $table->dropColumn('min_limit');
            }
        });
    }
};
