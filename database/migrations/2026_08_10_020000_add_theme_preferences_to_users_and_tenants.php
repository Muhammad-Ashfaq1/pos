<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_variant', 32)->nullable()->after('avatar');
            $table->string('theme_mode', 16)->nullable()->after('theme_variant');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('theme_variant', 32)->nullable()->after('logo');
            $table->string('theme_mode', 16)->nullable()->after('theme_variant');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_variant', 'theme_mode']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['theme_variant', 'theme_mode']);
        });
    }
};
