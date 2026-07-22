<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class CardPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $this->canViewCards($user);
    }

    public function view(User $user, Card $card): bool
    {
        return $this->hasTenantContext()
            && $this->canViewCards($user)
            && (int) $user->tenant_id === (int) $card->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('cards.create') || $user->can('cards.manage'));
    }

    public function update(User $user, Card $card): bool
    {
        return $this->hasTenantContext()
            && ($user->can('cards.update') || $user->can('cards.manage'))
            && (int) $user->tenant_id === (int) $card->tenant_id;
    }

    public function delete(User $user, Card $card): bool
    {
        return $this->hasTenantContext()
            && ($user->can('cards.delete') || $user->can('cards.manage'))
            && (int) $user->tenant_id === (int) $card->tenant_id;
    }

    private function canViewCards(User $user): bool
    {
        return $user->can('cards.view')
            || $user->can('cards.create')
            || $user->can('cards.manage');
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
