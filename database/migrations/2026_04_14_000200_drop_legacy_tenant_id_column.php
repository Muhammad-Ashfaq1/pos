<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'legacy_id')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
    }

    public function down(): void
    {
        //
    }
};
