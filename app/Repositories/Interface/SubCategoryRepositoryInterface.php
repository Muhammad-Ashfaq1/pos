<?php

namespace App\Repositories\Interface;

use App\Models\SubCategory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface SubCategoryRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?SubCategory $subCategory = null, ?Authenticatable $user = null): array;

    public function destroy(SubCategory $subCategory): array;

    public function getSubCategoriesListing(array $filters, ?Authenticatable $user = null): array;
}
