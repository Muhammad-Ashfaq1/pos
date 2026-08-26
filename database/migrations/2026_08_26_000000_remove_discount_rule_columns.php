<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'is_combinable',
        'requires_reason',
        'requires_manager_approval',
    ];

    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->dropColumn(self::COLUMNS);
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->boolean('is_combinable')->default(true);
            $table->boolean('requires_reason')->default(false);
            $table->boolean('requires_manager_approval')->default(false);
        });
    }
};
