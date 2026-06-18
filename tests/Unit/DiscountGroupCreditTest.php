<?php

namespace Tests\Unit;

use App\Models\DiscountGroup;
use PHPUnit\Framework\TestCase;

class DiscountGroupCreditTest extends TestCase
{
    private function group(array $attributes): DiscountGroup
    {
        $group = new DiscountGroup;
        $group->forceFill(array_merge([
            'is_active' => true,
            'earns_credit' => true,
            'credit_earn_type' => 'percentage',
            'credit_earn_rate' => 10,
            'credit_min_spend' => 0,
        ], $attributes));

        return $group;
    }

    public function test_percentage_earn_is_a_share_of_net_spend(): void
    {
        $this->assertSame(10.0, $this->group([])->creditEarnedFor(100));
        $this->assertSame(12.5, $this->group(['credit_earn_rate' => 5])->creditEarnedFor(250));
    }

    public function test_fixed_earn_is_a_flat_amount(): void
    {
        $group = $this->group(['credit_earn_type' => 'fixed', 'credit_earn_rate' => 7.5]);

        $this->assertSame(7.5, $group->creditEarnedFor(100));
        $this->assertSame(7.5, $group->creditEarnedFor(9999));
    }

    public function test_min_spend_threshold_blocks_earning(): void
    {
        $group = $this->group(['credit_min_spend' => 50]);

        $this->assertSame(0.0, $group->creditEarnedFor(40));
        $this->assertSame(5.0, $group->creditEarnedFor(50));
    }

    public function test_disabled_group_earns_nothing(): void
    {
        $this->assertSame(0.0, $this->group(['earns_credit' => false])->creditEarnedFor(100));
        $this->assertSame(0.0, $this->group(['is_active' => false])->creditEarnedFor(100));
    }
}
