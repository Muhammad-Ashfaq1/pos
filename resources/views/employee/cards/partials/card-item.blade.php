@php
    /** @var \App\Models\Card $card */
    $config = \App\Models\Card::metaFor($card->card_type);
    $productsById = $productsById ?? collect();
    $organizationName = $organizationName ?? 'Shop';
@endphp

@if ($card->card_type === \App\Models\Card::TYPE_GIFT)
    <x-gift-card
        :amount="$card->value"
        :status="data_get($card->details, 'payment_status', 'unpaid')"
        :card-label="$card->name"
        :unique-id="$card->id"
        :expires-at="$card->valid_until"
        :organization-name="$organizationName"
    />
@elseif ($card->card_type === \App\Models\Card::TYPE_REWARD)
    <x-reward-card
        :name="$card->name"
        :value="$card->value"
        :discount-type="$card->discount_type"
        :minimum-spend="$card->minimum_spend"
        :valid-until="$card->valid_until"
        :unique-id="$card->id"
        :product-ids="$card->productIds()"
        :collected="in_array(strtolower((string) data_get($card->details, 'status', '')), ['collected', 'redeemed'], true)
            || filter_var(data_get($card->details, 'collected'), FILTER_VALIDATE_BOOLEAN)"
    />
@else
    <article class="employee-loyalty-card">
        <div class="employee-loyalty-card-top">
            <div class="employee-loyalty-card-title">
                <span class="employee-loyalty-card-icon">
                    <i class="ti {{ $config['icon'] ?? 'tabler-ticket' }}"></i>
                </span>
                <span class="employee-order-number">{{ $card->name }}</span>
            </div>
            <span class="badge bg-label-primary">
                {{ ucfirst((string) $card->discount_type) }}
            </span>
        </div>

        <div class="employee-loyalty-card-value">
            {{ $card->discount_type === 'percentage'
                ? rtrim(rtrim(number_format((float) $card->value, 2, '.', ''), '0'), '.') . '%'
                : \App\Support\Currency::format((float) $card->value) }}
        </div>

        <div class="employee-loyalty-card-meta">
            <span>Min. spend {{ \App\Support\Currency::format((float) $card->minimum_spend) }}</span>
            @php
                $cardProductNames = collect($card->productIds())
                    ->map(fn ($id) => $productsById->get($id)?->name)
                    ->filter()
                    ->values();
            @endphp
            <span>
                Product{{ $cardProductNames->count() === 1 ? '' : 's' }}:
                {{ $cardProductNames->isEmpty() ? 'All products' : $cardProductNames->implode(', ') }}
            </span>
            <span>
                {{ $card->valid_until
                    ? 'Valid until '.$card->valid_until->format('M d, Y')
                    : 'No expiry date' }}
            </span>
        </div>
    </article>
@endif
