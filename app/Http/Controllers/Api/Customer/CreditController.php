<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CreditTransactionResource;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Support\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * Wallet balance + paginated ledger of earn/redeem/adjust entries.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $transactions = CustomerCreditTransaction::query()
            ->where('customer_id', $customer->getKey())
            ->with('order:id,order_number')
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return CreditTransactionResource::collection($transactions)
            ->additional([
                'meta' => [
                    'balance' => (float) $customer->credit_balance,
                    'balance_label' => Currency::format((float) $customer->credit_balance),
                ],
            ])
            ->response();
    }
}
