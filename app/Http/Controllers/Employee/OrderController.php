<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Orders\SaveOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(SaveOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user): Order {
            $requestedItems = collect($validated['items']);
            $products = Product::query()
                ->whereIn('id', $requestedItems->pluck('product_id')->unique()->all())
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== $requestedItems->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            $totalQuantity = 0;
            $subtotalAmount = 0.0;
            $orderItems = [];

            foreach ($requestedItems as $requestedItem) {
                $product = $products->get((int) $requestedItem['product_id']);
                $quantity = (int) $requestedItem['quantity'];
                $unitPrice = round((float) $product->sale_price, 2);
                $lineTotal = round($unitPrice * $quantity, 2);

                $totalQuantity += $quantity;
                $subtotalAmount += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotalAmount = round($subtotalAmount, 2);

            $order = Order::query()->create([
                'order_number' => $this->makeOrderNumber(),
                'customer_id' => $validated['customer_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'status' => Order::STATUS_PENDING,
                'total_quantity' => $totalQuantity,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => 0,
                'service_fee_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);

            $order->items()->createMany($orderItems);

            return $order->load('items');
        });

        return response()->json([
            'message' => "Order {$order->order_number} saved successfully.",
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_quantity' => $order->total_quantity,
                'subtotal_amount' => (float) $order->subtotal_amount,
                'discount_amount' => (float) $order->discount_amount,
                'service_fee_amount' => (float) $order->service_fee_amount,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ])->values(),
            ],
        ]);
    }

    private function makeOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd-His').'-'.random_int(100, 999);
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
