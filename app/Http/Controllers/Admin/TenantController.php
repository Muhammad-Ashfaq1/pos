<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeTenantStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeTenantStatusRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        session(['impersonator_id' => $admin->id]);

        auth()->login($shop);

        return redirect()->route('tenant.dashboard');
    }

    public function stopImpersonate(): RedirectResponse
    {
        $adminId = session('impersonator_id');

        if ($adminId) {
            auth()->loginUsingId($adminId);
            session()->forget('impersonator_id');
        }

        return redirect()->route('admin.shops.index');
    }
}
