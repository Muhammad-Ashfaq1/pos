<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToTenant;

class Category extends Model
{
      use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'sort_order',
        'is_active',
    ];



public function scopeActive($query)
{
    return $query->where('is_active', 1);
}

  public function subCategories()
{
    return $this->hasMany(SubCategory::class);
}
}
