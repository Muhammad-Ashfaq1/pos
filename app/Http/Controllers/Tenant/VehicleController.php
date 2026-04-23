<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Vehicles\SaveVehicleRequest;
use App\Models\Vehicle;
use App\Repositories\Interface\VehicleRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleRepositoryInterface $repo
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vehicle::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(
                    fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id())
                ),
            ],
            'is_default' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'customer', 'plate', 'year_desc'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getVehiclesListing($validated, $request->user())
        );
    }

    public function edit(Vehicle $vehicle, Request $request): JsonResponse
    {
        $this->authorize('update', $vehicle);

        return response()->json([
            'data' => $this->repo->getVehicleFormData($vehicle, $request->user()),
        ]);
    }

    public function save(SaveVehicleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $vehicle = isset($validated['id'])
            ? Vehicle::query()->findOrFail($validated['id'])
            : null;

        if ($vehicle) {
            $this->authorize('update', $vehicle);
        } else {
            $this->authorize('create', Vehicle::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $vehicle,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        $result = $this->repo->destroy($vehicle);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
