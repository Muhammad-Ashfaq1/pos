<?php

namespace App\Repositories\Interface;

use App\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface ProductRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Product $product = null, ?Authenticatable $user = null, array $images = []): array;

    public function destroy(Product $product): array;

    public function getProductsListing(array $filters, ?Authenticatable $user = null): array;

    public function getProductFormData(Product $product, ?Authenticatable $user = null): array;
}
