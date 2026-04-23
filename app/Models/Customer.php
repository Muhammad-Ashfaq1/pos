<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use BelongsToTenant;

    public const DEFAULT_WALK_IN_NAME = 'Walk-in Customer';

    public const TYPE_REGISTERED = 'registered';

    public const TYPE_WALK_IN = 'walk_in';

    public const TYPE_CORPORATE = 'corporate';

    protected $fillable = [
        'customer_type',
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'date_of_birth',
        'total_visits',
        'lifetime_value',
        'loyalty_points_balance',
        'credit_balance',
        'last_visit_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'total_visits' => 'integer',
            'lifetime_value' => 'decimal:2',
            'loyalty_points_balance' => 'integer',
            'credit_balance' => 'decimal:2',
            'last_visit_at' => 'datetime',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_REGISTERED => 'Registered',
            self::TYPE_WALK_IN => 'Walk In',
            self::TYPE_CORPORATE => 'Corporate',
        ];
    }

    public static function defaultWalkInName(): string
    {
        return self::DEFAULT_WALK_IN_NAME;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function defaultVehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class)->where('is_default', true);
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
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%")
                ->orWhereHas('vehicles', function (Builder $vehicleQuery) use ($term): void {
                    $vehicleQuery
                        ->where('plate_number', 'like', "%{$term}%")
                        ->orWhere('registration_number', 'like', "%{$term}%")
                        ->orWhere('make', 'like', "%{$term}%")
                        ->orWhere('model', 'like', "%{$term}%");
                });
        });
    }
}
