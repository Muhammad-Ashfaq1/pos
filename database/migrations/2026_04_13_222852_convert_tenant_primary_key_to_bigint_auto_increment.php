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

        // SQLite (test runner) cannot execute the MySQL-specific PK surgery below.
        // On a fresh test database the tenants table is empty at this point, so we
        // simply rebuild it with a bigint auto-increment id and retype the FKs.
        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();

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

    /**
     * SQLite-safe equivalent of the MySQL primary-key conversion. Only valid on a
     * fresh/empty tenants table (i.e. the test database) — there is no data to migrate.
     */
    private function upSqlite(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('tenants');

            Schema::create('tenants', function (Blueprint $table): void {
                $table->id();
                $table->string('shop_name');
                $table->string('business_type')->nullable();
                $table->string('owner_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('website_url')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('status')->default('pending');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejected_reason')->nullable();
                $table->string('onboarding_status')->default('not_started');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::table('users', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
            });

            Schema::table('domains', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
            });
        });
    }

    public function down(): void
    {
        // Intentionally left irreversible because converting auto-increment numeric tenant IDs
        // back to string primary keys would be destructive once live relational data exists.
    }
};
