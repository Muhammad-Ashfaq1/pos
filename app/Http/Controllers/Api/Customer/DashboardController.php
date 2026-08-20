<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CreditTransactionResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Customer\OrderResource;
use App\Http\Resources\Customer\VehicleResource;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Order;
use App\Models\Vehicle;
use App\Services\CreditService;
use App\Support\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Home-screen payload for **web portal and Flutter** (`GET /api/v1/customer/dashboard`).
     * Extra keys (`stats`, `vehicles`, `recent_credits`, unlock progress) are additive —
     * existing web fields (`customer`, `credit`, `recent_orders`) stay unchanged.
     */
    public function __invoke(Request $request, CreditService $credits): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->loadMissing('tenant');

        $minRedeem = $credits->minRedeemBalance();
        $balance = (float) $customer->credit_balance;
        $remaining = max(0.0, $minRedeem - $balance);
        $progress = $minRedeem > 0 ? min(1.0, $balance / $minRedeem) : 1.0;
        $recentLimit = max(1, min(12, (int) $request->integer('recent_limit', 5)));

        $recentOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->with(['vehicle:id,year,make,model,plate_number', 'items:id,order_id,product_name,quantity'])
            ->withCount('items')
            ->latest()
            ->limit($recentLimit)
            ->get();

        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->getKey())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->limit(6)
            ->get();

        $recentCredits = CustomerCreditTransaction::query()
            ->where('customer_id', $customer->getKey())
            ->with('order:id,order_number')
            ->latest()
            ->limit(5)
            ->get();

        $orders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE);

        $visits = (int) $customer->total_visits;
        $lifetime = (float) $customer->lifetime_value;
        $averageSpend = $visits > 0 ? $lifetime / $visits : 0.0;

        return response()->json([
            'data' => [
                'customer' => (new CustomerResource($customer))->resolve(),
                'credit' => [
                    'balance' => $balance,
                    'balance_label' => Currency::format($balance),
                    'min_redeem_balance' => $minRedeem,
                    'min_redeem_balance_label' => Currency::format($minRedeem),
                    'remaining_to_unlock' => $remaining,
                    'remaining_to_unlock_label' => Currency::format($remaining),
                    'unlock_progress' => round($progress, 3),
                    'can_redeem' => $credits->canRedeem($customer, $minRedeem),
                ],
                'stats' => [
                    'visits' => $visits,
                    'lifetime_value' => $lifetime,
                    'lifetime_value_label' => Currency::format($lifetime),
                    'average_spend' => $averageSpend,
                    'average_spend_label' => Currency::format($averageSpend),
                    'loyalty_points' => (int) $customer->loyalty_points_balance,
                    'vehicles_count' => Vehicle::query()->where('customer_id', $customer->getKey())->count(),
                    'open_orders_count' => (clone $orders)
                        ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PARTIALLY_PAID])
                        ->count(),
                    'paid_orders_count' => (clone $orders)->where('status', Order::STATUS_PAID)->count(),
                    'last_visit_at' => $customer->last_visit_at?->toIso8601String(),
                    'last_visit_at_label' => $customer->last_visit_at?->format('M j, Y'),
                ],
                'vehicles' => VehicleResource::collection($vehicles)->resolve(),
                'recent_credits' => CreditTransactionResource::collection($recentCredits)->resolve(),
                'recent_orders' => OrderResource::collection($recentOrders)->resolve(),
            ],
        ]);
    }
}
