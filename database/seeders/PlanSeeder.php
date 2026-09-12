<?php

namespace Database\Seeders;

use App\Enums\PlanDuration;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    private const PLANS = [
        [
            'name' => 'Starter Monthly',
            'slug' => 'starter-monthly',
            'description' => 'Ideal for single-bay quick lube or boutique repair shops.',
            'duration_type' => PlanDuration::Monthly,
            'duration_days' => 30,
            'price' => 49.00,
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
            'is_active' => true,
        ],
        [
            'name' => 'Professional Quarterly',
            'slug' => 'professional-quarterly',
            'description' => 'Best for growing automotive service centers with multiple bays and staff.',
            'duration_type' => PlanDuration::Quarterly,
            'duration_days' => 90,
            'price' => 129.00,
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
            'is_active' => true,
        ],
        [
            'name' => 'Enterprise Annual',
            'slug' => 'enterprise-annual',
            'description' => 'Full-featured automotive POS with complete inventory, customer portal, and multi-user support.',
            'duration_type' => PlanDuration::Yearly,
            'duration_days' => 365,
            'price' => 499.00,
            'billing_cycle' => 'yearly',
            'trial_days' => 30,
            'is_active' => true,
        ],
        [
            'name' => '7-Day Quick Pass',
            'slug' => '7-day-quick-pass',
            'description' => 'Short term evaluation pass for new test shops.',
            'duration_type' => PlanDuration::Weekly,
            'duration_days' => 7,
            'price' => 19.00,
            'billing_cycle' => 'weekly',
            'trial_days' => 0,
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::PLANS as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
