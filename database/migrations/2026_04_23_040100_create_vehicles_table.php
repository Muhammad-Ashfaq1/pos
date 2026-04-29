<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('plate_number', 50);
            $table->string('registration_number', 80)->nullable();
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('engine_type', 80)->nullable();
            $table->decimal('odometer', 12, 1)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'plate_number'], 'vehicles_tenant_plate_unique');
            $table->unique(['tenant_id', 'registration_number'], 'vehicles_tenant_registration_unique');
            $table->index(['tenant_id', 'customer_id'], 'vehicles_tenant_customer_index');
            $table->index(['tenant_id', 'is_default'], 'vehicles_tenant_default_index');
            $table->index(['tenant_id', 'make', 'model'], 'vehicles_tenant_make_model_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
