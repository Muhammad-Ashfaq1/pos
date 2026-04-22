<?php

namespace App\Repositories\Interface;

use App\Models\Category;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface CategoryRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Category $category = null, ?Authenticatable $user = null): array;

    public function destroy(Category $category): array;

    public function getCategoriesListing(array $filters, ?Authenticatable $user = null): array;
}
