<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\SaveShopSettingsRequest;
use App\Models\Tenant;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function __construct(
        private readonly ShopSettingsRepositoryInterface $repo,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function edit(): View
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return $this->repo->edit($tenant);
    }

    public function update(SaveShopSettingsRequest $request): RedirectResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        $result = $this->repo->update($tenant, $request->validated(), $request->user());

        return redirect()
            ->route('tenant.settings.shop-profile.edit')
            ->with('success', $result['message']);
    }

    private function currentTenant(): Tenant
    {
        return $this->tenantContext->current()
            ?? abort(404, 'Tenant context could not be resolved.');
    }
}
