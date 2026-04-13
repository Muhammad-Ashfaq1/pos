<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;
use Stancl\Tenancy\Database\Concerns\TenantRun;
use Stancl\Tenancy\Database\TenantCollection;
use Stancl\Tenancy\Events;

class Tenant extends Model implements TenantContract
{
    use CentralConnection;
    use HasDomains;
    use HasInternalKeys;
    use TenantRun;
    use InvalidatesResolverCache;

    protected $table = 'tenants';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $fillable = [
        'id',
        'shop_name',
        'business_type',
        'owner_name',
        'email',
        'phone',
        'website_url',
        'address',
        'city',
        'state',
        'country',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'onboarding_status',
    ];

    protected $casts = [
        'status' => TenantStatus::class,
        'approved_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'saving' => Events\SavingTenant::class,
        'saved' => Events\TenantSaved::class,
        'creating' => Events\CreatingTenant::class,
        'created' => Events\TenantCreated::class,
        'updating' => Events\UpdatingTenant::class,
        'updated' => Events\TenantUpdated::class,
        'deleting' => Events\DeletingTenant::class,
        'deleted' => Events\TenantDeleted::class,
    ];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class)->where('role', User::TENANT_ADMIN);
    }
}
