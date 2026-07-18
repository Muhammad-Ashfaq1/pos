<?php

namespace App\Repositories\Interface;

use App\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;

interface OrderRepositoryInterface
{
    public function listing(array $filters = []): array;

    public function details(Order $order): array;

    public function editDraft(Order $order): array;

    public function store(array $data, ?Authenticatable $user = null): array;

    public function addPayment(Order $order, array $paymentData, ?Authenticatable $user = null): array;

    public function returnsListing(array $filters = [], int $returnDays = 30): array;

    public function returnsHistory(array $filters = []): array;

    public function processReturn(Order $order, array $data, ?Authenticatable $user = null): array;
}
