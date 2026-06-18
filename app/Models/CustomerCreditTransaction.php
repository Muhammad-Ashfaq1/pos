<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCreditTransaction extends Model
{
    use BelongsToTenant;

    public const TYPE_EARN = 'earn';

    public const TYPE_REDEEM = 'redeem';

    public const TYPE_ADJUST = 'adjust';

    public const TYPE_EXPIRE = 'expire';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'order_id' => 'integer',
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
