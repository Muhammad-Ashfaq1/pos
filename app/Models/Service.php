<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'category_id',
        'name',
        'title_en',
        'title_ar',
        'code',
        'description',
        'description_en',
        'description_ar',
        'standard_price',
        'estimated_duration_minutes',
        'tax_percentage',
        'reminder_interval_days',
        'mileage_interval',
        'is_active',
        'requires_technician',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'standard_price' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'tax_percentage' => 'decimal:2',
            'reminder_interval_days' => 'integer',
            'mileage_interval' => 'integer',
            'is_active' => 'boolean',
            'requires_technician' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceProducts(): HasMany
    {
        return $this->hasMany(ServiceProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'service_products')
            ->withPivot(['tenant_id', 'quantity', 'unit', 'is_required'])
            ->withTimestamps();
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
                ->orWhere('title_en', 'like', "%{$term}%")
                ->orWhere('title_ar', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('description_en', 'like', "%{$term}%")
                ->orWhere('description_ar', 'like', "%{$term}%")
                ->orWhereHas('category', function (Builder $categoryQuery) use ($term): void {
                    $categoryQuery->where('name', 'like', "%{$term}%");
                });
        });
    }

    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();

        return $this->{"title_{$locale}"}
            ?: $this->title_en
            ?: $this->name
            ?: '';
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        $locale = app()->getLocale();

        return $this->{"description_{$locale}"}
            ?: $this->description_en
            ?: $this->description
            ?: '';
    }
}
