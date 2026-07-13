<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('card_type', 30);
            $table->string('name', 150);
            $table->string('discount_type', 20)->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('minimum_spend', 12, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->json('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'card_type']);
            $table->index(['tenant_id', 'card_type', 'is_active']);
            $table->index(['tenant_id', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
