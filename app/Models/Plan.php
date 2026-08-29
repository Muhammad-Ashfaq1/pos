<?php

namespace App\Models;

use App\Enums\PlanDuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_type',
        'duration_days',
        'price',
        'billing_cycle',
        'trial_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_type' => PlanDuration::class,
            'duration_days' => 'integer',
            'trial_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Plan $plan): void {
            if (! filled($plan->slug) && filled($plan->name)) {
                $plan->slug = static::uniqueSlug($plan->name, $plan->id);
            }

            if ($plan->duration_type instanceof PlanDuration) {
                $plan->duration_days = $plan->duration_type->days();
                $plan->billing_cycle = $plan->duration_type->billingCycle();
            } elseif (! filled($plan->billing_cycle)) {
                $plan->billing_cycle = static::billingCycleForDuration((int) ($plan->duration_days ?: 30));
            }
        });
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function billingCycleForDuration(int $durationDays): string
    {
        return match (true) {
            $durationDays <= 7 => 'weekly',
            $durationDays >= 365 => 'yearly',
            default => 'monthly',
        };
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
