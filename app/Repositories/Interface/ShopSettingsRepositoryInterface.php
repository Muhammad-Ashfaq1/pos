<?php

namespace App\Repositories\Interface;

use App\Models\Tenant;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface ShopSettingsRepositoryInterface
{
    public function edit(Tenant $tenant): View;

    public function update(Tenant $tenant, array $data, ?Authenticatable $user = null): array;
}
