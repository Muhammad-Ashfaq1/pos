<?php
namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountGroup extends Model
{
    use SoftDeletes, BelongsToTenant;

    public const TYPE_FIXED = 'fixed';

    public const TYPE_PERCENTAGE = 'percentage';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'type',
        'value',
        'min_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_FIXED => 'Fixed',
            self::TYPE_PERCENTAGE => 'Percentage',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
