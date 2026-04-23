<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('product_type', 50);
            $table->string('name', 150);
            $table->string('slug', 170)->nullable();
            $table->string('sku', 80)->nullable();
            $table->string('barcode', 80)->nullable();
            $table->string('brand', 120)->nullable();
            $table->string('unit', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('opening_stock', 12, 3)->default(0);
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('minimum_stock_level', 12, 3)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'products_tenant_name_unique');
            $table->unique(['tenant_id', 'slug'], 'products_tenant_slug_unique');
            $table->unique(['tenant_id', 'sku'], 'products_tenant_sku_unique');
            $table->unique(['tenant_id', 'barcode'], 'products_tenant_barcode_unique');
            $table->index(['tenant_id', 'category_id'], 'products_tenant_category_index');
            $table->index(['tenant_id', 'sub_category_id'], 'products_tenant_sub_category_index');
            $table->index(['tenant_id', 'product_type'], 'products_tenant_type_index');
            $table->index(['tenant_id', 'track_inventory'], 'products_tenant_tracking_index');
            $table->index(['tenant_id', 'is_active'], 'products_tenant_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
