<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToTenant;

class SubCategory extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'sub_categories';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'code',
        'sort_order',
        'is_active',
    ];

public function scopeActive($query)
{
    return $query->where('is_active', 1);
}
    /**
     * Casts (important for boolean handling)
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationship: SubCategory belongs to Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
