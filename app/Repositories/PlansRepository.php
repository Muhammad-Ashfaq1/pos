<?php

namespace App\Repositories;

use App\Enums\PlanDuration;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PlansRepository
{
    public function index(): View
    {
        $totalPlans = Plan::query()->count();
        $activePlans = Plan::query()->where('is_active', true)->count();
        $inactivePlans = $totalPlans - $activePlans;
        $subscribedShops = \App\Models\Tenant::query()->whereNotNull('plan_id')->count();

        return view('admin.plans.index', [
            'listingUrl' => route('admin.plans.listing'),
            'durations' => PlanDuration::cases(),
            'stats' => [
                'total' => $totalPlans,
                'active' => $activePlans,
                'inactive' => $inactivePlans,
                'subscribed' => $subscribedShops,
            ],
        ]);
    }

    public function store(array $data, ?Plan $plan = null): array
    {
        $isUpdate = $plan !== null;

        if ($isUpdate) {
            $plan->fill($data)->save();
        } else {
            $plan = Plan::create($data);
        }

        return [
            'success' => true,
            'message' => $isUpdate ? 'Plan updated successfully.' : 'Plan created successfully.',
            'data' => $this->transformPlan($plan),
        ];
    }

    public function destroy(Plan $plan): array
    {
        if ($plan->tenants()->exists()) {
            return [
                'success' => false,
                'message' => 'This plan is assigned to one or more shops and cannot be deleted.',
            ];
        }

        $plan->delete();

        return [
            'success' => true,
            'message' => 'Plan deleted successfully.',
        ];
    }

    public function getListing(array $filters): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Plan::query();
        $filteredQuery = Plan::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => (clone $baseQuery)->count(),
            'recordsFiltered' => (clone $filteredQuery)->count(),
            'data' => $filteredQuery
                ->skip($start)
                ->take($length)
                ->get()
                ->map(fn (Plan $plan) => $this->transformPlan($plan))
                ->values()
                ->all(),
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc');
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex.'.data')
            : null;

        $sortableColumns = [
            'name' => 'name',
            'duration_days' => 'duration_days',
            'price' => 'price',
            'created_at' => 'created_at',
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $query->orderBy($sortableColumns[$orderColumn], $orderDirection === 'desc' ? 'desc' : 'asc')
                ->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'duration' => $query->orderBy('duration_days')->orderBy('id'),
            default => $query->latest('id'),
        };
    }

    private function transformPlan(Plan $plan): array
    {
        $duration = $plan->duration_type instanceof PlanDuration
            ? $plan->duration_type
            : PlanDuration::tryFromDays((int) $plan->duration_days);

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'duration_type' => $duration->value,
            'duration_label' => $duration->label(),
            'duration_days' => $duration->days(),
            'price' => $plan->price !== null ? number_format((float) $plan->price, 2, '.', '') : null,
            'is_active' => $plan->is_active,
            'delete_url' => route('admin.plans.destroy', $plan),
        ];
    }
}
