<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerCreditTransaction;
use App\Models\Order;
use App\Models\Vehicle;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Services\CreditService;
use App\Services\CustomerPortalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $credits = app(CreditService::class);
        $creditMinRedeemBalance = $credits->minRedeemBalance();
        $creditCanRedeem = $credits->canRedeem($customer, $creditMinRedeemBalance);

        $recentOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->with('vehicle:id,year,make,model,plate_number')
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact(
            'customer',
            'recentOrders',
            'creditMinRedeemBalance',
            'creditCanRedeem',
        ));
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

    public function downloadOrderPdf(Request $request, int $order, OrderRepositoryInterface $orders): Response
    {
        $customer = $this->customer($request);

        $model = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->with('tenant')
            ->findOrFail($order);

        $details = $orders->details($model);
        $vehicleRequired = $model->tenant?->isVehicleRequired() ?? true;

        $pdf = Pdf::loadView('employee.order.pdf', [
            'order' => $model,
            'details' => $details,
            'vehicleRequired' => $vehicleRequired,
        ]);

        return $pdf->download("invoice-{$model->order_number}.pdf");
    }

    public function credits(Request $request): View
    {
        $customer = $this->customer($request);
        $type = trim((string) $request->query('type', ''));
        $allowedTypes = [
            CustomerCreditTransaction::TYPE_EARN,
            CustomerCreditTransaction::TYPE_REDEEM,
            CustomerCreditTransaction::TYPE_ADJUST,
            CustomerCreditTransaction::TYPE_EXPIRE,
        ];

        $transactions = CustomerCreditTransaction::query()
            ->where('customer_id', $customer->getKey())
            ->when(
                in_array($type, $allowedTypes, true),
                fn ($query) => $query->where('type', $type)
            )
            ->with('order:id,order_number')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $credits = app(CreditService::class);
        $creditMinRedeemBalance = $credits->minRedeemBalance();
        $creditCanRedeem = $credits->canRedeem($customer, $creditMinRedeemBalance);

        return view('customer.credits', compact(
            'customer',
            'transactions',
            'type',
            'creditMinRedeemBalance',
            'creditCanRedeem',
        ));
    }

    public function vehicles(Request $request): View
    {
        $customer = $this->customer($request);

        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->getKey())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return view('customer.vehicles', compact('customer', 'vehicles'));
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
