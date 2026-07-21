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
        return collect(self::typeMeta())
            ->mapWithKeys(fn (array $meta, string $type) => [$type => $meta['singular']])
            ->all();
    }

    /**
     * Single source of truth for card-type UI/metadata across portals.
     *
     * @return array<string, array{
     *     title: string,
     *     singular: string,
     *     tab: string,
     *     icon: string,
     *     modal: string,
     *     value_label: string,
     *     value_prefix: string|null,
     *     uses_currency_prefix: bool,
     *     value_step: string
     * }>
     */
    public static function typeMeta(): array
    {
        return [
            self::TYPE_DISCOUNT => [
                'title' => 'Discount Cards',
                'singular' => 'Discount Card',
                'tab' => 'Discount',
                'icon' => 'tabler-ticket',
                'modal' => 'addDiscountCardModal',
                'value_label' => 'Discount Percentage',
                'value_prefix' => '%',
                'uses_currency_prefix' => false,
                'value_step' => '0.01',
            ],
            self::TYPE_GIFT => [
                'title' => 'Gift Cards',
                'singular' => 'Gift Card',
                'tab' => 'Gift',
                'icon' => 'tabler-gift',
                'modal' => 'addGiftCardModal',
                'value_label' => 'Gift Amount',
                'value_prefix' => null,
                'uses_currency_prefix' => true,
                'value_step' => '0.01',
            ],
            self::TYPE_REWARD => [
                'title' => 'Reward Cards',
                'singular' => 'Reward Card',
                'tab' => 'Reward',
                'icon' => 'tabler-trophy',
                'modal' => 'addRewardCardModal',
                'value_label' => 'Reward Points',
                'value_prefix' => 'PTS',
                'uses_currency_prefix' => false,
                'value_step' => '1',
            ],
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     singular: string,
     *     tab: string,
     *     icon: string,
     *     modal: string,
     *     value_label: string,
     *     value_prefix: string|null,
     *     uses_currency_prefix: bool,
     *     value_step: string
     * }
     */
    public static function metaFor(string $type): array
    {
        $meta = self::typeMeta()[$type] ?? null;

        if ($meta === null) {
            abort(404);
        }

        return $meta;
    }

    public static function resolveTypeOrFail(string $type): string
    {
        if (! array_key_exists($type, self::typeMeta())) {
            abort(404);
        }

        return $type;
    }

    public static function discountTypeOptions(): array
    {
        return [
            'percentage' => 'Percentage',
            'fixed' => 'Fixed Amount',
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

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('name', 'like', "%{$term}%")
                ->orWhere('card_type', 'like', "%{$term}%")
                ->orWhere('discount_type', 'like', "%{$term}%");
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        $ids = data_get($this->details, 'product_ids');

        if (is_array($ids) && $ids !== []) {
            return array_values(array_unique(array_map('intval', $ids)));
        }

        return $this->product_id ? [(int) $this->product_id] : [];
    }
}
