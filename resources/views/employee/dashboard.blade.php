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
            @include('employee.partials.preview-product-mix', ['summaryCards' => $summaryCards])
            @include('employee.partials.preview-operations', ['operations' => $operations])
        </section>

        <section class="preview-right-column">
            @include('employee.partials.preview-tiles-grid', ['tiles' => $tiles])
        </section>
    </div>

    @include('employee.partials.preview-bottom-nav', ['bottomNav' => $bottomNav])
@endsection
