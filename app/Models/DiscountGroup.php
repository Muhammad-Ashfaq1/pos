<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountGroup extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const CREDIT_TYPE_PERCENTAGE = 'percentage';

    public const CREDIT_TYPE_FIXED = 'fixed';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'type',
        'value',
        'min_limit',
        'is_active',
        'earns_credit',
        'credit_earn_type',
        'credit_earn_rate',
        'credit_min_spend',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_limit' => 'decimal:2',
            'is_active' => 'boolean',
            'earns_credit' => 'boolean',
            'credit_earn_rate' => 'decimal:2',
            'credit_min_spend' => 'decimal:2',
        ];
    }

    /**
     * Store credit earned for a given qualifying (pre-tax) net spend.
     * Honors the group's earn type and minimum-spend threshold.
     */
    public function creditEarnedFor(float $netSpend): float
    {
        if (! $this->is_active || ! $this->earns_credit) {
            return 0.0;
        }

        $netSpend = round($netSpend, 2);

        if ($netSpend <= 0 || $netSpend < (float) $this->credit_min_spend) {
            return 0.0;
        }

        $rate = (float) $this->credit_earn_rate;

        $earned = match ($this->credit_earn_type) {
            self::CREDIT_TYPE_PERCENTAGE => $netSpend * ($rate / 100),
            self::CREDIT_TYPE_FIXED => $rate,
            default => 0.0,
        };

        return round(max($earned, 0), 2);
    }
}
