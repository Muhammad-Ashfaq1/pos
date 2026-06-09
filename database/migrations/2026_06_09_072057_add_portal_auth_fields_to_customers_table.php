<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->boolean('portal_enabled')->default(false)->after('remember_token');
            $table->timestamp('email_verified_at')->nullable()->after('portal_enabled');
            $table->timestamp('password_set_at')->nullable()->after('email_verified_at');

            // Login is resolved per-shop, so email must be unique within a tenant.
            $table->unique(['tenant_id', 'email'], 'customers_tenant_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique('customers_tenant_email_unique');
            $table->dropColumn([
                'password',
                'remember_token',
                'portal_enabled',
                'email_verified_at',
                'password_set_at',
            ]);
        });
    }
};
