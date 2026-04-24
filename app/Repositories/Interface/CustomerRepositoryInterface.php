<?php

namespace App\Repositories\Interface;

use App\Models\Customer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface CustomerRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Customer $customer = null, ?Authenticatable $user = null): array;

    public function destroy(Customer $customer): array;

    public function getCustomersListing(array $filters, ?Authenticatable $user = null): array;

    public function getCustomerFormData(Customer $customer, ?Authenticatable $user = null): array;
}
