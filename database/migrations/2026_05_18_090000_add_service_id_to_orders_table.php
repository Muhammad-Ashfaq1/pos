<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'service_id')) {
                $table->foreignId('service_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('services')
                    ->nullOnDelete();

                $table->index(['tenant_id', 'service_id'], 'orders_tenant_service_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'service_id')) {
                $table->dropIndex('orders_tenant_service_index');
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }
};
