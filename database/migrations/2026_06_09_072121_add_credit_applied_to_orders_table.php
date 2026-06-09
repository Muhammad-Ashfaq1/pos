<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Store credit redeemed against this order. Counts toward payment_amount.
            $table->decimal('credit_applied', 12, 2)->default(0)->after('change_amount');
            // Store credit granted to the customer when this order was paid (idempotency aid).
            $table->decimal('credit_earned', 12, 2)->default(0)->after('credit_applied');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['credit_applied', 'credit_earned']);
        });
    }
};
