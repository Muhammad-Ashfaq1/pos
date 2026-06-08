<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('vehicle_id')->constrained('discounts')->nullOnDelete();
            $table->foreignId('discount_group_id')->nullable()->after('discount_id')->constrained('discount_groups')->nullOnDelete();
            $table->json('discount_details')->nullable()->after('discount_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['discount_group_id']);
            $table->dropColumn(['discount_id', 'discount_group_id', 'discount_details']);
        });
    }
};
