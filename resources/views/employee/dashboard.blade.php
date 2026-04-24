@extends('layouts.employee-portal')

@section('title', 'Employee Portal Dashboard')

@php
    $summaryCards = [
        ['value' => '0', 'label' => 'Orders', 'meta' => 'Completed Today', 'icon' => 'tabler-calendar-event', 'chip' => 'preview-chip--blue'],
        ['value' => '0', 'label' => 'Orders', 'meta' => 'Incompleted Today', 'icon' => 'tabler-map-pin-share', 'chip' => 'preview-chip--purple'],
        ['value' => '2', 'label' => 'Products', 'meta' => 'Available Today', 'icon' => 'tabler-search', 'chip' => 'preview-chip--violet'],
    ];

    $tiles = [
        ['label' => 'Time Clock', 'icon' => 'tabler-clock-hour-4'],
        ['label' => 'New Order', 'icon' => 'tabler-shopping-bag'],
        ['label' => 'Reports', 'icon' => 'tabler-report-search'],
        ['label' => 'Orders', 'icon' => 'tabler-clipboard-data'],
        ['label' => 'Returns', 'icon' => 'tabler-arrow-back-up'],
        ['label' => 'Product Setup', 'icon' => 'tabler-tool'],
        ['label' => 'Invoices', 'icon' => 'tabler-file-invoice'],
        ['label' => 'Discounts', 'icon' => 'tabler-badge'],
    ];

    $operations = [
        ['label' => 'End of Day Status', 'icon' => 'tabler-sun-low'],
        ['label' => 'Till Management', 'icon' => 'tabler-credit-card'],
    ];

    $bottomNav = [
        ['label' => 'POS', 'icon' => 'tabler-device-desktop'],
        ['label' => 'Customers', 'icon' => 'tabler-users'],
        ['label' => 'Inventory', 'icon' => 'tabler-package'],
        ['label' => 'Settings', 'icon' => 'tabler-settings'],
    ];
@endphp

@section('content')
    <div class="preview-grid">
        <section class="preview-left-column">
            <div class="preview-card">
                <div class="preview-card-header">
                    <div>
                        <h2 class="preview-card-title">Product Mix</h2>
                    </div>

                    <div class="preview-card-tools">
                        <select class="preview-select" aria-label="Employee dashboard filter">
                            <option selected>Today (Default)</option>
                            <option>This Week</option>
                            <option>This Month</option>
                        </select>

                        <div class="preview-updated">
                            <span class="preview-updated-label">Updated</span>
                            <span class="preview-updated-time">19 seconds ago</span>
                        </div>

                        <button type="button" class="preview-refresh-btn">
                            <i class="ti tabler-refresh"></i>
                        </button>

                        <span class="preview-status-dot"></span>
                    </div>
                </div>

                <div class="preview-card-body">
                    <div class="preview-stats-grid">
                        @foreach($summaryCards as $card)
                            <div class="preview-chip {{ $card['chip'] }}">
                                <div>
                                    <div class="preview-chip-number-row">
                                        <span class="preview-chip-value">{{ $card['value'] }}</span>
                                        <span class="preview-chip-label">{{ $card['label'] }}</span>
                                    </div>
                                    <div class="preview-chip-meta">{{ $card['meta'] }}</div>
                                </div>
                                <i class="ti {{ $card['icon'] }} preview-chip-icon"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="preview-card preview-operations-card">
                <div class="preview-card-header">
                    <div>
                        <h2 class="preview-card-title">Operations</h2>
                    </div>
                </div>

                <div class="preview-card-body">
                    @foreach($operations as $operation)
                        <div class="preview-operation-item">
                            <div class="preview-operation-main">
                                <span class="preview-operation-icon">
                                    <i class="ti {{ $operation['icon'] }}"></i>
                                </span>
                                <span class="preview-operation-label">{{ $operation['label'] }}</span>
                            </div>

                            <span class="preview-operation-link">
                                <i class="ti tabler-arrow-up-right"></i>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="preview-right-column">
            <div class="preview-tiles-grid">
                @foreach($tiles as $tile)
                    <div class="preview-card preview-tile">
                        <div class="preview-tile-content">
                            <span class="preview-tile-icon-wrap">
                                <i class="ti {{ $tile['icon'] }}"></i>
                            </span>
                            <h3 class="preview-tile-title">{{ $tile['label'] }}</h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <nav class="preview-bottom-nav">
        @foreach($bottomNav as $item)
            <a href="javascript:void(0)" class="preview-bottom-link">
                <span class="preview-bottom-icon">
                    <i class="ti {{ $item['icon'] }}"></i>
                </span>
                <span class="preview-bottom-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endsection
