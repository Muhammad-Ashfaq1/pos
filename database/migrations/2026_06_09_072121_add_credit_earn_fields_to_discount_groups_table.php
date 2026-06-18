<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_groups', function (Blueprint $table): void {
            // A group keeps its direct discount (type/value/min_limit) AND can grant store credit.
            $table->boolean('earns_credit')->default(false)->after('min_limit');
            // percentage (of qualifying net spend) | fixed (flat amount per qualifying order)
            $table->string('credit_earn_type', 20)->default('percentage')->after('earns_credit');
            $table->decimal('credit_earn_rate', 12, 2)->default(0)->after('credit_earn_type');
            $table->decimal('credit_min_spend', 12, 2)->default(0)->after('credit_earn_rate');
        });
    }

    public function down(): void
    {
        Schema::table('discount_groups', function (Blueprint $table): void {
            $table->dropColumn([
                'earns_credit',
                'credit_earn_type',
                'credit_earn_rate',
                'credit_min_spend',
            ]);
        });
    }
};
