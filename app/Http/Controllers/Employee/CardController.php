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
        return view('employee.cards.index', [
            'cardsByType' => Card::query()
                ->with('product:id,name')
                ->latest()
                ->get()
                ->groupBy('card_type'),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
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
            'product_id' => [
                'nullable',
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

        $validated['created_by'] = auth()->id();
        Card::query()->create($validated);

        return to_route('employee.cards.index', ['module' => $validated['card_type']])
            ->with('success', Card::typeOptions()[$validated['card_type']].' created successfully.');
    }
}
