<?php

use App\Enums\PlanDuration;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'duration_type')) {
                $table->string('duration_type', 30)->default(PlanDuration::Monthly->value)->after('description');
            }
        });

        if (Schema::hasColumn('plans', 'duration_type')) {
            Plan::query()->each(function (Plan $plan): void {
                $duration = PlanDuration::tryFromDays((int) $plan->duration_days);

                $plan->forceFill([
                    'duration_type' => $duration->value,
                    'duration_days' => $duration->days(),
                    'billing_cycle' => $duration->billingCycle(),
                ])->saveQuietly();
            });
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'duration_type')) {
                $table->dropColumn('duration_type');
            }
        });
    }
};
