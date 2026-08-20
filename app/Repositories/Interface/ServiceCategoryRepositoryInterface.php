<?php

namespace App\Repositories\Interface;

use App\Models\ServiceCategory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface ServiceCategoryRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?ServiceCategory $serviceCategory = null, ?Authenticatable $user = null): array;

    public function destroy(ServiceCategory $serviceCategory): array;

    public function getServiceCategoriesListing(array $filters, ?Authenticatable $user = null): array;
}
