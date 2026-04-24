<?php

namespace App\Repositories\Interface;

use App\Models\Tenant;

interface ShopSettingsRepositoryInterface
{
    public function sharedViewData(Tenant $tenant): array;

    public function saveGeneralSettings(Tenant $tenant, array $data): array;

    public function saveRegionalSettings(Tenant $tenant, array $data): array;

    public function saveOperationsSettings(Tenant $tenant, array $data): array;

    public function saveNotificationsSettings(Tenant $tenant, array $data): array;
}
