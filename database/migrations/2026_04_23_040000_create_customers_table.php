<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('customer_type', 30)->default('registered');
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedInteger('total_visits')->default(0);
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->unsignedInteger('loyalty_points_balance')->default(0);
            $table->decimal('credit_balance', 12, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_type'], 'customers_tenant_type_index');
            $table->index(['tenant_id', 'name'], 'customers_tenant_name_index');
            $table->index(['tenant_id', 'phone'], 'customers_tenant_phone_index');
            $table->index(['tenant_id', 'email'], 'customers_tenant_email_index');
            $table->index(['tenant_id', 'last_visit_at'], 'customers_tenant_last_visit_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
