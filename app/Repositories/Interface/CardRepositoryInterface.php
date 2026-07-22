<?php

namespace App\Repositories\Interface;

use App\Models\Card;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

interface CardRepositoryInterface
{
    public function index(string $cardType): View;

    public function employeeIndex(string $cardType): View;

    public function store(
        array $data,
        ?Card $card = null,
        ?Authenticatable $user = null,
        bool $includeData = true
    ): array;

    public function destroy(Card $card): array;

    public function getCardsListing(array $filters, ?Authenticatable $user = null): array;

    public function getCardFormData(Card $card, ?Authenticatable $user = null): array;

    public function renderEmployeeCardHtml(Card $card, string $organizationName = 'Shop'): string;
}
