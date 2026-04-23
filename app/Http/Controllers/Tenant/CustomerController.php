<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customers\SaveCustomerRequest;
use App\Models\Customer;
use App\Repositories\Interface\CustomerRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $repo
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', Rule::in(array_keys(Customer::typeOptions()))],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'visits_high_low', 'value_high_low'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getCustomersListing($validated, $request->user())
        );
    }

    public function edit(Customer $customer, Request $request): JsonResponse
    {
        $this->authorize('update', $customer);

        return response()->json([
            'data' => $this->repo->getCustomerFormData($customer, $request->user()),
        ]);
    }

    public function save(SaveCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $customer = isset($validated['id'])
            ? Customer::query()->findOrFail($validated['id'])
            : null;

        if ($customer) {
            $this->authorize('update', $customer);
        } else {
            $this->authorize('create', Customer::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $customer,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $result = $this->repo->destroy($customer);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
