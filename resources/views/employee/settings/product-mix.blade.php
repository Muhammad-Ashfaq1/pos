@extends('layouts.employee-portal')

@section('title', 'Settings · Product Mix')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/employee-dashboard.css') }}?v={{ filemtime(public_path('assets/css/employee-dashboard.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/employee-settings.css') }}?v={{ filemtime(public_path('assets/css/employee-settings.css')) }}" />
@endpush

@section('content')
    <x-employee.settings-shell active-settings-tab="product-mix">
        <div class="employee-settings-stack"
             data-product-mix-picker
             data-max-selected="{{ $maxSelected }}">
            <form method="POST"
                  action="{{ route('employee.preferences.product-mix-cards') }}"
                  class="employee-settings-form">
                @csrf
                @method('PUT')

                <div class="employee-settings-panel pos-glass-card pos-tone-primary">
                    <div class="pos-glass-intro employee-settings-toolbar">
                        <div class="pos-glass-intro-copy">
                            <h2 class="pos-glass-intro-title">Dashboard Product Mix Cards</h2>
                            <p class="pos-glass-intro-subtitle mb-0">
                                Select which cards you want to display on your dashboard product mix section.
                                <strong>You can select a maximum of {{ $maxSelected }} cards.</strong>
                            </p>
                        </div>
                        <div class="employee-settings-toolbar-actions">
                            <span class="pos-glass-pill pos-tone-primary">
                                Currently selected:
                                <strong><span data-selected-count>{{ $selectedCount }}</span>/{{ $maxSelected }}</strong>
                            </span>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-check me-1"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <div class="employee-pm-group-stack">
                        @foreach($groupedCards as $groupLabel => $cards)
                            @php
                                $groupSelected = collect($cards)->whereIn('key', $selectedKeys)->count();
                                $groupKey = (string) ($cards->first()['group'] ?? 'product');
                                $groupTone = $groupKey === 'order' ? 'success' : 'info';
                                $groupIcon = $groupKey === 'order' ? 'tabler-clipboard-data' : 'tabler-package';
                            @endphp
                            <section class="employee-settings-panel pos-glass-card pos-tone-{{ $groupTone }}"
                                     data-pm-group-panel="{{ $groupLabel }}">
                                <div class="employee-pm-group-head">
                                    <span class="pos-stat-head mb-0">
                                        <span class="pos-stat-icon">
                                            <i class="icon-base ti {{ $groupIcon }}"></i>
                                        </span>
                                        <h3 class="pos-stat-label mb-0">{{ $groupLabel }}</h3>
                                    </span>
                                    <span class="pos-glass-pill pos-tone-{{ $groupTone }}">
                                        <span data-group-count="{{ $groupLabel }}">{{ $groupSelected }}</span> selected
                                    </span>
                                </div>

                                <div class="employee-pm-card-grid">
                                    @foreach($cards as $card)
                                        <x-employee.product-mix-option
                                            :card="$card"
                                            :checked="in_array($card['key'], $selectedKeys, true)"
                                            :preview="$previewByKey[$card['key']] ?? null"
                                        />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </form>

            <section class="employee-settings-panel pos-glass-card pos-tone-primary" aria-label="Dashboard preview">
                <div class="pos-glass-intro employee-pm-preview-head">
                    <div class="pos-glass-intro-copy">
                        <h3 class="pos-glass-intro-title">Dashboard preview</h3>
                        <p class="pos-glass-intro-subtitle mb-0">How your selected cards will appear on the dashboard.</p>
                    </div>
                </div>
                <div class="preview-stats-grid pos-ed-kpis" data-pm-slots></div>
                <p class="pos-glass-intro-subtitle mb-0" data-pm-preview-empty hidden>Select a card to preview.</p>
            </section>
        </div>
    </x-employee.settings-shell>
@endsection

@push('page-script')
    <script src="{{ asset('assets/js/employee/product-mix-settings.js') }}?v={{ filemtime(public_path('assets/js/employee/product-mix-settings.js')) }}"></script>
@endpush
