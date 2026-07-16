<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OrderCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class OrderCartController extends Controller
{
    private const MAX_PAYLOAD_BYTES = 524288;

    public function show(Request $request): JsonResponse
    {
        $cart = OrderCart::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->first();

        return response()->json([
            'data' => $cart?->payload ?? $this->emptyPayload(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'orders' => ['present', 'array', 'max:20'],
            'active_order_id' => ['nullable', 'string', 'max:100'],
            'next_order_number' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $payload = $this->sanitizePayload($request->all());
        $encoded = json_encode($payload);

        if ($encoded === false || strlen($encoded) > self::MAX_PAYLOAD_BYTES) {
            throw ValidationException::withMessages([
                'orders' => 'The saved cart is too large.',
            ]);
        }

        $cart = OrderCart::query()->updateOrCreate(
            ['user_id' => $request->user()->getAuthIdentifier()],
            ['payload' => $payload]
        );

        return response()->json([
            'message' => 'Cart saved.',
            'data' => $cart->payload,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        OrderCart::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->delete();

        return response()->json([
            'message' => 'Cart cleared.',
            'data' => $this->emptyPayload(),
        ]);
    }

    private function sanitizePayload(array $payload): array
    {
        $orders = collect(Arr::get($payload, 'orders', []))
            ->filter(fn ($order) => is_array($order))
            ->take(20)
            ->map(fn (array $order, int $index) => $this->sanitizeOrder($order, $index))
            ->filter()
            ->values()
            ->all();

        $orderIds = collect($orders)->pluck('id')->all();
        $activeOrderId = $this->limitedString(Arr::get($payload, 'active_order_id'), 100);

        if (! in_array($activeOrderId, $orderIds, true)) {
            $activeOrderId = $orderIds[0] ?? null;
        }

        return [
            'orders' => $orders,
            'active_order_id' => $activeOrderId,
            'next_order_number' => max(1, min((int) Arr::get($payload, 'next_order_number', 1), 9999)),
        ];
    }

    private function sanitizeOrder(array $order, int $index): ?array
    {
        $id = $this->limitedString(Arr::get($order, 'id'), 100);

        if ($id === null) {
            return null;
        }

        $items = collect(Arr::get($order, 'items', []))
            ->filter(fn ($item) => is_array($item))
            ->take(200)
            ->map(fn (array $item) => $this->sanitizeItem($item))
            ->filter()
            ->values()
            ->all();

        $serviceFees = collect(Arr::get($order, 'serviceFees', []))
            ->filter(fn ($fee) => is_array($fee))
            ->take(50)
            ->map(fn (array $fee) => $this->sanitizeDraftValue($fee))
            ->filter(fn ($fee) => is_array($fee))
            ->values()
            ->all();

        return [
            'id' => $id,
            'label' => $this->limitedString(Arr::get($order, 'label'), 60) ?? 'Order '.($index + 1),
            'items' => $items,
            'customer' => $this->sanitizeSelection(Arr::get($order, 'customer')),
            'vehicle' => $this->sanitizeSelection(Arr::get($order, 'vehicle')),
            'serviceFees' => $serviceFees,
        ];
    }

    private function sanitizeItem(array $item): ?array
    {
        $id = filter_var(Arr::get($item, 'id'), FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            return null;
        }

        return [
            'id' => $id,
            'name' => $this->limitedString(Arr::get($item, 'name'), 150) ?? 'Product',
            'price' => $this->money(Arr::get($item, 'price')),
            'qty' => max(1, min((int) Arr::get($item, 'qty', 1), 9999)),
            'discount' => $this->sanitizeDraftValue(Arr::get($item, 'discount')),
            'tax_percentage' => max(0, min((float) Arr::get($item, 'tax_percentage', 0), 100)),
            'current_stock' => max(0, min((int) Arr::get($item, 'current_stock', 0), 9999999)),
            'track_inventory' => filter_var(Arr::get($item, 'track_inventory', false), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    private function sanitizeSelection(mixed $selection): ?array
    {
        if (! is_array($selection) || blank(Arr::get($selection, 'id'))) {
            return null;
        }

        $sanitized = $this->sanitizeDraftValue($selection);

        return is_array($sanitized) ? $sanitized : null;
    }

    private function sanitizeDraftValue(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 6) {
            return null;
        }

        if (is_array($value)) {
            $sanitized = [];
            $count = 0;

            foreach ($value as $key => $nestedValue) {
                if ($count >= 50) {
                    break;
                }

                $sanitizedKey = $this->limitedString($key, 80);

                if ($sanitizedKey === null) {
                    continue;
                }

                $sanitizedValue = $this->sanitizeDraftValue($nestedValue, $depth + 1);

                if ($sanitizedValue !== null) {
                    $sanitized[$sanitizedKey] = $sanitizedValue;
                    $count++;
                }
            }

            return $sanitized;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->limitedString($value, 1000);
        }

        return null;
    }

    private function money(mixed $value): float
    {
        return round(max(0, min((float) $value, 999999.99)), 2);
    }

    private function limitedString(mixed $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }

    private function emptyPayload(): array
    {
        return [
            'orders' => [],
            'active_order_id' => null,
            'next_order_number' => 1,
        ];
    }
}
