<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('total_amount');
            }

            if (! Schema::hasColumn('orders', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->default(0)->after('payment_method');
            }

            if (! Schema::hasColumn('orders', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->default(0)->after('payment_amount');
            }

            if (! Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('change_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['paid_at', 'change_amount', 'payment_amount', 'payment_method'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
