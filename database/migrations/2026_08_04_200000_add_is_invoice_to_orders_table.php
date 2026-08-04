<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->boolean('is_invoice')->default(false)->after('status');
            $table->index(['tenant_id', 'is_invoice'], 'orders_tenant_is_invoice_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_tenant_is_invoice_index');
            $table->dropColumn('is_invoice');
        });
    }
};
