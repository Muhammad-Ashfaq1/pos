<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\Interface\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * Paginated service history for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $orders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->with(['vehicle:id,plate_number,make,model,year', 'items:id,order_id,product_name,quantity'])
            ->withCount('items')
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return OrderResource::collection($orders)->response();
    }

    public function show(Request $request, int $order): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $model = Order::query()
            ->where('customer_id', $customer->getKey())
            ->findOrFail($order);

        // Reuse the staff-facing detail transform for a single source of truth.
        return response()->json([
            'data' => $this->orderRepository->details($model),
        ]);
    }
}
