<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            // Hashed one-time token backing both portal invites and forgot-password.
            $table->string('reset_token')->nullable()->after('password_set_at');
            $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['reset_token', 'reset_token_expires_at']);
        });
    }
};
