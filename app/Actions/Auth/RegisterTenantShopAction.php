<?php

namespace App\Actions\Auth;

use App\Actions\Admin\SaveShopAction;
use App\Enums\TenantStatus;
use App\Models\User;

class RegisterTenantShopAction
{
    public function __construct(
        private readonly SaveShopAction $saveShopAction
    ) {}

    public function execute(array $payload): User
    {
        $payload['status'] = TenantStatus::Pending->value;

        $result = $this->saveShopAction->execute($payload);
        /** @var \App\Models\Tenant $tenant */
        $tenant = $result['data'];

        return $tenant->adminUser;
    }
}
