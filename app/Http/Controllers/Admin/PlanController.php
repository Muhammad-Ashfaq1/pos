<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePlanRequest;
use App\Models\Plan;
use App\Repositories\PlansRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlansRepository $repo,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'duration'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json($this->repo->getListing($validated));
    }

    public function save(SavePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $plan = isset($validated['id']) ? Plan::query()->findOrFail($validated['id']) : null;

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $plan,
        );

        return response()->json($result, ($result['success'] ?? true) ? 200 : 422);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $result = $this->repo->destroy($plan);

        return response()->json($result, ($result['success'] ?? true) ? 200 : 422);
    }
}
