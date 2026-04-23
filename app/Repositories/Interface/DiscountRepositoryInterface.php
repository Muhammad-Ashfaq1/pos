<?php

namespace App\Repositories\Interface;

use App\Models\Discount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface DiscountRepositoryInterface
{
    public function index(): View;

    public function store(array $data, ?Discount $discount = null, ?Authenticatable $user = null): array;

    public function destroy(Discount $discount): array;

    public function getDiscountsListing(array $filters, ?Authenticatable $user = null): array;

    public function getDiscountFormData(Discount $discount, ?Authenticatable $user = null): array;
}
