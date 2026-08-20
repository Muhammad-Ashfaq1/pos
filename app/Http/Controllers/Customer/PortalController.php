<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Services\CustomerPortalService;
use App\Support\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Customer portal pages are thin shells — all customer data is loaded from
 * /api/v1/customer/* (same endpoints the Flutter app will use). Session login
 * / impersonation issues a Sanctum Bearer token via apiToken() for axios.
 */
class PortalController extends Controller
{
    private function customer(Request $request)
    {
        return $request->user() ?? auth('customer')->user();
    }

    /**
     * Issue (or reuse) a Sanctum token for the session-authenticated customer
     * so the portal JS can call the same API routes as Flutter.
     */
    public function apiToken(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($existing = session('customer_api_token')) {
            return response()->json([
                'token' => $existing,
                'token_type' => 'Bearer',
            ]);
        }

        $customer->tokens()->where('name', 'web-portal')->delete();
        $token = $customer->createToken('web-portal')->plainTextToken;
        session(['customer_api_token' => $token]);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function dashboard(): View
    {
        return view('customer.dashboard');
    }

    public function orders(): View
    {
        return view('customer.orders');
    }

    public function showOrder(int $order): View
    {
        return view('customer.order-show', [
            'orderId' => $order,
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

        $pdf = Currency::using($model->tenant, fn () => Pdf::loadView('employee.order.pdf', [
            'order' => $model,
            'details' => $details,
            'vehicleRequired' => $vehicleRequired,
        ]));

        return $pdf->download("invoice-{$model->order_number}.pdf");
    }

    public function credits(): View
    {
        return view('customer.credits');
    }

    public function vehicles(): View
    {
        return view('customer.vehicles');
    }

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
