<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeTenantStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeTenantStatusRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private readonly ChangeTenantStatusAction $changeTenantStatusAction,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);

        $shops = Tenant::query()
            ->with('adminUser')
            ->latest()
            ->get();

        return view('shop.index', compact('shops'));
    }

    public function edit(Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);

        $tenant->loadMissing('adminUser');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tenant->id,
                'owner_name' => $tenant->owner_name ?: $tenant->adminUser?->name ?: '',
                'owner_email' => $tenant->owner_email ?: $tenant->email ?: $tenant->adminUser?->email ?: '',
                'shop_name' => $tenant->display_name,
                'status' => $tenant->status->value,
                'website_url' => $tenant->website_url ?? '',
                'business_type' => $tenant->business_type ?? '',
                'country' => $tenant->country ?? '',
                'state' => $tenant->state ?? '',
                'city' => $tenant->city ?? '',
                'phone' => $tenant->owner_phone ?: $tenant->phone ?: '',
                'address' => $tenant->address ?? '',
            ],
        ]);
    }

    public function save(\App\Http\Requests\Admin\SaveShopRequest $request, \App\Actions\Admin\SaveShopAction $action): JsonResponse
    {
        $validated = $request->validated();
        $tenant = ! empty($validated['id']) ? Tenant::query()->findOrFail($validated['id']) : null;

        if ($tenant) {
            $this->authorize('update', $tenant);
        } else {
            $this->authorize('create', Tenant::class);
        }

        $result = $action->execute($validated, $tenant);

        return response()->json($result);
    }

    public function changeStatus(ChangeTenantStatusRequest $request, Tenant $tenant, string $action): JsonResponse
    {
        $this->authorize('updateStatus', $tenant);

        return response()->json(
            $this->changeTenantStatusAction->execute(
                tenant: $tenant->loadMissing('adminUser'),
                action: $action,
                reason: $request->validated('reason'),
            )
        );
    }

    public function impersonate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('impersonate', $tenant);

        $admin = auth()->user();
        $tenant->loadMissing('adminUser');
        $shop = $tenant->adminUser;

        if (! $shop) {
            return back()->with('error', 'Shop admin not found.');
        }

        if (! $tenant->isAccessible()) {
            return back()->with('error', $tenant->status->loginBlockedMessage());
        }

        session([
            'impersonator_id' => $admin->id,
            'impersonator_return_url' => route('admin.shops.index'),
        ]);

        auth()->login($shop);

        return redirect()->route('tenant.dashboard');
    }

    public function stopImpersonate(): RedirectResponse
    {
        $adminId = session('impersonator_id');
        $returnUrl = session('impersonator_return_url');
        $wasCustomer = (bool) session('impersonating_customer');

        if ($wasCustomer) {
            Auth::guard('customer')->logout();
            session()->forget(['impersonator_id', 'impersonator_return_url', 'impersonating_customer', 'customer_api_token']);

            if ($adminId && ! Auth::guard('web')->check()) {
                Auth::guard('web')->loginUsingId($adminId);
            }

            return redirect($returnUrl ?: route('tenant.ecommerce.customers.index'));
        }

        if ($adminId) {
            Auth::guard('web')->loginUsingId($adminId);
            session()->forget(['impersonator_id', 'impersonator_return_url']);
        }

        return redirect($returnUrl ?: route('admin.shops.index'));
    }
}
