@extends('layouts.employee-portal')

@section('title', 'Discounts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/gift-card.css') }}?v={{ filemtime(public_path('assets/css/gift-card.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/reward-card.css') }}?v={{ filemtime(public_path('assets/css/reward-card.css')) }}">
@endpush

@section('content')
    @php
        $modules = [
            'discount' => [
                'title' => 'Discount Cards',
                'singular' => 'Discount Card',
                'icon' => 'tabler-ticket',
                'modal' => 'addDiscountCardModal',
            ],
            'gift' => [
                'title' => 'Gift Cards',
                'singular' => 'Gift Card',
                'icon' => 'tabler-gift',
                'modal' => 'addGiftCardModal',
            ],
            'reward' => [
                'title' => 'Reward Cards',
                'singular' => 'Reward Card',
                'icon' => 'tabler-trophy',
                'modal' => 'addRewardCardModal',
            ],
        ];
        $currencySymbol = \App\Support\Currency::symbol();
        $organizationName = auth()->user()?->tenant?->display_name
            ?? (function_exists('tenant') ? tenant()?->display_name : null)
            ?? 'Shop';
        $initialModule = old('card_type', request('module', 'discount'));
        if (! array_key_exists($initialModule, $modules)) {
            $initialModule = 'discount';
        }
    @endphp

    <div class="employee-orders-page">
        <x-employee.page-header title="Discounts" :back-url="route('employee.dashboard')" back-title="Back to dashboard">
            <x-slot:actions>
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addCardBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $modules[$initialModule]['modal'] }}"
                >
                    <i class="ti tabler-plus me-1"></i>
                    <span data-add-card-label>Add {{ $modules[$initialModule]['singular'] }}</span>
                </button>
            </x-slot:actions>
        </x-employee.page-header>

        <section class="employee-orders-panel employee-orders-results employee-cards-panel">
            <div class="employee-orders-tabs" role="tablist" aria-label="Card types">
                @foreach ($modules as $module => $config)
                    <button
                        type="button"
                        class="employee-orders-tab {{ $module === $initialModule ? 'active' : '' }}"
                        data-card-section="{{ $module }}"
                        data-card-modal="#{{ $config['modal'] }}"
                        data-card-label="Add {{ $config['singular'] }}"
                        role="tab"
                        aria-selected="{{ $module === $initialModule ? 'true' : 'false' }}"
                    >
                        {{ $config['singular'] }}
                        (<span>{{ $cardsByType->get($module, collect())->count() }}</span>)
                    </button>
                @endforeach
            </div>

            @foreach ($modules as $module => $config)
                <div
                    id="{{ $module }}Section"
                    class="employee-cards-section {{ $module === $initialModule ? '' : 'd-none' }}"
                    data-card-panel="{{ $module }}"
                >
                    <div class="employee-orders-list-heading">
                        <h5>{{ $config['title'] }}</h5>
                    </div>

                    @php $cards = $cardsByType->get($module, collect()); @endphp

                    @if ($cards->isEmpty())
                        <div class="employee-orders-empty">
                            <i class="ti {{ $config['icon'] }}"></i>
                            <span>No {{ strtolower($config['title']) }} yet. Create one to get started.</span>
                        </div>
                    @elseif ($module === 'gift')
                        <div class="gift-card-list">
                            @foreach ($cards as $card)
                                <x-gift-card
                                    :amount="$card->value"
                                    :status="data_get($card->details, 'payment_status', 'unpaid')"
                                    :card-label="$card->name"
                                    :unique-id="$card->id"
                                    :expires-at="$card->valid_until"
                                    :organization-name="$organizationName"
                                />
                            @endforeach
                        </div>
                    @elseif ($module === 'reward')
                        <div class="reward-card-list">
                            @foreach ($cards as $card)
                                <x-reward-card
                                    :name="$card->name"
                                    :value="$card->value"
                                    :discount-type="$card->discount_type"
                                    :minimum-spend="$card->minimum_spend"
                                    :valid-until="$card->valid_until"
                                    :unique-id="$card->id"
                                    :product-ids="$card->productIds()"
                                    :editable="true"
                                    :collected="in_array(strtolower((string) data_get($card->details, 'status', '')), ['collected', 'redeemed'], true)
                                        || filter_var(data_get($card->details, 'collected'), FILTER_VALIDATE_BOOLEAN)"
                                />
                            @endforeach
                        </div>
                    @else
                        <div class="employee-loyalty-cards">
                            @foreach ($cards as $card)
                                <article class="employee-loyalty-card">
                                    <div class="employee-loyalty-card-top">
                                        <div class="employee-loyalty-card-title">
                                            <span class="employee-loyalty-card-icon">
                                                <i class="ti {{ $config['icon'] }}"></i>
                                            </span>
                                            <span class="employee-order-number">{{ $card->name }}</span>
                                        </div>
                                        <span class="badge bg-label-primary">
                                            {{ ucfirst($card->discount_type) }}
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
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>

    {{-- Discount Card Modal --}}
    <div
        class="modal fade"
        id="addDiscountCardModal"
        tabindex="-1"
        aria-labelledby="addDiscountCardModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('employee.cards.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="card_type" value="discount">

                    <div class="modal-header">
                        <h5 class="modal-title" id="addDiscountCardModalLabel">Add Discount Card</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="discount_card_name">
                                    Card Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control {{ $errors->has('name') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                    id="discount_card_name"
                                    name="name"
                                    value="{{ old('card_type') === 'discount' ? old('name') : '' }}"
                                    maxlength="150"
                                    required
                                >
                                @if ($errors->has('name') && old('card_type') === 'discount')
                                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discountType">
                                    Discount Type <span class="text-danger">*</span>
                                </label>
                                <select
                                    class="form-select {{ $errors->has('discount_type') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                    id="discountType"
                                    name="discount_type"
                                    required
                                >
                                    <option value="percentage" @selected(old('discount_type', 'percentage') === 'percentage')>Percentage</option>
                                    <option value="fixed" @selected(old('discount_type') === 'fixed')>Fixed Amount</option>
                                </select>
                                @if ($errors->has('discount_type') && old('card_type') === 'discount')
                                    <div class="invalid-feedback">{{ $errors->first('discount_type') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discountValue" id="discountValueLabel">
                                    Discount Percentage <span class="text-danger">*</span>
                                </label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text" id="discountValuePrefix">%</span>
                                    <input
                                        type="number"
                                        class="form-control {{ $errors->has('value') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                        id="discountValue"
                                        name="value"
                                        value="{{ old('card_type') === 'discount' ? old('value') : '' }}"
                                        min="0.01"
                                        step="0.01"
                                        required
                                    >
                                    @if ($errors->has('value') && old('card_type') === 'discount')
                                        <div class="invalid-feedback">{{ $errors->first('value') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discount_minimum_spend">
                                    Minimum Spend Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">{{ $currencySymbol }}</span>
                                    <input
                                        type="number"
                                        class="form-control {{ $errors->has('minimum_spend') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                        id="discount_minimum_spend"
                                        name="minimum_spend"
                                        value="{{ old('card_type') === 'discount' ? old('minimum_spend', 0) : 0 }}"
                                        min="0"
                                        step="0.01"
                                        required
                                    >
                                    @if ($errors->has('minimum_spend') && old('card_type') === 'discount')
                                        <div class="invalid-feedback">{{ $errors->first('minimum_spend') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discount_product_ids">Select Products</label>
                                <select
                                    class="form-select select2 card-product-select {{ ($errors->has('product_ids') || $errors->has('product_ids.*')) && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                    id="discount_product_ids"
                                    name="product_ids[]"
                                    multiple
                                    data-placeholder="Select a product"
                                    data-dropdown-parent="#addDiscountCardModal"
                                >
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            @selected(old('card_type') === 'discount' && in_array((string) $product->id, array_map('strval', (array) old('product_ids', [])), true))
                                        >
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (($errors->has('product_ids') || $errors->has('product_ids.*')) && old('card_type') === 'discount')
                                    <div class="invalid-feedback d-block">{{ $errors->first('product_ids') ?: $errors->first('product_ids.*') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discount_valid_until">
                                    Valid Until <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    class="form-control {{ $errors->has('valid_until') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                    id="discount_valid_until"
                                    name="valid_until"
                                    value="{{ old('card_type') === 'discount' ? old('valid_until') : '' }}"
                                    min="{{ now()->toDateString() }}"
                                    required
                                >
                                @if ($errors->has('valid_until') && old('card_type') === 'discount')
                                    <div class="invalid-feedback">{{ $errors->first('valid_until') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ([
        'gift' => [
            'title' => 'Gift Card',
            'valueLabel' => 'Gift Amount',
            'prefix' => $currencySymbol,
            'step' => '0.01',
        ],
        'reward' => [
            'title' => 'Reward Card',
            'valueLabel' => 'Reward Points',
            'prefix' => 'PTS',
            'step' => '1',
        ],
    ] as $module => $config)
        @php
            $isRewardEditError = $module === 'reward'
                && $errors->any()
                && old('card_type') === 'reward'
                && old('_edit_card_id');
        @endphp
        <div
            class="modal fade"
            id="add{{ ucfirst($module) }}CardModal"
            tabindex="-1"
            aria-labelledby="add{{ ucfirst($module) }}CardModalLabel"
            aria-hidden="true"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form
                        method="POST"
                        action="{{ $isRewardEditError
                            ? route('employee.cards.update', old('_edit_card_id'))
                            : route('employee.cards.store') }}"
                        novalidate
                        @if ($module === 'reward')
                            id="rewardCardForm"
                            data-store-action="{{ route('employee.cards.store') }}"
                            data-update-action-template="{{ route('employee.cards.update', ['card' => '__CARD__']) }}"
                            data-add-title="Add {{ $config['title'] }}"
                            data-edit-title="Edit {{ $config['title'] }}"
                            data-add-submit="Save {{ $config['title'] }}"
                            data-edit-submit="Update {{ $config['title'] }}"
                        @endif
                    >
                        @csrf
                        @if ($isRewardEditError)
                            @method('PUT')
                            <input type="hidden" name="_edit_card_id" value="{{ old('_edit_card_id') }}">
                        @endif
                        <input type="hidden" name="card_type" value="{{ $module }}">

                        <div class="modal-header">
                            <h5 class="modal-title" id="add{{ ucfirst($module) }}CardModalLabel">
                                {{ $isRewardEditError ? 'Edit' : 'Add' }} {{ $config['title'] }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $module }}_card_name">
                                        Card Name <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control {{ $errors->has('name') && old('card_type') === $module ? 'is-invalid' : '' }}"
                                        id="{{ $module }}_card_name"
                                        name="name"
                                        value="{{ old('card_type') === $module ? old('name') : '' }}"
                                        maxlength="150"
                                        required
                                    >
                                    @if ($errors->has('name') && old('card_type') === $module)
                                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $module }}_card_value">
                                        {{ $config['valueLabel'] }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">{{ $config['prefix'] }}</span>
                                        <input
                                            type="number"
                                            class="form-control {{ $errors->has('value') && old('card_type') === $module ? 'is-invalid' : '' }}"
                                            id="{{ $module }}_card_value"
                                            name="value"
                                            value="{{ old('card_type') === $module ? old('value') : '' }}"
                                            min="0.01"
                                            step="{{ $config['step'] }}"
                                            required
                                        >
                                        @if ($errors->has('value') && old('card_type') === $module)
                                            <div class="invalid-feedback">{{ $errors->first('value') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $module }}_minimum_spend">
                                        Minimum Spend Amount <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">{{ $currencySymbol }}</span>
                                        <input
                                            type="number"
                                            class="form-control {{ $errors->has('minimum_spend') && old('card_type') === $module ? 'is-invalid' : '' }}"
                                            id="{{ $module }}_minimum_spend"
                                            name="minimum_spend"
                                            value="{{ old('card_type') === $module ? old('minimum_spend', 0) : 0 }}"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                        @if ($errors->has('minimum_spend') && old('card_type') === $module)
                                            <div class="invalid-feedback">{{ $errors->first('minimum_spend') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $module }}_product_ids">Select Products</label>
                                    <select
                                        class="form-select select2 card-product-select {{ ($errors->has('product_ids') || $errors->has('product_ids.*')) && old('card_type') === $module ? 'is-invalid' : '' }}"
                                        id="{{ $module }}_product_ids"
                                        name="product_ids[]"
                                        multiple
                                        data-placeholder="Select a product"
                                        data-dropdown-parent="#add{{ ucfirst($module) }}CardModal"
                                    >
                                        @foreach ($products as $product)
                                            <option
                                                value="{{ $product->id }}"
                                                @selected(old('card_type') === $module && in_array((string) $product->id, array_map('strval', (array) old('product_ids', [])), true))
                                            >
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (($errors->has('product_ids') || $errors->has('product_ids.*')) && old('card_type') === $module)
                                        <div class="invalid-feedback d-block">{{ $errors->first('product_ids') ?: $errors->first('product_ids.*') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $module }}_valid_until">Valid Until</label>
                                    <input
                                        type="date"
                                        class="form-control {{ $errors->has('valid_until') && old('card_type') === $module ? 'is-invalid' : '' }}"
                                        id="{{ $module }}_valid_until"
                                        name="valid_until"
                                        value="{{ old('card_type') === $module ? old('valid_until') : '' }}"
                                        min="{{ now()->toDateString() }}"
                                    >
                                    @if ($errors->has('valid_until') && old('card_type') === $module)
                                        <div class="invalid-feedback">{{ $errors->first('valid_until') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" type="submit" @if ($module === 'reward') data-reward-submit @endif>
                                {{ $isRewardEditError ? 'Update' : 'Save' }} {{ $config['title'] }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('discountType');
    const label = document.getElementById('discountValueLabel');
    const prefix = document.getElementById('discountValuePrefix');
    const value = document.getElementById('discountValue');
    const addCardBtn = document.getElementById('addCardBtn');
    const addCardLabel = document.querySelector('[data-add-card-label]');
    const rewardForm = document.getElementById('rewardCardForm');
    const rewardModal = document.getElementById('addRewardCardModal');

    function updateDiscountField() {
        const percentage = type.value === 'percentage';
        label.innerHTML = (percentage ? 'Discount Percentage' : 'Fixed Amount') + ' <span class="text-danger">*</span>';
        prefix.textContent = percentage ? '%' : @json($currencySymbol);
        value.max = percentage ? '100' : '';
    }

    type.addEventListener('change', updateDiscountField);
    updateDiscountField();

    function activateModule(module) {
        const tab = document.querySelector('[data-card-section="' + module + '"]');
        if (!tab) {
            return;
        }

        document.querySelectorAll('[data-card-section]').forEach(function (item) {
            const active = item.dataset.cardSection === module;
            item.classList.toggle('active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        document.querySelectorAll('[data-card-panel]').forEach(function (panel) {
            panel.classList.toggle('d-none', panel.dataset.cardPanel !== module);
        });

        addCardBtn.setAttribute('data-bs-target', tab.dataset.cardModal);
        addCardLabel.textContent = tab.dataset.cardLabel;
        addCardBtn.setAttribute('data-active-module', module);

        const url = new URL(window.location.href);
        url.searchParams.set('module', module);
        window.history.replaceState({}, '', url);
    }

    document.querySelectorAll('[data-card-section]').forEach(function (button) {
        button.addEventListener('click', function () {
            activateModule(button.dataset.cardSection);
        });
    });

    activateModule(@json($initialModule));

    function initCardProductSelects() {
        if (!window.jQuery || typeof jQuery.fn.select2 !== 'function') {
            return;
        }

        jQuery('.card-product-select').each(function () {
            const $select = jQuery(this);

            if ($select.data('select2')) {
                return;
            }

            const dropdownParentSelector = $select.data('dropdown-parent');
            const options = {
                width: '100%',
                placeholder: $select.data('placeholder') || 'Select a product',
                allowClear: true,
                closeOnSelect: false,
                minimumResultsForSearch: 0,
                dropdownParent: dropdownParentSelector ? jQuery(dropdownParentSelector) : $select.parent(),
            };

            // Multi-select hides dropdown search by default; decorate like order_service_filter.
            // Force dropdown below the field (Select2 AttachBody otherwise flips upward near modal bottom).
            if (jQuery.fn.select2.amd) {
                const Utils = jQuery.fn.select2.amd.require('select2/utils');
                const Dropdown = jQuery.fn.select2.amd.require('select2/dropdown');
                const DropdownSearch = jQuery.fn.select2.amd.require('select2/dropdown/search');
                const AttachBody = jQuery.fn.select2.amd.require('select2/dropdown/attachBody');

                function AttachBodyForceBelow() {}

                AttachBodyForceBelow.prototype._positionDropdown = function () {
                    const offset = this.$container.offset();
                    const containerBottom = offset.top + this.$container.outerHeight(false);
                    const css = {
                        left: offset.left,
                        top: containerBottom,
                    };

                    let $offsetParent = this.$dropdownParent;
                    if (!$offsetParent || !$offsetParent.length) {
                        $offsetParent = jQuery(document.body);
                    }

                    if ($offsetParent[0] !== document.body) {
                        const parentOffset = $offsetParent.offset();
                        css.top -= parentOffset.top;
                        css.left -= parentOffset.left;
                        css.top += $offsetParent.scrollTop();
                        css.left += $offsetParent.scrollLeft();
                    }

                    this.$dropdown
                        .removeClass('select2-dropdown--above')
                        .addClass('select2-dropdown--below');
                    this.$container
                        .removeClass('select2-container--above')
                        .addClass('select2-container--below');

                    this.$dropdownContainer.css(css);
                };

                options.dropdownAdapter = Utils.Decorate(
                    Utils.Decorate(
                        Utils.Decorate(Dropdown, DropdownSearch),
                        AttachBody
                    ),
                    AttachBodyForceBelow
                );
            }

            $select.select2(options);
        });
    }

    initCardProductSelects();

    function setRewardProductIds(productIds) {
        const $select = window.jQuery ? jQuery('#reward_product_ids') : null;
        if (!$select || !$select.length) {
            return;
        }

        initCardProductSelects();
        $select.val((productIds || []).map(String)).trigger('change');
    }

    function clearRewardValidationState() {
        if (!rewardForm) {
            return;
        }

        rewardForm.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        rewardForm.querySelectorAll('.invalid-feedback').forEach(function (el) {
            el.remove();
        });
    }

    function resetRewardFormToCreate() {
        if (!rewardForm) {
            return;
        }

        rewardForm.setAttribute('action', rewardForm.dataset.storeAction);
        rewardForm.querySelectorAll('input[name="_method"], input[name="_edit_card_id"]').forEach(function (el) {
            el.remove();
        });

        rewardForm.reset();
        document.getElementById('reward_minimum_spend').value = '0';
        setRewardProductIds([]);
        clearRewardValidationState();

        document.getElementById('addRewardCardModalLabel').textContent = rewardForm.dataset.addTitle;
        const submitBtn = rewardForm.querySelector('[data-reward-submit]');
        if (submitBtn) {
            submitBtn.textContent = rewardForm.dataset.addSubmit;
        }
    }

    function openRewardEdit(card) {
        if (!rewardForm || !rewardModal || !card || !card.id) {
            return;
        }

        clearRewardValidationState();

        const updateAction = rewardForm.dataset.updateActionTemplate.replace('__CARD__', String(card.id));
        rewardForm.setAttribute('action', updateAction);

        let methodInput = rewardForm.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            const csrf = rewardForm.querySelector('input[name="_token"]');
            if (csrf) {
                csrf.insertAdjacentElement('afterend', methodInput);
            } else {
                rewardForm.prepend(methodInput);
            }
        }
        methodInput.value = 'PUT';

        let editIdInput = rewardForm.querySelector('input[name="_edit_card_id"]');
        if (!editIdInput) {
            editIdInput = document.createElement('input');
            editIdInput.type = 'hidden';
            editIdInput.name = '_edit_card_id';
            rewardForm.appendChild(editIdInput);
        }
        editIdInput.value = String(card.id);

        document.getElementById('reward_card_name').value = card.name || '';
        document.getElementById('reward_card_value').value = card.value != null ? card.value : '';
        document.getElementById('reward_minimum_spend').value = card.minimum_spend != null ? card.minimum_spend : '0';
        document.getElementById('reward_valid_until').value = card.valid_until || '';
        setRewardProductIds(card.product_ids || []);

        document.getElementById('addRewardCardModalLabel').textContent = rewardForm.dataset.editTitle;
        const submitBtn = rewardForm.querySelector('[data-reward-submit]');
        if (submitBtn) {
            submitBtn.textContent = rewardForm.dataset.editSubmit;
        }

        bootstrap.Modal.getOrCreateInstance(rewardModal).show();
    }

    document.querySelectorAll('[data-edit-reward-card]').forEach(function (button) {
        button.addEventListener('click', function () {
            let card = {};
            try {
                card = JSON.parse(button.getAttribute('data-card') || '{}');
            } catch (e) {
                card = {};
            }
            openRewardEdit(card);
        });
    });

    if (rewardModal) {
        rewardModal.addEventListener('hidden.bs.modal', function () {
            resetRewardFormToCreate();
        });
    }

    if (addCardBtn) {
        addCardBtn.addEventListener('click', function () {
            if (addCardBtn.getAttribute('data-bs-target') === '#addRewardCardModal') {
                resetRewardFormToCreate();
            }
        });
    }

    @if ($errors->any())
        const errorType = @json(old('card_type', 'discount'));
        const modalId = errorType === 'discount'
            ? 'addDiscountCardModal'
            : 'add' + errorType.charAt(0).toUpperCase() + errorType.slice(1) + 'CardModal';
        bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();
    @endif
});
</script>
@endpush
