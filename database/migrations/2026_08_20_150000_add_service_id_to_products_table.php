<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'service_id')) {
                $table->foreignId('service_id')
                    ->nullable()
                    ->after('discount_id')
                    ->constrained('services')
                    ->nullOnDelete();

                $table->index(['tenant_id', 'service_id'], 'products_tenant_service_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'service_id')) {
                $table->dropIndex('products_tenant_service_index');
                $table->dropConstrainedForeignId('service_id');
            }
        });
    }
};
