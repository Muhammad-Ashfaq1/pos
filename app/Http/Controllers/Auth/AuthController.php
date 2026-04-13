<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TenantController extends Controller
{
    // =========================
    // 1. ALL TENANTS (SHOPS)
    // =========================
    public function index()
    {
        $admin = auth()->user();

        if ($admin->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $shops = Tenant::latest()->get();

        return view('shop.index', compact('shops'));
    }

    // =========================
    // 2. APPROVE TENANT
    // =========================
    public function approve($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'approved',
            'approved_at' => now(),
            'onboarding_status' => 'in_progress',
        ]);

        // Activate shop admin user
        User::where('tenant_id', $tenant->id)
            ->where('role', 'shop_admin')
            ->update(['is_active' => 1]);

        // Optional email
        Mail::raw('Congratulations! Your shop has been approved.', function ($message) use ($tenant) {
            $message->to($tenant->email)->subject('Shop Approved');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tenant approved successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success'
        ]);
    }

    // =========================
    // 3. REJECT TENANT
    // =========================
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'rejected',
            'rejected_reason' => $request->reason,
            'is_active' => 0
        ]);

        Mail::raw('Sorry, your shop request has been rejected.', function ($message) use ($tenant) {
            $message->to($tenant->email)->subject('Shop Rejected');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tenant rejected successfully',
            'status_text' => 'Rejected',
            'badge_class' => 'bg-danger'
        ]);
    }

    // =========================
    // 4. SUSPEND TENANT
    // =========================
    public function suspend($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'suspended',
            'is_active' => 0
        ]);

        Mail::raw('Your shop has been suspended.', function ($message) use ($tenant) {
            $message->to($tenant->email)->subject('Shop Suspended');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tenant suspended successfully',
            'status_text' => 'Suspended',
            'badge_class' => 'bg-secondary'
        ]);
    }

    // =========================
    // 5. REACTIVATE TENANT
    // =========================
    public function reactivate($id)
    {
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'approved',
            'is_active' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant reactivated successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success'
        ]);
    }

    // =========================
    // 6. IMPERSONATE TENANT
    // =========================
    public function impersonate($id)
    {
        $admin = auth()->user();

        if ($admin->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $shopUser = User::where('tenant_id', $id)
            ->where('role', 'shop_admin')
            ->first();

        if (!$shopUser) {
            return back()->with('error', 'Shop admin not found');
        }

        if ($shopUser->is_active != 1) {
            return back()->with('error', 'Shop is not active');
        }

        session(['impersonator_id' => $admin->id]);

        auth()->login($shopUser);

        return redirect('/admin/dashboard');
    }

    // =========================
    // 7. STOP IMPERSONATION
    // =========================
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