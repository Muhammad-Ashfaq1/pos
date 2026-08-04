<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private TenantContext $tenantContext,
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantContext->current();

        return view('employee.invoices.index', [
            'dueDays' => $tenant?->returnDaysAfterPurchase() ?? 30,
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->current();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => ['nullable', 'numeric', 'min:0'],
            'status' => [
                'nullable',
                'string',
                Rule::in(['paid', 'partially_paid', 'pending']),
            ],
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
        ]);

        $filters['due_days'] = $tenant?->returnDaysAfterPurchase() ?? 30;

        return response()->json($this->orderRepository->invoiceListing($filters));
    }

    public function create(): View
    {
        $tenant = $this->tenantContext->current();

        return view('employee.order.new-order', [
            'vehicleRequired' => $tenant?->isVehicleRequired() ?? true,
            'returnDaysAfterPurchase' => $tenant?->returnDaysAfterPurchase() ?? 30,
            'creditMinRedeemBalance' => $tenant?->creditMinRedeemBalance() ?? 50.0,
            'editOrder' => null,
            'invoiceMode' => true,
            'orderCards' => Card::query()
                ->currentlyValid()
                ->whereIn('card_type', [Card::TYPE_DISCOUNT, Card::TYPE_GIFT, Card::TYPE_REWARD])
                ->orderBy('name')
                ->get([
                    'id',
                    'product_id',
                    'card_type',
                    'discount_type',
                    'name',
                    'value',
                    'minimum_spend',
                    'valid_until',
                    'details',
                ])
                ->groupBy('card_type'),
        ]);
    }
}
