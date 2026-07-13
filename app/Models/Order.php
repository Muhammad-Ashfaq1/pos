<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\FiltersByDateRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant;
    use FiltersByDateRange;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_ESTIMATE = 'estimate';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'order_number',
        'customer_id',
        'vehicle_id',
        'service_id',
        'discount_id',
        'discount_group_id',
        'discount_details',
        'status',
        'total_quantity',
        'subtotal_amount',
        'discount_amount',
        'service_fee_amount',
        'service_fee_details',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_amount',
        'credit_applied',
        'gift_card_amount',
        'reward_points_earned',
        'card_details',
        'credit_earned',
        'change_amount',
        'paid_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'vehicle_id' => 'integer',
            'service_id' => 'integer',
            'discount_id' => 'integer',
            'discount_group_id' => 'integer',
            'discount_details' => 'array',
            'total_quantity' => 'integer',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'service_fee_details' => 'array',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'credit_applied' => 'decimal:2',
            'gift_card_amount' => 'decimal:2',
            'reward_points_earned' => 'integer',
            'card_details' => 'array',
            'credit_earned' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
