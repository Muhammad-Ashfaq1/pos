@props([
    'name' => '',
    'value' => 0,
    'discountType' => null,
    'minimumSpend' => 0,
    'validUntil' => null,
    'uniqueId' => '',
    'collected' => false,
    'editUrl' => null,
    'editable' => false,
    'productIds' => [],
])

@php
    $numericValue = (float) $value;
    $type = $discountType ? strtolower((string) $discountType) : null;

    if ($type === 'percentage') {
        $discountLabel = rtrim(rtrim(number_format($numericValue, 2, '.', ''), '0'), '.') . '% OFF';
    } elseif ($type === 'fixed') {
        $discountLabel = \App\Support\Currency::symbol() . rtrim(rtrim(number_format($numericValue, 2, '.', ''), '0'), '.') . ' OFF';
    } else {
        $discountLabel = number_format($numericValue, fmod($numericValue, 1.0) === 0.0 ? 0 : 2) . ' PTS';
    }

    $validDateText = $validUntil instanceof \Illuminate\Support\Carbon
        ? $validUntil->format('d-m-Y')
        : ($validUntil ? \Illuminate\Support\Carbon::parse($validUntil)->format('d-m-Y') : '—');

    $displayId = is_numeric($uniqueId)
        ? str_pad((string) (int) $uniqueId, 6, '0', STR_PAD_LEFT)
        : (string) $uniqueId;

    $isCollected = filter_var($collected, FILTER_VALIDATE_BOOLEAN);

    $editPayload = [
        'id' => is_numeric($uniqueId) ? (int) $uniqueId : $uniqueId,
        'name' => (string) $name,
        'value' => $numericValue,
        'discount_type' => $discountType,
        'minimum_spend' => (float) $minimumSpend,
        'valid_until' => $validUntil instanceof \Illuminate\Support\Carbon
            ? $validUntil->format('Y-m-d')
            : ($validUntil ? \Illuminate\Support\Carbon::parse($validUntil)->format('Y-m-d') : ''),
        'product_ids' => array_values(array_map('intval', (array) $productIds)),
    ];
@endphp

<article {{ $attributes->class(['reward-card']) }}>
    <div class="reward-card__header">
        <h6 class="reward-card__title">Reward Card - {{ $name }}</h6>
        @if ($editable)
            <button
                type="button"
                class="reward-card__edit"
                data-edit-reward-card
                data-card='@json($editPayload)'
                aria-label="Edit {{ $name }}"
            >
                <i class="ti tabler-pencil"></i>
            </button>
        @elseif ($editUrl)
            <a href="{{ $editUrl }}" class="reward-card__edit" aria-label="Edit {{ $name }}">
                <i class="ti tabler-pencil"></i>
            </a>
        @else
            <span class="reward-card__edit" aria-hidden="true">
                <i class="ti tabler-pencil"></i>
            </span>
        @endif
    </div>

    <div class="reward-card__body">
        <div class="reward-card__left">
            <div class="reward-card__discount">{{ $discountLabel }}</div>
            <div class="reward-card__min-label">Min. Spend Limit</div>
            <div class="reward-card__min-value">{{ \App\Support\Currency::format((float) $minimumSpend) }}</div>
        </div>

        <div class="reward-card__right">
            <div class="reward-card__valid-date">Valid Date: {{ $validDateText }}</div>

            @if ($isCollected)
                <span class="reward-card__watermark" aria-hidden="true">Collected</span>
            @endif

            @if ($displayId !== '')
                <div class="reward-card__id-badge">{{ $displayId }}</div>
            @endif
        </div>
    </div>
</article>
