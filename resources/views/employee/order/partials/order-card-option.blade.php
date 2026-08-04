@php
    $cardType = $card->card_type;
    $meta = \App\Models\Card::metaFor($cardType);
    $valueSuffix = $cardType === 'reward' ? ' points' : '';
@endphp

<label class="order-card-option" data-card-id="{{ $card->id }}"
    data-card-type="{{ $cardType }}" data-card-name="{{ $card->name }}"
    data-card-value="{{ (float) $card->value }}"
    data-discount-type="{{ $card->discount_type }}"
    data-minimum-spend="{{ (float) $card->minimum_spend }}"
    data-product-id="{{ $card->product_id }}"
    data-product-ids="{{ implode(',', $card->productIds()) }}">
    <input class="form-check-input order-card-radio" type="radio"
        name="selected_{{ $cardType }}_card" value="{{ $card->id }}">
    <span class="order-card-option-icon"><i class="ti {{ $meta['icon'] }}"></i></span>
    <span class="min-w-0 flex-grow-1">
        <span class="d-block fw-bold">{{ $card->name }}</span>
        <span class="d-block text-primary fw-bold">
            @if ($cardType === 'discount')
                {{ $card->discount_type === 'fixed'
                    ? \App\Support\Currency::format((float) $card->value)
                    : rtrim(rtrim(number_format((float) $card->value, 2, '.', ''), '0'), '.').'%' }}
            @elseif ($cardType === 'gift')
                {{ \App\Support\Currency::format((float) $card->value) }}
            @else
                {{ number_format((float) $card->value) }}{{ $valueSuffix }}
            @endif
        </span>
        <small class="text-muted d-block">
            Min. spend {{ \App\Support\Currency::format((float) $card->minimum_spend) }}
            @if ($card->valid_until)
                &middot; Valid through {{ $card->valid_until->format('M d, Y') }}
            @endif
        </small>
    </span>
</label>
