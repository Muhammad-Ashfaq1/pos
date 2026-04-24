<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->nullable();
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'sub_categories_tenant_name_unique');
            $table->unique(['tenant_id', 'slug'], 'sub_categories_tenant_slug_unique');
            $table->unique(['tenant_id', 'code'], 'sub_categories_tenant_code_unique');
            $table->index(['tenant_id', 'category_id'], 'sub_categories_tenant_category_index');
            $table->index(['tenant_id', 'is_active'], 'sub_categories_tenant_active_index');
            $table->index(['tenant_id', 'sort_order'], 'sub_categories_tenant_sort_order_index');
            $table->index(['tenant_id', 'name'], 'sub_categories_tenant_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
