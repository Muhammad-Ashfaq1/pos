<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'approve' => [
            'user_is_active' => true,
            'tenant_status' => 'approved',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success',
            'message' => 'Shop approved successfully',
        ],
        'reject' => [
            'user_is_active' => false,
            'tenant_status' => 'rejected',
            'status_text' => 'Rejected',
            'badge_class' => 'bg-danger',
            'message' => 'Shop rejected successfully',
        ],
        'suspend' => [
            'user_is_active' => false,
            'tenant_status' => 'suspended',
            'status_text' => 'Suspended',
            'badge_class' => 'bg-secondary',
            'message' => 'Shop suspended successfully',
        ],
        'reactivate' => [
            'user_is_active' => true,
            'tenant_status' => 'approved',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success',
            'message' => 'Shop reactivated successfully',
        ],
    ];

    public function index()
    {
        $shops = User::query()
            ->where('role', User::TENANT_ADMIN)
            ->latest()
            ->get();

        return view('shop.index', compact('shops'));
    }

    public function pending()
    {
        $shops = User::query()
            ->where('role', User::TENANT_ADMIN)
            ->whereHas('tenant', function ($query): void {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get();

        return view('shop.index', compact('shops'));
    }

    public function changeStatus(Request $request, $id, string $action)
    {
        abort_unless(array_key_exists($action, self::STATUS_TRANSITIONS), 404);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $shop = User::query()->where('role', User::TENANT_ADMIN)->findOrFail($id);
        $transition = self::STATUS_TRANSITIONS[$action];

        $shop->update([
            'is_active' => $transition['user_is_active'],
        ]);

        optional($shop->tenant)->update([
            'status' => $transition['tenant_status'],
            'approved_at' => $transition['tenant_status'] === 'approved' ? now() : optional($shop->tenant)->approved_at,
            'rejected_reason' => $transition['tenant_status'] === 'rejected'
                ? ($request->string('reason')->toString() ?: null)
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $transition['message'],
            'status_text' => $transition['status_text'],
            'badge_class' => $transition['badge_class'],
        ]);
    }

    public function impersonate($id)
    {
        $admin = auth()->user();
        $shop = User::query()->where('role', User::TENANT_ADMIN)->findOrFail($id);

        if (optional($shop->tenant)->status !== 'approved') {
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
