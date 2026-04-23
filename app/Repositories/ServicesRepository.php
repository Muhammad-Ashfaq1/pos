<?php

namespace App\Repositories;

use App\Actions\Tenant\Services\SyncServiceProductsAction;
use App\Models\Category;
use App\Models\Service;
use App\Repositories\Interface\ServiceRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServicesRepository implements ServiceRepositoryInterface
{
    public function __construct(
        private readonly SyncServiceProductsAction $syncServiceProductsAction
    ) {
    }

    public function index(): View
    {
        return view('tenant.ecommerce.services.index', [
            'listingUrl' => route('tenant.ecommerce.services.listing'),
            'editUrlTemplate' => route('tenant.ecommerce.services.edit', ['service' => '__SERVICE__']),
            'categoriesDropdownUrl' => route('tenant.ecommerce.dropdowns.categories'),
            'productsDropdownUrl' => route('tenant.ecommerce.dropdowns.products'),
        ]);
    }

    public function store(array $data, ?Service $service = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $service !== null;
        $userId = $user?->getAuthIdentifier();

        $service = DB::transaction(function () use ($data, $service, $isUpdate, $userId): Service {
            $payload = [
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'standard_price' => $this->normalizeMoney($data['standard_price']),
                'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
                'tax_percentage' => $data['tax_percentage'] !== null && $data['tax_percentage'] !== ''
                    ? $this->normalizeMoney($data['tax_percentage'])
                    : null,
                'reminder_interval_days' => $data['reminder_interval_days'] ?? null,
                'mileage_interval' => $data['mileage_interval'] ?? null,
                'is_active' => (bool) $data['is_active'],
                'requires_technician' => (bool) $data['requires_technician'],
            ];

            if ($isUpdate) {
                $service->fill($payload);
                $service->forceFill(['updated_by' => $userId])->save();
            } else {
                $service = new Service($payload);
                $service->forceFill([
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $service->save();
            }

            $this->syncServiceProductsAction->execute($service, $data['mappings'] ?? []);

            return $service->fresh([
                'category:id,name',
                'serviceProducts.product:id,name,sku,unit',
            ]);
        });

        return [
            'success' => true,
            'message' => $isUpdate ? 'Service updated successfully.' : 'Service created successfully.',
            'data' => $this->transformService($service, $user),
        ];
    }

    public function destroy(Service $service): array
    {
        $service->delete();

        return [
            'success' => true,
            'message' => 'Service deleted successfully.',
        ];
    }

    public function getServicesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';
        $categoryId = $filters['category_id'] ?? null;
        $requiresTechnician = $filters['requires_technician'] ?? '';

        $baseQuery = Service::query();
        $filteredQuery = Service::query()
            ->with('category:id,name')
            ->withCount('serviceProducts')
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            })
            ->when($categoryId, function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($requiresTechnician !== '', function (Builder $query) use ($requiresTechnician): void {
                $query->where('requires_technician', $requiresTechnician === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $services = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformServices($services, $user),
        ];
    }

    public function getServiceFormData(Service $service, ?Authenticatable $user = null): array
    {
        $service->loadMissing([
            'category:id,name',
            'serviceProducts.product:id,name,sku,unit',
        ]);

        return $this->transformService($service, $user);
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex . '.data')
            : null;

        $sortableColumns = [
            'category_name' => fn (Builder $builder, string $direction) => $builder->orderBy(
                Category::query()
                    ->select('name')
                    ->whereColumn('categories.id', 'services.category_id')
                    ->limit(1),
                $direction
            ),
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'code' => fn (Builder $builder, string $direction) => $builder->orderBy('code', $direction),
            'standard_price' => fn (Builder $builder, string $direction) => $builder->orderBy('standard_price', $direction),
            'estimated_duration_minutes' => fn (Builder $builder, string $direction) => $builder->orderBy('estimated_duration_minutes', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'category' => $sortableColumns['category_name']($query, 'asc'),
            'name' => $query->orderBy('name')->orderBy('id'),
            'price_low_high' => $query->orderBy('standard_price')->orderBy('name')->orderBy('id'),
            'duration_low_high' => $query->orderBy('estimated_duration_minutes')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformServices(Collection $services, ?Authenticatable $user = null): array
    {
        return $services
            ->map(fn (Service $service) => $this->transformService($service, $user))
            ->all();
    }

    private function transformService(Service $service, ?Authenticatable $user = null): array
    {
        return [
            'id' => $service->id,
            'category_id' => $service->category_id,
            'category_name' => $service->category?->name,
            'name' => $service->name,
            'code' => $service->code,
            'description' => $service->description,
            'standard_price' => (string) $service->standard_price,
            'estimated_duration_minutes' => $service->estimated_duration_minutes,
            'tax_percentage' => $service->tax_percentage !== null ? (string) $service->tax_percentage : null,
            'reminder_interval_days' => $service->reminder_interval_days,
            'mileage_interval' => $service->mileage_interval,
            'is_active' => $service->is_active,
            'requires_technician' => $service->requires_technician,
            'status_label' => $service->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $service->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'requires_technician_label' => $service->requires_technician ? 'Required' : 'Not Required',
            'requires_technician_badge_class' => $service->requires_technician ? 'bg-label-warning' : 'bg-label-secondary',
            'mapped_products_count' => $service->service_products_count
                ?? ($service->relationLoaded('serviceProducts') ? $service->serviceProducts->count() : 0),
            'mappings' => $service->relationLoaded('serviceProducts')
                ? $service->serviceProducts
                    ->map(fn ($mapping) => [
                        'product_id' => $mapping->product_id,
                        'product_name' => $mapping->product?->name,
                        'product_sku' => $mapping->product?->sku,
                        'product_unit' => $mapping->product?->unit,
                        'quantity' => (string) $mapping->quantity,
                        'unit' => $mapping->unit,
                        'is_required' => $mapping->is_required,
                    ])
                    ->values()
                    ->all()
                : [],
            'created_at' => $service->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $service) ?? false,
            'can_delete' => $user?->can('delete', $service) ?? false,
            'edit_url' => $user?->can('update', $service)
                ? route('tenant.ecommerce.services.edit', $service)
                : null,
            'delete_url' => $user?->can('delete', $service)
                ? route('tenant.ecommerce.services.destroy', $service)
                : null,
        ];
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
