<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Repositories\Interface\CustomerRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class CustomersRepository implements CustomerRepositoryInterface
{
    public function index(): View
    {
        return view('tenant.ecommerce.customers.index', [
            'listingUrl' => route('tenant.ecommerce.customers.listing'),
            'editUrlTemplate' => route('tenant.ecommerce.customers.edit', ['customer' => '__CUSTOMER__']),
            'customerTypes' => Customer::typeOptions(),
            'vehicleIndexUrlTemplate' => route('tenant.ecommerce.vehicles.index', ['customer_id' => '__CUSTOMER__']),
        ]);
    }

    public function store(array $data, ?Customer $customer = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $customer !== null;
        $userId = $user?->getAuthIdentifier();
        $payload = $this->buildPayload($data);

        if ($isUpdate) {
            $customer->fill($payload);
            $customer->forceFill(['updated_by' => $userId])->save();
        } else {
            $customer = new Customer($payload);
            $customer->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $customer->save();
        }

        $customer->loadMissing('defaultVehicle:id,customer_id,plate_number');
        $customer->loadCount('vehicles');

        return [
            'success' => true,
            'message' => $isUpdate ? 'Customer updated successfully.' : 'Customer created successfully.',
            'data' => $this->transformCustomer($customer, $user),
        ];
    }

    public function destroy(Customer $customer): array
    {
        $customer->delete();

        return [
            'success' => true,
            'message' => 'Customer deleted successfully.',
        ];
    }

    public function getCustomersListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $customerType = $filters['customer_type'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Customer::query();
        $filteredQuery = Customer::query()
            ->with('defaultVehicle:id,customer_id,plate_number')
            ->withCount('vehicles')
            ->search($search)
            ->when($customerType !== '', function (Builder $query) use ($customerType): void {
                $query->where('customer_type', $customerType);
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $customers = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformCustomers($customers, $user),
        ];
    }

    public function getCustomerFormData(Customer $customer, ?Authenticatable $user = null): array
    {
        $customer->loadMissing('defaultVehicle:id,customer_id,plate_number');
        $customer->loadCount('vehicles');

        return $this->transformCustomer($customer, $user);
    }

    private function buildPayload(array $data): array
    {
        return [
            'customer_type' => $data['customer_type'],
            'name' => $data['name'],
            'phone' => $this->normalizeNullableString($data['phone'] ?? null),
            'email' => $this->normalizeNullableString($data['email'] ?? null),
            'address' => $this->normalizeNullableString($data['address'] ?? null),
            'notes' => $this->normalizeNullableString($data['notes'] ?? null),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'total_visits' => (int) ($data['total_visits'] ?? 0),
            'lifetime_value' => $this->normalizeMoney($data['lifetime_value'] ?? 0),
            'loyalty_points_balance' => (int) ($data['loyalty_points_balance'] ?? 0),
            'credit_balance' => $this->normalizeMoney($data['credit_balance'] ?? 0),
            'last_visit_at' => $data['last_visit_at'] ?? null,
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex.'.data')
            : null;

        $sortableColumns = [
            'customer_type_label' => fn (Builder $builder, string $direction) => $builder->orderBy('customer_type', $direction),
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'phone' => fn (Builder $builder, string $direction) => $builder->orderBy('phone', $direction),
            'email' => fn (Builder $builder, string $direction) => $builder->orderBy('email', $direction),
            'total_visits' => fn (Builder $builder, string $direction) => $builder->orderBy('total_visits', $direction),
            'lifetime_value' => fn (Builder $builder, string $direction) => $builder->orderBy('lifetime_value', $direction),
            'vehicles_count' => fn (Builder $builder, string $direction) => $builder->orderBy('vehicles_count', $direction),
            'last_visit_at' => fn (Builder $builder, string $direction) => $builder->orderBy('last_visit_at', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'visits_high_low' => $query->orderByDesc('total_visits')->orderBy('name')->orderBy('id'),
            'value_high_low' => $query->orderByDesc('lifetime_value')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformCustomers(Collection $customers, ?Authenticatable $user = null): array
    {
        return $customers
            ->map(fn (Customer $customer) => $this->transformCustomer($customer, $user))
            ->all();
    }

    private function transformCustomer(Customer $customer, ?Authenticatable $user = null): array
    {
        $typeOptions = Customer::typeOptions();

        return [
            'id' => $customer->id,
            'customer_type' => $customer->customer_type,
            'customer_type_label' => $typeOptions[$customer->customer_type] ?? ucfirst((string) $customer->customer_type),
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'notes' => $customer->notes,
            'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
            'date_of_birth_label' => $customer->date_of_birth?->format('d M Y'),
            'total_visits' => $customer->total_visits,
            'lifetime_value' => (string) $customer->lifetime_value,
            'loyalty_points_balance' => $customer->loyalty_points_balance,
            'credit_balance' => (string) $customer->credit_balance,
            'last_visit_at' => $customer->last_visit_at?->format('Y-m-d H:i:s'),
            'last_visit_at_form' => $customer->last_visit_at?->format('Y-m-d\TH:i'),
            'last_visit_at_label' => $customer->last_visit_at?->format('d M Y h:i A'),
            'vehicles_count' => $customer->vehicles_count ?? ($customer->relationLoaded('vehicles') ? $customer->vehicles->count() : 0),
            'default_vehicle_plate' => $customer->defaultVehicle?->plate_number,
            'created_at' => $customer->created_at?->format('d M Y'),
            'vehicles_index_url' => ($user?->can('viewAny', Vehicle::class) ?? false)
                ? route('tenant.ecommerce.vehicles.index', ['customer_id' => $customer->id])
                : null,
            'can_update' => $user?->can('update', $customer) ?? false,
            'can_delete' => $user?->can('delete', $customer) ?? false,
            'edit_url' => $user?->can('update', $customer)
                ? route('tenant.ecommerce.customers.edit', $customer)
                : null,
            'delete_url' => $user?->can('delete', $customer)
                ? route('tenant.ecommerce.customers.destroy', $customer)
                : null,
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
