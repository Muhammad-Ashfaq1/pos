<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->nullable();
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'service_categories_tenant_name_unique');
            $table->unique(['tenant_id', 'slug'], 'service_categories_tenant_slug_unique');
            $table->unique(['tenant_id', 'code'], 'service_categories_tenant_code_unique');
            $table->index(['tenant_id', 'is_active'], 'service_categories_tenant_active_index');
            $table->index(['tenant_id', 'sort_order'], 'service_categories_tenant_sort_order_index');
            $table->index(['tenant_id', 'name'], 'service_categories_tenant_name_index');
            $table->index(['tenant_id', 'slug'], 'service_categories_tenant_slug_index');
        });

        // Detach services from product categories before retargeting the FK.
        Schema::table('services', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        DB::table('services')->whereNotNull('category_id')->update(['category_id' => null]);

        Schema::table('services', function (Blueprint $table): void {
            $table->foreign('category_id')
                ->references('id')
                ->on('service_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        DB::table('services')->whereNotNull('category_id')->update(['category_id' => null]);

        Schema::table('services', function (Blueprint $table): void {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });

        Schema::dropIfExists('service_categories');
    }
};
