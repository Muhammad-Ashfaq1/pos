<?php

namespace App\Models;

/**
 * @deprecated Use Card with a card_type scope instead.
 */
class DiscountCard extends Card
{
    protected $table = 'cards';

    protected static function booted(): void
    {
        static::addGlobalScope('discount_card', fn ($query) => $query->where('card_type', Card::TYPE_DISCOUNT));
        static::creating(fn (self $card) => $card->card_type = Card::TYPE_DISCOUNT);
    }
}
