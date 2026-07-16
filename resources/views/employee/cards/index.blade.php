@extends('layouts.employee-portal')

@section('title', 'Cards')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
@endpush

@section('content')
    @php
        $modules = [
            'discount' => [
                'title' => 'Discount Cards',
                'singular' => 'Discount Card',
                'icon' => 'tabler-discount-2',
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
        $initialModule = old('card_type', request('module', 'discount'));
        if (! array_key_exists($initialModule, $modules)) {
            $initialModule = 'discount';
        }
    @endphp

    <div class="employee-orders-page">
        <x-employee.page-header title="Cards" :back-url="route('employee.dashboard')" back-title="Back to dashboard">
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
                                            @if ($module === 'discount')
                                                {{ ucfirst($card->discount_type) }}
                                            @else
                                                {{ $config['singular'] }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="employee-loyalty-card-value">
                                        @if ($module === 'discount')
                                            {{ $card->discount_type === 'percentage'
                                                ? rtrim(rtrim(number_format((float) $card->value, 2, '.', ''), '0'), '.') . '%'
                                                : \App\Support\Currency::format((float) $card->value) }}
                                        @elseif ($module === 'gift')
                                            {{ \App\Support\Currency::format((float) $card->value) }}
                                        @else
                                            {{ number_format((float) $card->value) }} points
                                        @endif
                                    </div>

                                    <div class="employee-loyalty-card-meta">
                                        <span>Min. spend {{ \App\Support\Currency::format((float) $card->minimum_spend) }}</span>
                                        <span>Product: {{ $card->product?->name ?? 'All products' }}</span>
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
                                <label class="form-label" for="discount_product_id">Select Product</label>
                                <select
                                    class="form-select {{ $errors->has('product_id') && old('card_type') === 'discount' ? 'is-invalid' : '' }}"
                                    id="discount_product_id"
                                    name="product_id"
                                >
                                    <option value="">All products</option>
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            @selected(old('card_type') === 'discount' && (string) old('product_id') === (string) $product->id)
                                        >
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('product_id') && old('card_type') === 'discount')
                                    <div class="invalid-feedback">{{ $errors->first('product_id') }}</div>
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
                    <form method="POST" action="{{ route('employee.cards.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="card_type" value="{{ $module }}">

                        <div class="modal-header">
                            <h5 class="modal-title" id="add{{ ucfirst($module) }}CardModalLabel">Add {{ $config['title'] }}</h5>
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
                                    <label class="form-label" for="{{ $module }}_product_id">Select Product</label>
                                    <select
                                        class="form-select {{ $errors->has('product_id') && old('card_type') === $module ? 'is-invalid' : '' }}"
                                        id="{{ $module }}_product_id"
                                        name="product_id"
                                    >
                                        <option value="">All products</option>
                                        @foreach ($products as $product)
                                            <option
                                                value="{{ $product->id }}"
                                                @selected(old('card_type') === $module && (string) old('product_id') === (string) $product->id)
                                            >
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('product_id') && old('card_type') === $module)
                                        <div class="invalid-feedback">{{ $errors->first('product_id') }}</div>
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
                            <button class="btn btn-primary" type="submit">Save {{ $config['title'] }}</button>
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
