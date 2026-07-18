<?php

namespace App\Repositories;

use App\Models\Card;
use App\Models\Product;
use App\Repositories\Interface\CardRepositoryInterface;
use App\Support\Currency;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class CardsRepository implements CardRepositoryInterface
{
    public function index(string $cardType): View
    {
        $modules = [
            Card::TYPE_DISCOUNT => [
                'title' => 'Discount Cards',
                'singular' => 'Discount Card',
                'icon' => 'tabler-ticket',
            ],
            Card::TYPE_GIFT => [
                'title' => 'Gift Cards',
                'singular' => 'Gift Card',
                'icon' => 'tabler-gift',
            ],
            Card::TYPE_REWARD => [
                'title' => 'Reward Cards',
                'singular' => 'Reward Card',
                'icon' => 'tabler-trophy',
            ],
        ];

        return view('tenant.ecommerce.cards.index', [
            'cardType' => $cardType,
            'modules' => $modules,
            'activeModule' => $modules[$cardType],
            'listingUrl' => route('tenant.ecommerce.cards.listing', ['type' => $cardType]),
            'editUrlTemplate' => route('tenant.ecommerce.cards.edit', [
                'type' => $cardType,
                'card' => '__CARD__',
            ]),
            'saveUrl' => route('tenant.ecommerce.cards.save', ['type' => $cardType]),
            'discountTypes' => Card::discountTypeOptions(),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(array $data, ?Card $card = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $card !== null;
        $userId = $user?->getAuthIdentifier();
        $payload = $this->buildPayload($data, $card);

        if ($isUpdate) {
            $card->fill($payload);
            $card->save();
        } else {
            $card = new Card($payload);
            $card->forceFill([
                'created_by' => $userId,
            ]);
            $card->save();
        }

        $card->load('product:id,name');

        $label = Card::typeOptions()[$card->card_type] ?? 'Card';

        return [
            'success' => true,
            'message' => $isUpdate
                ? "{$label} updated successfully."
                : "{$label} created successfully.",
            'data' => $this->transformCard($card, $user),
        ];
    }

    public function destroy(Card $card): array
    {
        $label = Card::typeOptions()[$card->card_type] ?? 'Card';
        $card->delete();

        return [
            'success' => true,
            'message' => "{$label} deleted successfully.",
        ];
    }

    public function getCardsListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $cardType = $filters['card_type'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Card::query()
            ->when($cardType !== '', function (Builder $query) use ($cardType): void {
                $query->where('card_type', $cardType);
            });

        $filteredQuery = Card::query()
            ->with('product:id,name')
            ->when($cardType !== '', function (Builder $query) use ($cardType): void {
                $query->where('card_type', $cardType);
            })
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $cards = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformCards($cards, $user),
        ];
    }

    private function buildPayload(array $data, ?Card $existing = null): array
    {
        $cardType = $data['card_type'];
        $productIds = collect($data['product_ids'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $details = is_array($existing?->details) ? $existing->details : [];
        if ($productIds === []) {
            unset($details['product_ids']);
        } else {
            $details['product_ids'] = $productIds;
        }

        return [
            'card_type' => $cardType,
            'name' => trim((string) $data['name']),
            'discount_type' => $cardType === Card::TYPE_DISCOUNT
                ? ($data['discount_type'] ?? null)
                : null,
            'value' => $this->normalizeMoney($data['value']),
            'minimum_spend' => $this->normalizeMoney($data['minimum_spend'] ?? 0),
            'product_id' => $productIds[0] ?? null,
            'details' => $details === [] ? null : $details,
            'valid_until' => ($data['valid_until'] ?? null) !== null && $data['valid_until'] !== ''
                ? $data['valid_until']
                : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex.'.data')
            : null;

        $sortableColumns = [
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'value' => fn (Builder $builder, string $direction) => $builder->orderBy('value', $direction),
            'minimum_spend' => fn (Builder $builder, string $direction) => $builder->orderBy('minimum_spend', $direction),
            'valid_until' => fn (Builder $builder, string $direction) => $builder->orderBy('valid_until', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'value_high_low' => $query->orderByDesc('value')->orderBy('name')->orderBy('id'),
            'valid_until' => $query->orderBy('valid_until')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformCards(Collection $cards, ?Authenticatable $user = null): array
    {
        $productNamesById = $this->productNamesById(
            $cards->flatMap(fn (Card $card) => $card->productIds())->all()
        );

        return $cards
            ->map(fn (Card $card) => $this->transformCard($card, $user, $productNamesById))
            ->all();
    }

    public function getCardFormData(Card $card, ?Authenticatable $user = null): array
    {
        $card->loadMissing('product:id,name');

        return $this->transformCard(
            $card,
            $user,
            $this->productNamesById($card->productIds())
        );
    }

    private function transformCard(Card $card, ?Authenticatable $user = null, array $productNamesById = []): array
    {
        $cardTypes = Card::typeOptions();
        $discountTypes = Card::discountTypeOptions();
        $productIds = $card->productIds();

        return [
            'id' => $card->id,
            'name' => $card->name,
            'card_type' => $card->card_type,
            'card_type_label' => $cardTypes[$card->card_type] ?? ucfirst((string) $card->card_type),
            'discount_type' => $card->discount_type,
            'discount_type_label' => $card->discount_type
                ? ($discountTypes[$card->discount_type] ?? ucfirst((string) $card->discount_type))
                : null,
            'value' => $card->value !== null ? (string) $card->value : null,
            'value_label' => $this->formatValueLabel($card),
            'minimum_spend' => (string) $card->minimum_spend,
            'minimum_spend_label' => Currency::format($card->minimum_spend),
            'product_ids' => $productIds,
            'products_label' => $this->formatProductsLabel($productIds, $productNamesById),
            'valid_until' => $card->valid_until?->format('Y-m-d'),
            'valid_until_label' => $card->valid_until?->format('d M Y') ?? 'No expiry',
            'is_active' => $card->is_active,
            'status_label' => $card->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $card->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $card->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $card) ?? false,
            'can_delete' => $user?->can('delete', $card) ?? false,
            'edit_url' => $user?->can('update', $card)
                ? route('tenant.ecommerce.cards.edit', [
                    'type' => $card->card_type,
                    'card' => $card,
                ])
                : null,
            'delete_url' => $user?->can('delete', $card)
                ? route('tenant.ecommerce.cards.destroy', [
                    'type' => $card->card_type,
                    'card' => $card,
                ])
                : null,
        ];
    }

    private function formatValueLabel(Card $card): string
    {
        if ($card->value === null) {
            return '—';
        }

        if ($card->card_type === Card::TYPE_DISCOUNT && $card->discount_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $card->value, 2, '.', ''), '0'), '.').'%';
        }

        if ($card->card_type === Card::TYPE_REWARD) {
            return rtrim(rtrim(number_format((float) $card->value, 2, '.', ''), '0'), '.').' PTS';
        }

        return Currency::format($card->value);
    }

    /**
     * @param  list<int>  $productIds
     * @param  array<int, string>  $productNamesById
     */
    private function formatProductsLabel(array $productIds, array $productNamesById): string
    {
        if ($productIds === []) {
            return 'All products';
        }

        $names = collect($productIds)
            ->map(fn (int $id) => $productNamesById[$id] ?? null)
            ->filter()
            ->values()
            ->all();

        return $names === [] ? 'All products' : implode(', ', $names);
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, string>
     */
    private function productNamesById(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter($productIds)));

        if ($productIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->pluck('name', 'id')
            ->all();
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
