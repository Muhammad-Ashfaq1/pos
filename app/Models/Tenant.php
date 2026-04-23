<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\Database\Concerns\TenantRun;

class Tenant extends Model implements TenantContract
{
    use CentralConnection;
    use TenantRun;

    protected $table = 'tenants';

    protected $guarded = [];

    protected $fillable = [
        'name',
        'slug',
        'owner_name',
        'owner_email',
        'owner_phone',
        'business_name',
        'business_email',
        'business_phone',
        'address',
        'city',
        'state',
        'country',
        'status',
        'approved_by',
        'approved_at',
        'rejected_at',
        'suspended_at',
        'onboarding_completed_at',
        'settings',
        'shop_name',
        'email',
        'phone',
        'business_type',
        'website_url',
        'rejected_reason',
        'onboarding_status',
    ];

    protected $casts = [
        'status' => TenantStatus::class,
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'suspended_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'settings' => 'array',
    ];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function getInternal(string $key)
    {
        return $this->getAttribute($key);
    }

    public function setInternal(string $key, $value)
    {
        $this->setAttribute($key, $value);

        return $this;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class)->where('role', User::TENANT_ADMIN);
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->name ?: $this->business_name ?: $this->shop_name ?: 'Tenant');
    }

    protected function ownerEmailAddress(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->owner_email ?: $this->email);
    }

    protected function onboardingState(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->onboarding_completed_at) {
                return 'completed';
            }

            if ($this->approved_at) {
                return 'in_progress';
            }

            return 'not_started';
        });
    }

    public function isAccessible(): bool
    {
        return $this->status->allowsLogin();
    }
}
