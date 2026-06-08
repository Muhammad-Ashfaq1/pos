<?php

namespace App\Repositories\Interface;

use App\Models\ProductType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface ProductTypeRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?ProductType $productType = null, ?Authenticatable $user = null): array;

    public function destroy(ProductType $productType): array;

    public function getProductTypesListing(array $filters, ?Authenticatable $user = null): array;
}
