<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeTenantStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeTenantStatusRequest;
use App\Models\Tenant;
use App\Models\User;

class TenantController extends Controller
{
    public function __construct(
        private readonly ChangeTenantStatusAction $changeTenantStatusAction,
    ) {
    }

    public function index()
    {
        $shops = Tenant::query()
            ->with('adminUser')
            ->latest()
            ->get();

        return view('shop.index', compact('shops'));
    }

    public function changeStatus(ChangeTenantStatusRequest $request, string $id, string $action)
    {
        $tenant = Tenant::query()->with('adminUser')->findOrFail($id);

        return response()->json(
            $this->changeTenantStatusAction->execute(
                tenant: $tenant,
                action: $action,
                reason: $request->validated('reason'),
            )
        );
    }

    public function impersonate($id)
    {
        $admin = auth()->user();
        $tenant = Tenant::query()->with('adminUser')->findOrFail($id);
        $shop = $tenant->adminUser;

        if (! $shop) {
            return back()->with('error', 'Shop admin not found.');
        }

        if ($tenant->status->value !== 'approved') {
            return back()->with('error', 'Only approved shops can be impersonated');
        }

        session(['impersonator_id' => $admin->id]);

        auth()->login($shop);

        return redirect('/admin/dashboard');
    }

    public function stopImpersonate()
    {
        $adminId = session('impersonator_id');

        if ($adminId) {
            auth()->loginUsingId($adminId);
            session()->forget('impersonator_id');
        }

        return redirect('/admin/shops');
    }
}
