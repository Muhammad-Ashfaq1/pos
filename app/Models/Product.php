<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;
    use HasImages;

    public const TYPE_INVENTORY = 'inventory';

    public const TYPE_OIL = 'oil';

    public const TYPE_FILTER = 'filter';

    public const TYPE_PART = 'part';

    public const TYPE_ADDITIVE = 'additive';

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'product_type',
        'name',
        'slug',
        'sku',
        'barcode',
        'brand',
        'unit',
        'description',
        'cost_price',
        'sale_price',
        'tax_percentage',
        'opening_stock',
        'current_stock',
        'minimum_stock_level',
        'reorder_level',
        'track_inventory',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'sub_category_id' => 'integer',
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'opening_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'minimum_stock_level' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_INVENTORY => 'Inventory Item',
            self::TYPE_OIL => 'Oil',
            self::TYPE_FILTER => 'Filter',
            self::TYPE_PART => 'Part',
            self::TYPE_ADDITIVE => 'Additive',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function serviceProducts(): HasMany
    {
        return $this->hasMany(ServiceProduct::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_products')
            ->withPivot(['tenant_id', 'quantity', 'unit', 'is_required'])
            ->withTimestamps();
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
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%")
                ->orWhereHas('category', function (Builder $categoryQuery) use ($term): void {
                    $categoryQuery->where('name', 'like', "%{$term}%");
                })
                ->orWhereHas('subCategory', function (Builder $subCategoryQuery) use ($term): void {
                    $subCategoryQuery->where('name', 'like', "%{$term}%");
                });
        });
    }
}
