<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CreditTransactionResource;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Services\CreditService;
use App\Support\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * Wallet balance + paginated ledger of earn/redeem/adjust entries.
     *
     * Query params (Flutter + web):
     * - type: earn|redeem|adjust|expire (optional)
     * - per_page: int (default 20)
     * - page: int
     */
    public function index(Request $request, CreditService $credits): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

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
            ->paginate((int) $request->integer('per_page', 20));

        $minRedeem = $credits->minRedeemBalance();

        return CreditTransactionResource::collection($transactions)
            ->additional([
                'meta' => [
                    'balance' => (float) $customer->credit_balance,
                    'balance_label' => Currency::format((float) $customer->credit_balance),
                    'min_redeem_balance' => $minRedeem,
                    'min_redeem_balance_label' => Currency::format($minRedeem),
                    'can_redeem' => $credits->canRedeem($customer, $minRedeem),
                    'type' => in_array($type, $allowedTypes, true) ? $type : null,
                ],
            ])
            ->response();
    }
}
