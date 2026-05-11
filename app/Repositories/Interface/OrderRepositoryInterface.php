<?php

namespace App\Repositories\Interface;

use App\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;

interface OrderRepositoryInterface
{
    public function listing(array $filters = []): array;

    public function details(Order $order): array;

    public function store(array $data, ?Authenticatable $user = null): array;
}
