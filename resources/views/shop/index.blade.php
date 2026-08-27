@extends('layouts.app')

@section('title', 'Shop List')

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
        <div class="card bg-label-primary">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-primary mb-3">Tenant Review Desk</span>
                        <h3 class="mb-2">Manage every shop from one approval console</h3>
                        <p class="text-muted mb-0">
                            Review registrations, impersonate approved tenants for support, and keep your SaaS onboarding queue moving without leaving this page.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}" alt="Shop management" class="img-fluid" style="max-height: 180px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Total Shops</span>
                        <h3 class="mb-0">{{ $shops->count() }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="icon-base ti tabler-building-store"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Pending Review</span>
                        <h3 class="mb-0 text-warning">{{ $pendingCount }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="icon-base ti tabler-hourglass-high"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Approved</span>
                        <h3 class="mb-0 text-success">{{ $approvedCount }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="icon-base ti tabler-circle-check"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted text-uppercase small fw-semibold d-block mb-1">Flagged</span>
                        <h3 class="mb-0 text-danger">{{ $suspendedCount + $rejectedCount }}</h3>
                    </div>
                    <span class="avatar avatar-sm">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="icon-base ti tabler-alert-triangle"></i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-title mb-1">Shop Directory</h5>
                    <p class="text-muted mb-0">Central admin view across all registered tenants</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-label-warning">Pending {{ $pendingCount }}</span>
                    <span class="badge bg-label-success">Approved {{ $approvedCount }}</span>
                    <button type="button" class="btn btn-primary btn-sm ms-1 me-1" id="addShopBtn" data-bs-toggle="modal" data-bs-target="#shopSaveModal">
                        <i class="icon-base ti tabler-plus me-1"></i>Add Shop
                    </button>
                    @if ($suspendedCount > 0)
                        <span class="badge bg-label-secondary">Suspended {{ $suspendedCount }}</span>
                    @endif
                    @if ($rejectedCount > 0)
                        <span class="badge bg-label-danger">Rejected {{ $rejectedCount }}</span>
                    @endif
                </div>
            </div>

            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table" id="shop-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Owner</th>
                            <th>Contact</th>
                            <th>Shop</th>
                            <th>Status</th>
                            <th>Impersonate</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody id="shop-table-body">
                        @include('shop.data-table')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Shop Modal --}}
<div class="modal fade" id="shopSaveModal" tabindex="-1" aria-labelledby="shopModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="shopSaveForm" action="{{ route('admin.shops.save') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id" id="shop_id">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="shopModalTitle">Add New Shop</h5>
                        <p class="text-muted mb-0 small" id="shopModalSubtitle">Fill in the mandatory and optional details for this shop.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Mandatory Fields --}}
                        <div class="col-md-6">
                            <label for="shop_owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shop_owner_name" name="owner_name" required maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_owner_email" class="form-label">Owner Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="shop_owner_email" name="owner_email" required maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_password" class="form-label">Password <span class="text-danger" id="shop_password_star">*</span></label>
                            <input type="password" class="form-control" id="shop_password" name="password" minlength="8">
                            <small class="form-text text-muted d-none" id="shopPasswordHelp">Leave blank to keep existing password.</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_name_input" class="form-label">Shop Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shop_name_input" name="shop_name" required maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_status_select" class="form-label">Shop Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="shop_status_select" name="status" required>
                                @foreach(\App\Enums\TenantStatus::cases() as $tenantStatus)
                                    <option value="{{ $tenantStatus->value }}">{{ $tenantStatus->label() }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_website_url" class="form-label">Website URL <small class="text-muted">(Optional)</small></label>
                            <input type="url" class="form-control" id="shop_website_url" name="website_url" placeholder="https://example.com" maxlength="255">
                            <small class="form-text text-muted">Must start with http:// or https://</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Optional Fields --}}
                        <div class="col-md-6">
                            <label for="shop_business_type" class="form-label">Business Type <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_business_type" name="business_type" placeholder="e.g. Auto Garage, Oil Center" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="shop_phone" class="form-label">Phone <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_phone" name="phone" placeholder="+1 555 000 0000" maxlength="50">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="shop_country" class="form-label">Country <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_country" name="country" placeholder="e.g. United States" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="shop_state" class="form-label">State <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_state" name="state" placeholder="e.g. California" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="shop_city" class="form-label">City <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_city" name="city" placeholder="e.g. Los Angeles" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label for="shop_address" class="form-label">Address <small class="text-muted">(Optional)</small></label>
                            <input type="text" class="form-control" id="shop_address" name="address" placeholder="123 Main St, Suite 100" maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="shopSubmitBtn">Save Shop</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const $shopSaveModal = $('#shopSaveModal');
    const getModalInstance = function () {
        if (!$shopSaveModal.length) return null;
        if (window.bootstrap && window.bootstrap.Modal) {
            return window.bootstrap.Modal.getOrCreateInstance($shopSaveModal[0]);
        }
        return null;
    };
    const $form = $('#shopSaveForm');

    const datatableOptions = {
        responsive: true,
        processing: true,
        order: [],
        language: {
            emptyTable: 'No shops found',
            search: '',
            searchPlaceholder: 'Search shops, owners, or email'
        },
        columnDefs: [
            { orderable: false, targets: [4, 5, 6] }
        ]
    };

    let shopTable = $('#shop-table').DataTable(datatableOptions);

    const reinitializeShopTable = function () {
        shopTable.destroy();
        $('#shop-table-body').load(location.href + ' #shop-table-body>*', function () {
            shopTable = $('#shop-table').DataTable(datatableOptions);
        });
    };

    const clearFormErrors = function () {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');
    };

    const resetShopForm = function () {
        $form[0].reset();
        $('#shop_id').val('');
        clearFormErrors();
        $('#shopModalTitle').text('Add New Shop');
        $('#shopModalSubtitle').text('Fill in the mandatory and optional details for this shop.');
        $('#shopSubmitBtn').text('Save Shop');
        $('#shop_password_star').removeClass('d-none');
        $('#shopPasswordHelp').addClass('d-none');
    };

    $('#addShopBtn').on('click', function () {
        resetShopForm();
        const modal = getModalInstance();
        if (modal) modal.show();
    });

    $(document).on('click', '.edit-shop-btn', function (e) {
        e.preventDefault();
        const shopId = $(this).data('id');
        resetShopForm();

        $('#shopModalTitle').text('Edit Shop');
        $('#shopModalSubtitle').text('Update details for this shop.');
        $('#shopSubmitBtn').text('Update Shop');
        $('#shop_password_star').addClass('d-none');
        $('#shopPasswordHelp').removeClass('d-none');

        if (window.appLoading && typeof window.appLoading.show === 'function') {
            window.appLoading.show('Loading shop...');
        }

        $.ajax({
            url: '/admin/shops/' + shopId + '/edit',
            method: 'GET'
        })
        .done(function (response) {
            if (response.success && response.data) {
                const data = response.data;
                $('#shop_id').val(data.id);
                $('#shop_owner_name').val(data.owner_name);
                $('#shop_owner_email').val(data.owner_email);
                $('#shop_name_input').val(data.shop_name);
                $('#shop_status_select').val(data.status);
                $('#shop_website_url').val(data.website_url);
                $('#shop_business_type').val(data.business_type);
                $('#shop_country').val(data.country);
                $('#shop_state').val(data.state);
                $('#shop_city').val(data.city);
                $('#shop_phone').val(data.phone);
                $('#shop_address').val(data.address);

                const modal = getModalInstance();
                if (modal) modal.show();
            }
        })
        .fail(function (xhr) {
            if (typeof window.appNotify === 'function') {
                window.appNotify('error', xhr.responseJSON?.message || 'Unable to load shop details.');
            }
        })
        .always(function () {
            if (window.appLoading && typeof window.appLoading.hide === 'function') {
                window.appLoading.hide(200);
            }
        });
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const websiteUrl = $('#shop_website_url').val().trim();
        if (websiteUrl !== '' && ! /^https?:\/\//i.test(websiteUrl)) {
            $('#shop_website_url').addClass('is-invalid');
            $('#shop_website_url').siblings('.invalid-feedback').text('Website URL must start with http:// or https://');
            return;
        }

        const $submitBtn = $('#shopSubmitBtn');
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize()
        })
        .done(function (response) {
            if (response.success) {
                if (typeof window.appNotify === 'function') {
                    window.appNotify('success', response.message);
                }
                const modal = getModalInstance();
                if (modal) modal.hide();
                reinitializeShopTable();
            }
        })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    const $input = $form.find('[name="' + field + '"]');
                    if ($input.length) {
                        $input.addClass('is-invalid');
                        $input.siblings('.invalid-feedback').text(messages[0]);
                    }
                });
            } else if (typeof window.appNotify === 'function') {
                window.appNotify('error', xhr.responseJSON?.message || 'Save failed.');
            }
        })
        .always(function () {
            $submitBtn.prop('disabled', false);
        });
    });
});

function confirmImpersonate(shopId) {
    Swal.fire({
        title: 'Impersonate Shop?',
        text: "You will sign in as this shop admin",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#696cff',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Continue'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/admin/shops/impersonate/' + shopId;
        }
    });
}
</script>
@endsection
