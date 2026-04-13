<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasTable('users') || ! Schema::hasTable('domains')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id_int')) {
                return;
            }

            $table->unsignedBigInteger('tenant_id_int')->nullable()->after('tenant_id');
        });

        Schema::table('domains', function (Blueprint $table) {
            if (Schema::hasColumn('domains', 'tenant_id_int')) {
                return;
            }

            $table->unsignedBigInteger('tenant_id_int')->nullable()->after('tenant_id');
        });

        DB::statement('ALTER TABLE tenants ADD COLUMN numeric_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE');

        DB::statement('
            UPDATE users
            INNER JOIN tenants ON users.tenant_id = tenants.id
            SET users.tenant_id_int = tenants.numeric_id
            WHERE users.tenant_id IS NOT NULL
        ');

        DB::statement('
            UPDATE domains
            INNER JOIN tenants ON domains.tenant_id = tenants.id
            SET domains.tenant_id_int = tenants.numeric_id
        ');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->renameColumn('id', 'legacy_id');
        });

        DB::statement('ALTER TABLE tenants DROP PRIMARY KEY');
        DB::statement('ALTER TABLE tenants CHANGE numeric_id id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('tenant_id_int', 'tenant_id');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->renameColumn('tenant_id_int', 'tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Intentionally left irreversible because converting auto-increment numeric tenant IDs
        // back to string primary keys would be destructive once live relational data exists.
    }
};
