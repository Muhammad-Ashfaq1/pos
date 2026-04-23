<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Services\SaveServiceRequest;
use App\Models\Service;
use App\Repositories\Interface\ServiceRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceRepositoryInterface $repo
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Service::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Service::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id())
                ),
            ],
            'requires_technician' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'category', 'price_low_high', 'duration_low_high'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getServicesListing($validated, $request->user())
        );
    }

    public function edit(Service $service, Request $request): JsonResponse
    {
        $this->authorize('update', $service);

        return response()->json([
            'data' => $this->repo->getServiceFormData($service, $request->user()),
        ]);
    }

    public function save(SaveServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $service = isset($validated['id'])
            ? Service::query()->findOrFail($validated['id'])
            : null;

        if ($service) {
            $this->authorize('update', $service);
        } else {
            $this->authorize('create', Service::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $service,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $result = $this->repo->destroy($service);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
