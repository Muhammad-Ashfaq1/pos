<?php

namespace App\Http\Resources\Customer;

use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact order representation for the customer's service-history list.
 * Full detail is served via OrdersRepository::details() on the show endpoint.
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => str_replace('_', ' ', (string) $this->status),
            'status_class' => $this->status === 'paid' ? 'success' : 'warning',
            'total_amount' => (float) $this->total_amount,
            'total_amount_label' => Currency::format((float) $this->total_amount),
            'credit_earned' => (float) $this->credit_earned,
            'credit_earned_label' => Currency::format((float) $this->credit_earned),
            'credit_applied' => (float) $this->credit_applied,
            'items_count' => (int) ($this->items_count ?? $this->items->sum('quantity')),
            'vehicle' => $this->whenLoaded('vehicle', fn () => [
                'plate_number' => $this->vehicle?->plate_number,
                'label' => trim(collect([
                    $this->vehicle?->year,
                    $this->vehicle?->make,
                    $this->vehicle?->model,
                ])->filter()->implode(' ')),
            ]),
            'service_summary' => $this->whenLoaded('items', fn () => $this->items->pluck('product_name')->take(3)->implode(', ')),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_label' => $this->created_at?->format('M j, Y h:i A'),
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}
