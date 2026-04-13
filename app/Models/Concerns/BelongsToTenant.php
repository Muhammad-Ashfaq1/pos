<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = static::resolveTenantId();

            if ($tenantId !== null) {
                $builder->where(
                    $builder->getModel()->qualifyColumn('tenant_id'),
                    $tenantId
                );
            }
        });

        static::creating(function (Model $model): void {
            if (blank($model->tenant_id)) {
                $tenantId = static::resolveTenantId();

                if ($tenantId !== null) {
                    $model->tenant_id = $tenantId;
                }
            }
        });

        static::updating(function (Model $model): void {
            $tenantId = static::resolveTenantId();

            if ($tenantId !== null && array_key_exists('tenant_id', $model->getOriginal())) {
                $originalTenantId = $model->getOriginal()['tenant_id'];

                if ((string) $model->tenant_id !== (string) $originalTenantId) {
                    $model->tenant_id = $originalTenantId;
                }
            }
        });
    }

    public function scopeForTenant(Builder $query, string|int|null $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? static::resolveTenantId();

        if ($tenantId === null) {
            return $query;
        }

        return $query
            ->withoutGlobalScope('tenant')
            ->where($this->qualifyColumn('tenant_id'), $tenantId);
    }

    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function resolveTenantId(): string|int|null
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->getTenantKey();
        }

        return auth()->user()?->tenant_id;
    }
}
