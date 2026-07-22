@props([
    'cardType',
    'products',
    'idPrefix' => 'card',
    'modalId',
    'currencySymbol',
    'discountTypes' => \App\Models\Card::discountTypeOptions(),
    'showStatus' => false,
    'valuePrefix' => null,
    'valueLabel' => null,
    'nameColumn' => 'col-md-6',
    'validUntilRequired' => false,
    'validUntilMin' => null,
    'discountSelect2' => false,
])

@php
    $meta = \App\Models\Card::typeMeta()[$cardType] ?? [];
    $isDiscount = $cardType === \App\Models\Card::TYPE_DISCOUNT;
    $isReward = $cardType === \App\Models\Card::TYPE_REWARD;
    $resolvedValueLabel = $valueLabel ?? ($meta['value_label'] ?? 'Value');
    $valueStep = $meta['value_step'] ?? ($isReward ? '1' : '0.01');
    // Reward cards can be created without a points value.
    $valueRequired = ! $isReward;
    $valueMin = $isReward ? '0' : '0.01';
    $resolvedValuePrefix = $valuePrefix
        ?? (($meta['uses_currency_prefix'] ?? false)
            ? $currencySymbol
            : ($meta['value_prefix'] ?? null));
    // Same as employee: date picker only allows today onward (admin edit may lower min in JS).
    $resolvedValidUntilMin = $validUntilMin ?? now()->toDateString();
@endphp

<div class="row g-3" data-card-fields data-card-type="{{ $cardType }}">
    <div class="{{ $nameColumn }}" data-card-field="name">
        <label class="form-label" for="{{ $idPrefix }}_name">
            Card Name <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            class="form-control"
            id="{{ $idPrefix }}_name"
            name="name"
            maxlength="150"
            required
        >
        <div class="invalid-feedback" data-card-error="name"></div>
    </div>

    @if ($isDiscount)
        <div class="col-md-6" data-card-field="discount_type">
            <label class="form-label" for="{{ $idPrefix }}_discount_type">
                Discount Type <span class="text-danger">*</span>
            </label>
            <div class="position-relative">
                <select
                    class="form-select {{ $discountSelect2 ? 'select2' : '' }}"
                    id="{{ $idPrefix }}_discount_type"
                    name="discount_type"
                    data-card-discount-type
                    data-placeholder="Select a discount type"
                    data-dropdown-parent="#{{ $modalId }}"
                    required
                >
                    @foreach ($discountTypes as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-card-error="discount_type"></div>
            </div>
        </div>
    @endif

    <div class="col-md-6" data-card-field="value">
        <label class="form-label" for="{{ $idPrefix }}_value" data-card-value-label>
            {{ $resolvedValueLabel }}
            @if ($valueRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
        @if ($resolvedValuePrefix)
            <div class="input-group has-validation">
                <span class="input-group-text" data-card-value-prefix>{{ $resolvedValuePrefix }}</span>
                <input
                    type="number"
                    class="form-control"
                    id="{{ $idPrefix }}_value"
                    name="value"
                    min="{{ $valueMin }}"
                    step="{{ $valueStep }}"
                    data-card-value
                    @if ($isDiscount) max="100" @endif
                    @if ($valueRequired) required @endif
                >
                <div class="invalid-feedback" data-card-error="value"></div>
            </div>
        @else
            <input
                type="number"
                class="form-control"
                id="{{ $idPrefix }}_value"
                name="value"
                min="{{ $valueMin }}"
                step="{{ $valueStep }}"
                data-card-value
                @if ($isDiscount) max="100" @endif
                @if ($valueRequired) required @endif
            >
            <div class="invalid-feedback" data-card-error="value"></div>
        @endif
    </div>

    <div class="col-md-6" data-card-field="minimum_spend">
        <label class="form-label" for="{{ $idPrefix }}_minimum_spend">
            Minimum Spend Amount <span class="text-danger">*</span>
        </label>
        <div class="input-group has-validation">
            <span class="input-group-text">{{ $currencySymbol }}</span>
            <input
                type="number"
                class="form-control"
                id="{{ $idPrefix }}_minimum_spend"
                name="minimum_spend"
                value="0"
                min="0"
                step="0.01"
                required
            >
            <div class="invalid-feedback" data-card-error="minimum_spend"></div>
        </div>
    </div>

    <div class="col-md-6" data-card-field="product_ids">
        <label class="form-label" for="{{ $idPrefix }}_product_ids">Select Products</label>
        <div class="position-relative">
            <select
                class="form-select select2 card-product-select"
                id="{{ $idPrefix }}_product_ids"
                name="product_ids[]"
                multiple
                data-placeholder="Select a product"
                data-dropdown-parent="#{{ $modalId }}"
            >
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback" data-card-error="product_ids"></div>
        </div>
    </div>

    <div class="col-md-6" data-card-field="valid_until">
        <label class="form-label" for="{{ $idPrefix }}_valid_until">
            Valid Until
            @if ($validUntilRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
        <input
            type="date"
            class="form-control"
            id="{{ $idPrefix }}_valid_until"
            name="valid_until"
            min="{{ $resolvedValidUntilMin }}"
            @if ($validUntilRequired) required @endif
        >
        <div class="invalid-feedback" data-card-error="valid_until"></div>
    </div>

    @if ($showStatus)
        <div class="col-md-6" data-card-field="is_active">
            <label class="form-label d-block">Status</label>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check form-switch mt-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="{{ $idPrefix }}_is_active"
                    name="is_active"
                    value="1"
                    checked
                >
                <label class="form-check-label" for="{{ $idPrefix }}_is_active">Active</label>
            </div>
            <div class="invalid-feedback d-block" data-card-error="is_active"></div>
        </div>
    @endif
</div>
