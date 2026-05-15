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
        Schema::table('discount_groups', function (Blueprint $table) {
            if(! Schema::hasColumn('discount_groups', 'min_limit')) {
                $table->decimal('min_limit', 10, 2)->nullable()->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_groups', function (Blueprint $table) {
            if (Schema::hasColumn('discount_groups', 'min_limit')) {
                $table->dropColumn('min_limit');
            }
        });
    }
};
