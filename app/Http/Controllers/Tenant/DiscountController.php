<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Discounts\SaveDiscountRequest;
use App\Models\Discount;
use App\Repositories\Interface\DiscountRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function __construct(
        private readonly DiscountRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Discount::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Discount::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'discount_type' => ['nullable', Rule::in(array_keys(Discount::typeOptions()))],
            'applies_to' => ['nullable', Rule::in(array_keys(Discount::appliesToOptions()))],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'value_high_low', 'starts_at'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getDiscountsListing($validated, $request->user())
        );
    }

    public function edit(Discount $discount, Request $request): JsonResponse
    {
        $this->authorize('update', $discount);

        return response()->json([
            'data' => $this->repo->getDiscountFormData($discount, $request->user()),
        ]);
    }

    public function save(SaveDiscountRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $discount = isset($validated['id'])
            ? Discount::query()->findOrFail($validated['id'])
            : null;

        if ($discount) {
            $this->authorize('update', $discount);
        } else {
            $this->authorize('create', Discount::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $discount,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Discount $discount): JsonResponse
    {
        $this->authorize('delete', $discount);

        $result = $this->repo->destroy($discount);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
