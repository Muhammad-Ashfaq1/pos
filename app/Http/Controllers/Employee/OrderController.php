<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Orders\SaveOrderRequest;
use App\Models\Order;
use App\Repositories\Interface\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function index(): View
    {
        return view('employee.order.index');
    }

    public function create(): View
    {
        return view('employee.order.new-order');
    }

    public function listing(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'tab' => ['nullable', 'string', 'in:today,all,pending'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => [
                'nullable',
                'string',
                Rule::in([
                    'latest',
                    'oldest',
                    'amount_desc',
                    'amount_asc',
                    'customer_name',
                    'date_opened',
                    'order_id',
                    'order_total',
                ]),
            ],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search_fields' => ['nullable', 'array'],
            'search_fields.*' => [
                'string',
                Rule::in(['order_number', 'customer_id', 'paid_status', 'retailer', 'time', 'date']),
            ],
        ]);

        return response()->json($this->orderRepository->listing($filters));
    }

    public function show(Order $order): View
    {
        return view('employee.order.show', [
            'order' => $this->orderRepository->details($order),
        ]);
    }

    public function store(SaveOrderRequest $request): JsonResponse
    {
        $result = $this->orderRepository->store($request->validated(), $request->user());

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
}
