<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'product_id',
        'discount_id',
        'product_name',
        'sku',
        'unit',
        'discount_name',
        'discount_type',
        'discount_value',
        'quantity',
        'unit_price',
        'line_subtotal',
        'unit_discount_amount',
        'line_discount_amount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'discount_id' => 'integer',
            'discount_value' => 'decimal:2',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_subtotal' => 'decimal:2',
            'unit_discount_amount' => 'decimal:2',
            'line_discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
