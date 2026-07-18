<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CardController extends Controller
{
    public function index(): View
    {
        $cards = Card::query()
            ->with('product:id,name')
            ->latest()
            ->get();

        $linkedProductIds = $cards
            ->flatMap(fn (Card $card) => $card->productIds())
            ->unique()
            ->values()
            ->all();

        return view('employee.cards.index', [
            'cardsByType' => $cards->groupBy('card_type'),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'productsById' => $linkedProductIds === []
                ? collect()
                : Product::query()
                    ->whereIn('id', $linkedProductIds)
                    ->get(['id', 'name'])
                    ->keyBy('id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_type' => ['required', Rule::in(array_keys(Card::typeOptions()))],
            'name' => ['required', 'string', 'max:150'],
            'discount_type' => [
                Rule::requiredIf($request->input('card_type') === Card::TYPE_DISCOUNT),
                'nullable',
                Rule::in(['percentage', 'fixed']),
            ],
            'value' => ['required', 'numeric', 'gt:0'],
            'minimum_spend' => ['required', 'numeric', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)
                ),
            ],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($validated['card_type'] === Card::TYPE_DISCOUNT && $validated['discount_type'] === 'percentage' && (float) $validated['value'] > 100) {
            return back()
                ->withErrors(['value' => 'The percentage discount cannot be greater than 100%.'])
                ->withInput();
        }

        $productIds = collect($validated['product_ids'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        unset($validated['product_ids']);

        $validated['product_id'] = $productIds[0] ?? null;
        $validated['details'] = $productIds === []
            ? null
            : ['product_ids' => $productIds];
        $validated['created_by'] = auth()->id();

        Card::query()->create($validated);

        return to_route('employee.cards.index', ['module' => $validated['card_type']])
            ->with('success', Card::typeOptions()[$validated['card_type']].' created successfully.');
    }

    public function update(Request $request, Card $card): RedirectResponse
    {
        $validated = $request->validate([
            'card_type' => ['required', Rule::in(array_keys(Card::typeOptions()))],
            'name' => ['required', 'string', 'max:150'],
            'discount_type' => [
                Rule::requiredIf($request->input('card_type') === Card::TYPE_DISCOUNT),
                'nullable',
                Rule::in(['percentage', 'fixed']),
            ],
            'value' => ['required', 'numeric', 'gt:0'],
            'minimum_spend' => ['required', 'numeric', 'min:0'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)
                ),
            ],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($validated['card_type'] !== $card->card_type) {
            return back()
                ->withErrors(['card_type' => 'Card type cannot be changed.'])
                ->withInput();
        }

        if ($validated['card_type'] === Card::TYPE_DISCOUNT && $validated['discount_type'] === 'percentage' && (float) $validated['value'] > 100) {
            return back()
                ->withErrors(['value' => 'The percentage discount cannot be greater than 100%.'])
                ->withInput();
        }

        $productIds = collect($validated['product_ids'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        unset($validated['product_ids']);

        $details = is_array($card->details) ? $card->details : [];
        if ($productIds === []) {
            unset($details['product_ids']);
        } else {
            $details['product_ids'] = $productIds;
        }

        $validated['product_id'] = $productIds[0] ?? null;
        $validated['details'] = $details === [] ? null : $details;

        // Preserve discount_type for non-discount cards when the form does not send it.
        if ($validated['card_type'] !== Card::TYPE_DISCOUNT && ! array_key_exists('discount_type', $request->all())) {
            unset($validated['discount_type']);
        }

        $card->update($validated);

        return to_route('employee.cards.index', ['module' => $card->card_type])
            ->with('success', Card::typeOptions()[$card->card_type].' updated successfully.');
    }
}
