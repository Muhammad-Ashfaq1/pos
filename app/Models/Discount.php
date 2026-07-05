<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use BelongsToTenant;

    public const TYPE_FIXED = 'fixed';

    public const TYPE_PERCENTAGE = 'percentage';

    public const APPLIES_TO_BILL = 'bill';

    public const APPLIES_TO_ITEM = 'item';

    public const APPLIES_TO_CUSTOMER_PROFILE = 'customer_profile';

    public const APPLIES_TO_VOUCHER = 'voucher';

    public const APPLIES_TO_PROMOTION = 'promotion';

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type',
        'applies_to',
        'value',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'usage_limit',
        'is_active',
        'is_combinable',
        'requires_reason',
        'requires_manager_approval',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'is_active' => 'boolean',
            'is_combinable' => 'boolean',
            'requires_reason' => 'boolean',
            'requires_manager_approval' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_FIXED => __('admin.common.fixed'),
            self::TYPE_PERCENTAGE => __('admin.common.percentage'),
        ];
    }

    public static function appliesToOptions(): array
    {
        return [
            self::APPLIES_TO_BILL => __('admin.models.bill'),
            self::APPLIES_TO_ITEM => __('admin.models.item'),
            self::APPLIES_TO_CUSTOMER_PROFILE => __('admin.models.customer_profile'),
            self::APPLIES_TO_VOUCHER => __('admin.models.voucher'),
            self::APPLIES_TO_PROMOTION => __('admin.models.promotion'),
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
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
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('discount_type', 'like', "%{$term}%")
                ->orWhere('applies_to', 'like', "%{$term}%");
        });
    }
}
