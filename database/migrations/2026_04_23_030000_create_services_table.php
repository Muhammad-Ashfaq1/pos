<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('standard_price', 12, 2)->default(0);
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->unsignedInteger('reminder_interval_days')->nullable();
            $table->unsignedInteger('mileage_interval')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_technician')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'services_tenant_name_unique');
            $table->unique(['tenant_id', 'code'], 'services_tenant_code_unique');
            $table->index(['tenant_id', 'category_id'], 'services_tenant_category_index');
            $table->index(['tenant_id', 'is_active'], 'services_tenant_active_index');
            $table->index(['tenant_id', 'requires_technician'], 'services_tenant_technician_index');
            $table->index(['tenant_id', 'name'], 'services_tenant_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
