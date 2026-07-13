<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    use BelongsToTenant;

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_GIFT = 'gift';

    public const TYPE_REWARD = 'reward';

    protected $fillable = [
        'product_id',
        'card_type',
        'name',
        'discount_type',
        'value',
        'minimum_spend',
        'valid_until',
        'details',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_spend' => 'decimal:2',
            'valid_until' => 'date',
            'details' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DISCOUNT => 'Discount Card',
            self::TYPE_GIFT => 'Gift Card',
            self::TYPE_REWARD => 'Reward Card',
        ];
    }

    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', today());
            });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
