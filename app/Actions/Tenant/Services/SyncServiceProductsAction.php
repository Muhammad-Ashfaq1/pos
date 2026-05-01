<?php

namespace App\Actions\Tenant\Services;

use App\Models\Service;
use Illuminate\Support\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SyncServiceProductsAction
{
    public function execute(Service $service, array $mappings = []): void
    {
        DB::transaction(function () use ($service, $mappings) {



            $normalizedMappings = $this->normalizeMappings(collect($mappings));


            foreach ($service->serviceProducts as $oldMapping) {

                $product = Product::find($oldMapping->product_id);

                if (!$product) {
                    continue;
                }

                $product->increment('current_stock', $oldMapping->quantity);
            }


            $service->serviceProducts()->delete();

            if ($normalizedMappings->isEmpty()) {
                return;
            }

            foreach ($normalizedMappings as $mapping) {

                $product = Product::find($mapping['product_id']);

                if (!$product) {
                    continue;
                }

                $requestedQty = (float) $mapping['quantity'];

                if ($product->current_stock < $requestedQty) {
                    throw new \Exception(
                        "Insufficient stock for {$product->name}. Available: {$product->current_stock}"
                    );
                }

                $product->decrement('current_stock', $requestedQty);
            }


            $service->serviceProducts()->createMany(
                $normalizedMappings->map(fn($mapping) => [
                    'tenant_id'   => $service->tenant_id,
                    'product_id'  => $mapping['product_id'],
                    'quantity'    => $mapping['quantity'],
                    'unit'        => $mapping['unit'],
                    'is_required' => $mapping['is_required'],
                ])->all()
            );
        });
    }

    private function normalizeMappings(Collection $mappings): Collection
    {
        return $mappings
            ->map(function ($mapping): ?array {
                $productId = (int) data_get($mapping, 'product_id');
                $quantity = data_get($mapping, 'quantity');

                if ($productId <= 0 || $quantity === null || $quantity === '') {
                    return null;
                }

                return [
                    'product_id' => $productId,
                    'quantity' => (float) $quantity,
                    'unit' => $this->normalizeNullableString(data_get($mapping, 'unit')),
                    'is_required' => filter_var(data_get($mapping, 'is_required', false), FILTER_VALIDATE_BOOL),
                ];
            })
            ->filter()
            ->groupBy('product_id')
            ->map(function (Collection $group): array {
                return [
                    'product_id' => $group->first()['product_id'],
                    'quantity' => number_format((float) $group->sum('quantity'), 3, '.', ''),
                    'unit' => $group->pluck('unit')->filter()->last(),
                    'is_required' => $group->contains(fn (array $mapping) => $mapping['is_required']),
                ];
            })
            ->values();
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
