<?php

namespace App\Http\Resources\Customer;

use App\Models\Customer;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'customer_type' => $this->customer_type,
            'customer_type_label' => Customer::typeOptions()[$this->customer_type] ?? ucfirst((string) $this->customer_type),
            'credit_balance' => (float) $this->credit_balance,
            'credit_balance_label' => Currency::format((float) $this->credit_balance),
            'loyalty_points_balance' => (int) $this->loyalty_points_balance,
            'total_visits' => (int) $this->total_visits,
            'lifetime_value' => (float) $this->lifetime_value,
            'lifetime_value_label' => Currency::format((float) $this->lifetime_value),
            'last_visit_at' => $this->last_visit_at?->toIso8601String(),
            'last_visit_at_label' => $this->last_visit_at?->format('M j, Y'),
            'shop' => [
                'id' => $this->tenant?->id,
                'name' => $this->tenant?->name ?? $this->tenant?->shop_name,
                'slug' => $this->tenant?->slug,
            ],
        ];
    }
}
