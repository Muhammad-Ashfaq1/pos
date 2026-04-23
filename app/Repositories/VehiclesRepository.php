<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Repositories\Interface\VehicleRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehiclesRepository implements VehicleRepositoryInterface
{
    public function index(): View
    {
        return view('tenant.ecommerce.vehicles.index', [
            'listingUrl' => route('tenant.ecommerce.vehicles.listing'),
            'editUrlTemplate' => route('tenant.ecommerce.vehicles.edit', ['vehicle' => '__VEHICLE__']),
            'customersDropdownUrl' => route('tenant.ecommerce.dropdowns.customers'),
            'vehiclesDropdownUrl' => route('tenant.ecommerce.dropdowns.vehicles'),
        ]);
    }

    public function store(array $data, ?Vehicle $vehicle = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $vehicle !== null;
        $userId = $user?->getAuthIdentifier();

        $vehicle = DB::transaction(function () use ($data, $vehicle, $userId, $isUpdate): Vehicle {
            $payload = $this->buildPayload($data);

            if ($isUpdate) {
                $vehicle->fill($payload);
                $vehicle->forceFill(['updated_by' => $userId])->save();
            } else {
                $vehicle = new Vehicle($payload);
                $vehicle->forceFill([
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $vehicle->save();
            }

            if ($vehicle->is_default) {
                Vehicle::query()
                    ->where('customer_id', $vehicle->customer_id)
                    ->whereKeyNot($vehicle->id)
                    ->update(['is_default' => false]);
            }

            return $vehicle->fresh('customer:id,name,phone,email');
        });

        return [
            'success' => true,
            'message' => $isUpdate ? 'Vehicle updated successfully.' : 'Vehicle created successfully.',
            'data' => $this->transformVehicle($vehicle, $user),
        ];
    }

    public function destroy(Vehicle $vehicle): array
    {
        $vehicle->delete();

        return [
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
        ];
    }

    public function getVehiclesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $customerId = $filters['customer_id'] ?? null;
        $isDefault = $filters['is_default'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Vehicle::query();
        $filteredQuery = Vehicle::query()
            ->with('customer:id,name,phone,email')
            ->search($search)
            ->when($customerId, function (Builder $query) use ($customerId): void {
                $query->where('customer_id', $customerId);
            })
            ->when($isDefault !== '', function (Builder $query) use ($isDefault): void {
                $query->where('is_default', $isDefault === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $vehicles = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformVehicles($vehicles, $user),
        ];
    }

    public function getVehicleFormData(Vehicle $vehicle, ?Authenticatable $user = null): array
    {
        $vehicle->loadMissing('customer:id,name,phone,email');

        return $this->transformVehicle($vehicle, $user);
    }

    private function buildPayload(array $data): array
    {
        return [
            'customer_id' => (int) $data['customer_id'],
            'plate_number' => strtoupper(trim((string) $data['plate_number'])),
            'registration_number' => $this->normalizeNullableUpperString($data['registration_number'] ?? null),
            'make' => $this->normalizeNullableString($data['make'] ?? null),
            'model' => $this->normalizeNullableString($data['model'] ?? null),
            'year' => $data['year'] ?: null,
            'color' => $this->normalizeNullableString($data['color'] ?? null),
            'engine_type' => $this->normalizeNullableString($data['engine_type'] ?? null),
            'odometer' => $data['odometer'] !== null && $data['odometer'] !== ''
                ? number_format((float) $data['odometer'], 1, '.', '')
                : null,
            'notes' => $this->normalizeNullableString($data['notes'] ?? null),
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
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
            'customer_name' => fn (Builder $builder, string $direction) => $builder->orderBy(
                Customer::query()
                    ->select('name')
                    ->whereColumn('customers.id', 'vehicles.customer_id')
                    ->limit(1),
                $direction
            ),
            'plate_number' => fn (Builder $builder, string $direction) => $builder->orderBy('plate_number', $direction),
            'registration_number' => fn (Builder $builder, string $direction) => $builder->orderBy('registration_number', $direction),
            'make_model' => fn (Builder $builder, string $direction) => $builder->orderBy('make', $direction)->orderBy('model', $direction),
            'year' => fn (Builder $builder, string $direction) => $builder->orderBy('year', $direction),
            'odometer' => fn (Builder $builder, string $direction) => $builder->orderBy('odometer', $direction),
            'is_default' => fn (Builder $builder, string $direction) => $builder->orderBy('is_default', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'customer' => $sortableColumns['customer_name']($query, 'asc'),
            'plate' => $query->orderBy('plate_number')->orderBy('id'),
            'year_desc' => $query->orderByDesc('year')->orderBy('plate_number')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformVehicles(Collection $vehicles, ?Authenticatable $user = null): array
    {
        return $vehicles
            ->map(fn (Vehicle $vehicle) => $this->transformVehicle($vehicle, $user))
            ->all();
    }

    private function transformVehicle(Vehicle $vehicle, ?Authenticatable $user = null): array
    {
        $vehicleLabel = trim(collect([$vehicle->make, $vehicle->model, $vehicle->year])->filter()->implode(' '));

        return [
            'id' => $vehicle->id,
            'customer_id' => $vehicle->customer_id,
            'customer_name' => $vehicle->customer?->name,
            'customer_phone' => $vehicle->customer?->phone,
            'customer_email' => $vehicle->customer?->email,
            'plate_number' => $vehicle->plate_number,
            'registration_number' => $vehicle->registration_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'color' => $vehicle->color,
            'engine_type' => $vehicle->engine_type,
            'odometer' => $vehicle->odometer !== null ? (string) $vehicle->odometer : null,
            'notes' => $vehicle->notes,
            'is_default' => $vehicle->is_default,
            'default_label' => $vehicle->is_default ? 'Default' : 'Standard',
            'default_badge_class' => $vehicle->is_default ? 'bg-label-primary' : 'bg-label-secondary',
            'vehicle_label' => $vehicleLabel !== '' ? $vehicleLabel : 'Vehicle',
            'created_at' => $vehicle->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $vehicle) ?? false,
            'can_delete' => $user?->can('delete', $vehicle) ?? false,
            'edit_url' => $user?->can('update', $vehicle)
                ? route('tenant.ecommerce.vehicles.edit', $vehicle)
                : null,
            'delete_url' => $user?->can('delete', $vehicle)
                ? route('tenant.ecommerce.vehicles.destroy', $vehicle)
                : null,
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeNullableUpperString(mixed $value): ?string
    {
        $value = $this->normalizeNullableString($value);

        return $value !== null ? strtoupper($value) : null;
    }
}
