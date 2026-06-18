<?php

namespace App\Http\Resources\Customer;

use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = (float) $this->amount;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $amount,
            'amount_label' => ($amount >= 0 ? '+' : '-').Currency::format(abs($amount)),
            'direction' => $amount >= 0 ? 'credit' : 'debit',
            'balance_after' => (float) $this->balance_after,
            'balance_after_label' => Currency::format((float) $this->balance_after),
            'description' => $this->description,
            'order_id' => $this->order_id,
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_label' => $this->created_at?->format('M j, Y h:i A'),
        ];
    }
}
