@extends('layouts.app')

@section('title', 'Shop List')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
@php
    $resolveStatus = fn ($shop) => $shop->status instanceof \App\Enums\TenantStatus ? $shop->status->value : $shop->status;
    $pendingCount = $shops->filter(fn ($shop) => $resolveStatus($shop) === 'pending')->count();
    $approvedCount = $shops->filter(fn ($shop) => $resolveStatus($shop) === 'approved')->count();
    $suspendedCount = $shops->filter(fn ($shop) => $resolveStatus($shop) === 'suspended')->count();
    $rejectedCount = $shops->filter(fn ($shop) => $resolveStatus($shop) === 'rejected')->count();
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="pos-glass-card pos-tone-primary">
            <div class="pos-glass-intro">
                <div class="pos-glass-intro-copy">
                    <h4 class="pos-glass-intro-title">Manage every shop from one approval console</h4>
                    <p class="pos-glass-intro-subtitle">
                        Review registrations, impersonate approved tenants for support, and keep your SaaS onboarding queue moving without leaving this page.
                    </p>
                </div>
                <div class="pos-glass-intro-actions d-flex flex-wrap gap-2 align-items-center">
                    <span class="pos-glass-pill pos-tone-primary">
                        <i class="icon-base ti tabler-building-store" aria-hidden="true"></i>
                        {{ $shops->count() }} shops
                    </span>
                    @if ($pendingCount > 0)
                        <span class="pos-glass-pill pos-tone-warning">
                            <i class="icon-base ti tabler-hourglass-high" aria-hidden="true"></i>
                            {{ $pendingCount }} pending
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-building-store" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Shops</h6>
                </div>
                <p class="pos-stat-value">{{ $shops->count() }}</p>
                <p class="pos-stat-desc mb-0">Platform-wide registrations</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-warning h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-hourglass-high" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Pending Review</h6>
                </div>
                <p class="pos-stat-value text-warning">{{ $pendingCount }}</p>
                <p class="pos-stat-desc mb-0">Action needed — requires review</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-circle-check" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Approved</h6>
                </div>
                <p class="pos-stat-value text-success">{{ $approvedCount }}</p>
                <p class="pos-stat-desc mb-0">Active tenant stores</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-danger h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-alert-triangle" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Flagged</h6>
                </div>
                <p class="pos-stat-value text-danger">{{ $suspendedCount + $rejectedCount }}</p>
                <p class="pos-stat-desc mb-0">Suspended or rejected</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="pos-listing">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar">
                    <h4 class="pos-listing-title">Shop Directory</h4>
                    <div class="pos-listing-search-slot">
                        <div class="dt-search">
                            <input
                                type="search"
                                id="shopTableSearch"
                                class="form-control"
                                placeholder="Search shops"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <div class="pos-listing-toolbar-tools">
                        <div class="pos-listing-toolbar-actions d-flex flex-wrap gap-2 align-items-center" id="shopTableActions">
                            <span class="badge bg-label-warning">Pending {{ $pendingCount }}</span>
                            <span class="badge bg-label-success">Approved {{ $approvedCount }}</span>
                            @if ($suspendedCount > 0)
                                <span class="badge bg-label-secondary">Suspended {{ $suspendedCount }}</span>
                            @endif
                            @if ($rejectedCount > 0)
                                <span class="badge bg-label-danger">Rejected {{ $rejectedCount }}</span>
                            @endif
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="addShopBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#shopSaveModal"
                            >
                                <i class="ti tabler-plus me-1"></i>
                                Add Shop
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-datatable table-responsive pos-listing-table pt-0">
                    <table class="shops-datatables table table-hover align-middle mb-0" id="shop-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Owner</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Shop</th>
                                <th>Plan & Expiry</th>
                                <th>Status</th>
                                <th class="text-center">Impersonate</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shop-table-body">
                            @include('shop.data-table')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade pos-listing-modal" id="shopSaveModal" tabindex="-1" aria-labelledby="shopModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form id="shopSaveForm" action="{{ route('admin.shops.save') }}" method="POST" novalidate>
                            @csrf
                            <input type="hidden" name="id" id="shop_id">

                            <div class="modal-header">
                                <h5 class="modal-title" id="shopModalTitle">Add Shop</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="shop_owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="shop_owner_name" name="owner_name" maxlength="150">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_owner_email" class="form-label">Owner Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="shop_owner_email" name="owner_email" maxlength="150">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_password" class="form-label">
                                            Password <span class="text-danger" id="shop_password_star">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="shop_password" name="password" minlength="8">
                                        <small class="form-text text-muted d-none" id="shopPasswordHelp">Leave blank to keep the current password.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_name_input" class="form-label">Shop Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="shop_name_input" name="shop_name" maxlength="150">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_status_select" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="shop_status_select" name="status">
                                            @foreach(\App\Enums\TenantStatus::cases() as $tenantStatus)
                                                <option value="{{ $tenantStatus->value }}">{{ $tenantStatus->label() }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_plan_id" class="form-label">Subscription Plan</label>
                                        <select class="form-select" id="shop_plan_id" name="plan_id">
                                            <option value="">No plan</option>
                                            @foreach($plans as $plan)
                                                @php($planDuration = $plan->duration_type ?? \App\Enums\PlanDuration::tryFromDays((int) $plan->duration_days))
                                                <option value="{{ $plan->id }}" data-duration="{{ $planDuration->days() }}">
                                                    {{ $plan->name }} ({{ $planDuration->label() }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_plan_expires_at" class="form-label">Plan Expiry</label>
                                        <input type="text" class="form-control app-datepicker" id="shop_plan_expires_at" name="plan_expires_at" placeholder="YYYY-MM-DD" autocomplete="off">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_website_url" class="form-label">Website URL</label>
                                        <input type="url" class="form-control" id="shop_website_url" name="website_url" placeholder="https://example.com" maxlength="255">
                                        <small class="form-text text-muted">Must start with http:// or https://</small>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_business_type" class="form-label">Business Type</label>
                                        <input type="text" class="form-control" id="shop_business_type" name="business_type" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="shop_phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="shop_phone" name="phone" maxlength="50">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="shop_country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="shop_country" name="country" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="shop_state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="shop_state" name="state" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="shop_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="shop_city" name="city" maxlength="100">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="shop_address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="shop_address" name="address" maxlength="255">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="shopSubmitBtn" data-create-text="Save Shop" data-update-text="Update Shop">
                                    Save Shop
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        window.adminShops = {
            saveUrl: @json(route('admin.shops.save')),
            editUrl: @json(url('/admin/shops/__ID__/edit')),
            statusUrl: @json(url('/admin/shops/__ID__/status/__ACTION__')),
            impersonateUrl: @json(url('/admin/shops/impersonate/__ID__')),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/admin/shops.js') }}?v={{ filemtime(public_path('assets/js/admin/shops.js')) }}"></script>
@endsection
