<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('gift_card_amount', 12, 2)->default(0)->after('credit_applied');
            $table->unsignedInteger('reward_points_earned')->default(0)->after('gift_card_amount');
            $table->json('card_details')->nullable()->after('reward_points_earned');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['gift_card_amount', 'reward_points_earned', 'card_details']);
        });
    }
};
