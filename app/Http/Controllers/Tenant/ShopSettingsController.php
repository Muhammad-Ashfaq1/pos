<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\SaveShopGeneralSettingsRequest;
use App\Http\Requests\Tenant\Settings\SaveShopNotificationsSettingsRequest;
use App\Http\Requests\Tenant\Settings\SaveShopOperationsSettingsRequest;
use App\Http\Requests\Tenant\Settings\SaveShopRegionalSettingsRequest;
use App\Models\Tenant;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function __construct(
        private readonly ShopSettingsRepositoryInterface $repo,
        private readonly TenantContext $tenantContext,
    ) {}

    public function edit(): RedirectResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return redirect()->route('tenant.settings.shop-profile.general');
    }

    public function general(): View
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return view('tenant.settings.shop-profile.general', $this->repo->sharedViewData($tenant));
    }

    public function saveGeneral(SaveShopGeneralSettingsRequest $request): JsonResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        $result = $this->repo->saveGeneralSettings($tenant, $request->validated());

        return response()->json($result);
    }

    public function regional(): View
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return view('tenant.settings.shop-profile.regional', $this->repo->sharedViewData($tenant));
    }

    public function saveRegional(SaveShopRegionalSettingsRequest $request): JsonResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        $result = $this->repo->saveRegionalSettings($tenant, $request->validated());

        return response()->json($result);
    }

    public function operations(): View
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return view('tenant.settings.shop-profile.operations', $this->repo->sharedViewData($tenant));
    }

    public function saveOperations(SaveShopOperationsSettingsRequest $request): JsonResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        $result = $this->repo->saveOperationsSettings($tenant, $request->validated());

        return response()->json($result);
    }

    public function notifications(): View
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        return view('tenant.settings.shop-profile.notifications', $this->repo->sharedViewData($tenant));
    }

    public function saveNotifications(SaveShopNotificationsSettingsRequest $request): JsonResponse
    {
        $tenant = $this->currentTenant();

        $this->authorize('manageSettings', $tenant);

        $result = $this->repo->saveNotificationsSettings($tenant, $request->validated());

        return response()->json($result);
    }

    private function currentTenant(): Tenant
    {
        return $this->tenantContext->current()
            ?? abort(404, 'Tenant context could not be resolved.');
    }
}
