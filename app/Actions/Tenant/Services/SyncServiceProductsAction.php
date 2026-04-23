<?php

namespace App\Actions\Tenant\Services;

use App\Models\Service;
use Illuminate\Support\Collection;

class SyncServiceProductsAction
{
    public function execute(Service $service, array $mappings = []): void
    {
        $normalizedMappings = $this->normalizeMappings(collect($mappings));

        $service->serviceProducts()->delete();

        if ($normalizedMappings->isEmpty()) {
            return;
        }

        $service->serviceProducts()->createMany(
            $normalizedMappings
                ->map(fn (array $mapping) => [
                    'tenant_id' => $service->tenant_id,
                    'product_id' => $mapping['product_id'],
                    'quantity' => $mapping['quantity'],
                    'unit' => $mapping['unit'],
                    'is_required' => $mapping['is_required'],
                ])
                ->all()
        );
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
