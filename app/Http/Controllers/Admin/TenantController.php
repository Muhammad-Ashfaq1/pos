<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TenantController extends Controller
{
    // =========================
    // 1. ALL SHOPS (TENANTS)
    // =========================
    public function index()
    {


        $shops = User::where('role', 'shop_admin')
            ->latest()
            ->get();

        return view('shop.index', compact('shops'));
    }

    // =========================
    // 2. APPROVE SHOP
    // =========================
    public function approve($id)
    {
        $shop = User::where('role', 'shop_admin')->findOrFail($id);

        $shop->update([
            'approval_status' => 'approved',
            'is_active' => 1
        ]);

        Mail::raw('Your shop has been approved.', function ($message) use ($shop) {
            $message->to($shop->email)->subject('Shop Approved');
        });

        return response()->json([
            'success' => true,
            'message' => 'Shop approved successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success'
        ]);
    }

    // =========================
    // 3. REJECT SHOP
    // =========================
    public function reject(Request $request, $id)
    {
        $shop = User::where('role', 'shop_admin')->findOrFail($id);

        $shop->update([
            'approval_status' => 'rejected',
            'is_active' => 0
        ]);

        Mail::raw('Your shop request has been rejected.', function ($message) use ($shop) {
            $message->to($shop->email)->subject('Shop Rejected');
        });

        return response()->json([
            'success' => true,
            'message' => 'Shop rejected successfully',
            'status_text' => 'Rejected',
            'badge_class' => 'bg-danger'
        ]);
    }

    // =========================
    // 4. SUSPEND SHOP
    // =========================
    public function suspend($id)
    {
        $shop = User::where('role', 'shop_admin')->findOrFail($id);

        $shop->update([
            'approval_status' => 'suspended',
            'is_active' => 0
        ]);

        Mail::raw('Your shop has been suspended.', function ($message) use ($shop) {
            $message->to($shop->email)->subject('Shop Suspended');
        });

        return response()->json([
            'success' => true,
            'message' => 'Shop suspended successfully',
            'status_text' => 'Suspended',
            'badge_class' => 'bg-secondary'
        ]);
    }

    // =========================
    // 5. REACTIVATE SHOP
    // =========================
    public function reactivate($id)
    {
        $shop = User::where('role', 'shop_admin')->findOrFail($id);

        $shop->update([
            'approval_status' => 'approved',
            'is_active' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop reactivated successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success'
        ]);
    }

    // =========================
    // 6. IMPERSONATE SHOP
    // =========================
    public function impersonate($id)
    {
        $admin = auth()->user();

        if ($admin->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $shop = User::where('role', 'shop_admin')->findOrFail($id);

        if ($shop->approval_status !== 'approved') {
            return back()->with('error', 'Only approved shops can be impersonated');
        }

        session(['impersonator_id' => $admin->id]);

        auth()->login($shop);

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
