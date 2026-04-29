<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type', 20);
            $table->string('applies_to', 30);
            $table->decimal('value', 12, 2);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_combinable')->default(true);
            $table->boolean('requires_reason')->default(false);
            $table->boolean('requires_manager_approval')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'discounts_tenant_code_unique');
            $table->index(['tenant_id', 'discount_type'], 'discounts_tenant_type_index');
            $table->index(['tenant_id', 'applies_to'], 'discounts_tenant_applies_to_index');
            $table->index(['tenant_id', 'is_active'], 'discounts_tenant_active_index');
            $table->index(['tenant_id', 'starts_at', 'ends_at'], 'discounts_tenant_schedule_index');
            $table->index(['tenant_id', 'name'], 'discounts_tenant_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
