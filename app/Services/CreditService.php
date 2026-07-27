<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Order;
use App\Support\Currency;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Single source of truth for the customer store-credit wallet.
 *
 * Every mutation updates customers.credit_balance AND writes a ledger row
 * (customer_credit_transactions) with the resulting balance, inside one
 * transaction with a row lock so concurrent orders cannot double-spend.
 */
class CreditService
{
    /**
     * Tenant minimum balance required before any store credit can be redeemed.
     */
    public function minRedeemBalance(): float
    {
        $tenant = app(TenantContext::class)->current();

        return $tenant?->creditMinRedeemBalance() ?? 50.0;
    }

    /**
     * Whether the customer's current wallet may be used at checkout.
     */
    public function canRedeem(Customer $customer, ?float $minimum = null): bool
    {
        $min = $minimum ?? $this->minRedeemBalance();

        return round((float) $customer->credit_balance, 2) + 0.001 >= $min;
    }

    /**
     * Reject redeem attempts while the wallet is below the unlock threshold.
     */
    public function assertCanRedeem(Customer $customer, ?float $minimum = null): void
    {
        $min = $minimum ?? $this->minRedeemBalance();

        if ($this->canRedeem($customer, $min)) {
            return;
        }

        throw ValidationException::withMessages([
            'credits_applied' => 'Store credit can be used when the balance is at least '.Currency::format($min).'.',
        ]);
    }

    /**
     * Grant store credit (e.g. earned on a paid order).
     * Returns null when the amount rounds to zero (nothing recorded).
     */
    public function earn(Customer $customer, float $amount, ?Order $order, string $description, ?int $userId = null): ?CustomerCreditTransaction
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return null;
        }

        return $this->record($customer, CustomerCreditTransaction::TYPE_EARN, $amount, $order, $description, $userId);
    }

    /**
     * Redeem store credit against an order. Guards against unlock threshold and over-spending.
     */
    public function redeem(Customer $customer, float $amount, ?Order $order, ?int $userId = null): CustomerCreditTransaction
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'credits_applied' => 'Credit to redeem must be greater than zero.',
            ]);
        }

        $this->assertCanRedeem($customer);

        return $this->record($customer, CustomerCreditTransaction::TYPE_REDEEM, -$amount, $order, 'Store credit redeemed', $userId);
    }

    /**
     * Manual staff correction (signed amount: positive adds, negative removes).
     */
    public function adjust(Customer $customer, float $signedAmount, string $description, ?int $userId = null): CustomerCreditTransaction
    {
        $signedAmount = round($signedAmount, 2);

        if ($signedAmount === 0.0) {
            throw ValidationException::withMessages([
                'amount' => 'Adjustment amount cannot be zero.',
            ]);
        }

        return $this->record($customer, CustomerCreditTransaction::TYPE_ADJUST, $signedAmount, null, $description, $userId);
    }

    /**
     * Locks the customer row, applies the signed delta, and writes the ledger entry.
     */
    private function record(Customer $customer, string $type, float $signedAmount, ?Order $order, string $description, ?int $userId): CustomerCreditTransaction
    {
        return DB::transaction(function () use ($customer, $type, $signedAmount, $order, $description, $userId): CustomerCreditTransaction {
            /** @var Customer $locked */
            $locked = Customer::query()->lockForUpdate()->findOrFail($customer->getKey());

            $currentBalance = round((float) $locked->credit_balance, 2);
            $newBalance = round($currentBalance + $signedAmount, 2);

            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'credits_applied' => 'Insufficient store credit balance.',
                ]);
            }

            $locked->forceFill(['credit_balance' => $newBalance])->save();

            // Keep the in-memory model the caller holds consistent.
            $customer->setAttribute('credit_balance', $newBalance);

            $transaction = new CustomerCreditTransaction([
                'customer_id' => $locked->getKey(),
                'order_id' => $order?->getKey(),
                'type' => $type,
                'amount' => round($signedAmount, 2),
                'balance_after' => $newBalance,
                'description' => $description,
                'created_by' => $userId,
            ]);
            $transaction->tenant_id = $locked->tenant_id;
            $transaction->save();

            return $transaction;
        });
    }
}
