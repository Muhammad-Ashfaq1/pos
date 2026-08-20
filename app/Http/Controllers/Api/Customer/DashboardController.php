<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Customer\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Services\CreditService;
use App\Support\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Home-screen payload for web portal + Flutter.
     *
     * GET /api/v1/customer/dashboard
     */
    public function __invoke(Request $request, CreditService $credits): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->loadMissing('tenant');

        $minRedeem = $credits->minRedeemBalance();

        $recentOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->with(['vehicle:id,year,make,model,plate_number', 'items:id,order_id,product_name,quantity'])
            ->withCount('items')
            ->latest()
            ->limit((int) $request->integer('recent_limit', 5))
            ->get();

        return response()->json([
            'data' => [
                'customer' => (new CustomerResource($customer))->resolve(),
                'credit' => [
                    'balance' => (float) $customer->credit_balance,
                    'balance_label' => Currency::format((float) $customer->credit_balance),
                    'min_redeem_balance' => $minRedeem,
                    'min_redeem_balance_label' => Currency::format($minRedeem),
                    'can_redeem' => $credits->canRedeem($customer, $minRedeem),
                ],
                'recent_orders' => OrderResource::collection($recentOrders)->resolve(),
            ],
        ]);
    }
}
