<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 50)->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'service_id', 'product_id'], 'service_products_unique_mapping');
            $table->index(['tenant_id', 'service_id'], 'service_products_tenant_service_index');
            $table->index(['tenant_id', 'product_id'], 'service_products_tenant_product_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_products');
    }
};
