@extends('layouts.customer-portal')

@section('title', 'Vehicles')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Vehicles</h1>
                <p class="cp-page-subtitle">Vehicles on file at this shop</p>
            </div>
        </div>
    </div>

    <div class="cp-panel">
        <div class="cp-list" id="cp-vehicles-list">
            <div class="cp-list-empty">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="mb-0">Loading...</p>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script src="{{ asset('assets/js/customer/vehicles.js') }}?v={{ filemtime(public_path('assets/js/customer/vehicles.js')) }}"></script>
@endpush
