<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('duration_days')->default(30);
                $table->string('duration_type', 30)->default('monthly');
                $table->decimal('price', 10, 2)->nullable();
                $table->string('billing_cycle')->default('monthly');
                $table->unsignedInteger('trial_days')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('plans', 'duration_days')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->unsignedInteger('duration_days')->default(30)->after('description');
            });
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('status')->constrained('plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('tenants', 'plan_expires_at')) {
                $table->date('plan_expires_at')->nullable()->after('plan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'plan_id')) {
                $table->dropConstrainedForeignId('plan_id');
            }
            if (Schema::hasColumn('tenants', 'plan_expires_at')) {
                $table->dropColumn('plan_expires_at');
            }
        });
    }
};
