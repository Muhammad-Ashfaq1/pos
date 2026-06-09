<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerCreditTransaction;
use App\Models\Order;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Services\CustomerPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Server-rendered customer portal. Customers reach these pages by signing in
 * through the shared /login form (customer guard) — there is no separate login
 * page. Tenancy is initialised by the customer.tenant.init middleware, so the
 * BelongsToTenant scope keeps every query within the customer's shop.
 */
class PortalController extends Controller
{
    private function customer(Request $request)
    {
        return $request->user() ?? auth('customer')->user();
    }

    public function dashboard(Request $request): View
    {
        $customer = $this->customer($request);

        $recentOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('customer', 'recentOrders'));
    }

    public function orders(Request $request): View
    {
        $customer = $this->customer($request);

        $orders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('customer.orders', compact('customer', 'orders'));
    }

    public function showOrder(Request $request, int $order, OrderRepositoryInterface $orders): View
    {
        $customer = $this->customer($request);

        $model = Order::query()
            ->where('customer_id', $customer->getKey())
            ->findOrFail($order);

        return view('customer.order-show', [
            'customer' => $customer,
            'order' => $orders->details($model),
        ]);
    }

    public function credits(Request $request): View
    {
        $customer = $this->customer($request);

        $transactions = CustomerCreditTransaction::query()
            ->where('customer_id', $customer->getKey())
            ->with('order:id,order_number')
            ->latest()
            ->paginate(20);

        return view('customer.credits', compact('customer', 'transactions'));
    }

    public function profile(Request $request): View
    {
        return view('customer.profile', ['customer' => $this->customer($request)]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = $this->customer($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->fill($data)->save();

        return back()->with('success', 'Profile updated.');
    }

    // ── Public set-password (invite / forgot links) ───────────────────────────

    public function reset(Request $request): View
    {
        return view('customer.reset', [
            'shop' => $request->query('shop'),
            'email' => $request->query('email'),
            'token' => $request->query('token'),
        ]);
    }

    public function resetSubmit(Request $request, CustomerPortalService $portal): RedirectResponse
    {
        $data = $request->validate([
            'shop' => ['required', 'string'],
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $tenant = $portal->findTenantBySlug($data['shop']);

        if (! $tenant) {
            throw ValidationException::withMessages(['shop' => 'Shop not found.']);
        }

        $portal->resetPassword($tenant, $data['email'], $data['token'], $data['password']);

        return redirect()->route('login')->with('success', 'Password set. You can now sign in.');
    }
}
