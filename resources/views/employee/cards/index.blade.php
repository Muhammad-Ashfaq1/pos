@extends('layouts.employee-portal')

@section('title', 'Discounts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/gift-card.css') }}?v={{ filemtime(public_path('assets/css/gift-card.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/reward-card.css') }}?v={{ filemtime(public_path('assets/css/reward-card.css')) }}">
@endpush

@section('content')
    @php
        $modules = \App\Models\Card::typeMeta();
        $currencySymbol = \App\Support\Currency::symbol();
        $organizationName = auth()->user()?->tenant?->display_name
            ?? (function_exists('tenant') ? tenant()?->display_name : null)
            ?? 'Shop';
        $initialModule = old('card_type', $cardType ?? 'discount');
        if (! array_key_exists($initialModule, $modules)) {
            $initialModule = 'discount';
        }
    @endphp

    <div class="employee-orders-page">
        <x-employee.page-header title="Discounts" :back-url="route('employee.dashboard')" back-title="Back to dashboard">
            <x-slot:actions>
                @can('create', \App\Models\Card::class)
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
                @endcan
            </x-slot:actions>
        </x-employee.page-header>

        <section class="employee-orders-panel employee-orders-results employee-cards-panel">
            <div class="employee-orders-tabs" role="tablist" aria-label="Card types">
                @foreach ($modules as $module => $config)
                    <a
                        href="{{ route('employee.cards.type', $module) }}"
                        class="employee-orders-tab {{ $module === $initialModule ? 'active' : '' }}"
                        data-card-section="{{ $module }}"
                        data-card-modal="#{{ $config['modal'] }}"
                        data-card-label="Add {{ $config['singular'] }}"
                        role="tab"
                        aria-selected="{{ $module === $initialModule ? 'true' : 'false' }}"
                    >
                        {{ $config['tab'] }}
                        (<span>{{ (int) $cardCounts->get($module, 0) }}</span>)
                    </a>
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
                        @php
                            $listClass = match ($module) {
                                'gift' => 'gift-card-list',
                                'reward' => 'reward-card-list',
                                default => 'employee-loyalty-cards',
                            };
                        @endphp
                        <div class="{{ $listClass }}" data-card-list="{{ $module }}">
                            @foreach ($cards as $card)
                                @include('employee.cards.partials.card-item', [
                                    'card' => $card,
                                    'organizationName' => $organizationName,
                                    'productsById' => $productsById,
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>

    @include('employee.cards.partials.create-modals', [
        'products' => $products,
        'currencySymbol' => $currencySymbol,
    ])
@endsection

@push('page-script')
<script>
    window.employeeCards = {
        currencySymbol: @json($currencySymbol),
        initialModule: @json($initialModule),
    };
</script>
<script src="{{ asset('assets/js/cards-form.js') }}?v={{ filemtime(public_path('assets/js/cards-form.js')) }}"></script>
<script src="{{ asset('assets/js/employee/cards.js') }}?v={{ filemtime(public_path('assets/js/employee/cards.js')) }}"></script>
@endpush
