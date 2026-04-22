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
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Shop Directory</h5>
                    <p class="text-muted mb-0">Central admin view across all registered tenants</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-label-warning">Pending {{ $pendingCount }}</span>
                    <span class="badge bg-label-success">Approved {{ $approvedCount }}</span>
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

<div class="modal fade" id="shopActionModal" tabindex="-1" aria-labelledby="shopActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="shopActionModalLabel">Edit Shop</h5>
                    <p class="text-muted mb-0 small">Review shop details and choose the next action.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rounded bg-label-primary p-3 mb-3">
                    <div class="fw-semibold fs-5" id="modalShopName">-</div>
                    <div class="text-muted small" id="modalShopOwner">-</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Owner Email</div>
                            <div id="modalShopEmail">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Current Status</div>
                            <div id="modalShopStatus"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small fw-semibold mb-1">Available Actions</div>
                            <div class="d-flex flex-wrap gap-2" id="modalActionButtons"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
$(document).ready(function () {
    const $shopActionModal = $('#shopActionModal');
    const shopActionModal = $shopActionModal.length ? bootstrap.Modal.getOrCreateInstance($shopActionModal[0]) : null;

    const datatableOptions = {
        responsive: true,
        processing: true,
        order: [],
        language: {
            search: '',
            searchPlaceholder: 'Search shops, owners, or email'
        },
        columnDefs: [
            { orderable: false, targets: [4, 5, 6] }
        ]
    };

    const buildActionButton = function (shopId, action, label, btnClass, icon) {
        return `
            <button type="button" class="btn ${btnClass} btn-sm action-btn" data-id="${shopId}" data-action="${action}">
                <i class="icon-base ti ${icon} me-1"></i>${label}
            </button>
        `;
    };

    const populateShopActionModal = function ($button) {
        const shopId = $button.data('id');
        const shopName = $button.data('shop-name');
        const ownerName = $button.data('owner-name');
        const ownerEmail = $button.data('owner-email');
        const status = $button.data('status');
        const statusText = $button.data('status-text');
        const badgeClass = $button.data('badge-class');
        let actionsHtml = '';

        if (status === 'approved') {
            actionsHtml = buildActionButton(shopId, 'suspend', 'Suspend Shop', 'btn-warning', 'tabler-player-pause');
        } else if (status === 'pending') {
            actionsHtml =
                buildActionButton(shopId, 'approve', 'Approve', 'btn-success', 'tabler-check') +
                buildActionButton(shopId, 'reject', 'Reject', 'btn-danger', 'tabler-x');
        } else if (status === 'rejected') {
            actionsHtml = buildActionButton(shopId, 'approve', 'Approve', 'btn-success', 'tabler-refresh');
        } else if (status === 'suspended') {
            actionsHtml = buildActionButton(shopId, 'reactivate', 'Reactivate', 'btn-success', 'tabler-rotate-clockwise');
        }

        $('#modalShopName').text(shopName || '-');
        $('#modalShopOwner').text(ownerName ? `Owner: ${ownerName}` : 'Owner not available');
        $('#modalShopEmail').text(ownerEmail || '-');
        $('#modalShopStatus').html(`<span class="badge ${badgeClass}">${statusText}</span>`);
        $('#modalActionButtons').html(actionsHtml || '<span class="text-muted">No actions available.</span>');
    };

    const reinitializeShopTable = function () {
        shopTable.destroy();

        $('#shop-table-body').load(location.href + ' #shop-table-body>*', function () {
            shopTable = $('#shop-table').DataTable(datatableOptions);
        });
    };

    const promptReasonIfNeeded = function (action, callback) {
        if (! ['reject', 'suspend'].includes(action)) {
            callback('');
            return;
        }

        Swal.fire({
            title: action === 'reject' ? 'Reject shop?' : 'Suspend shop?',
            input: 'textarea',
            inputLabel: 'Reason',
            inputPlaceholder: 'Add a short audit note',
            inputAttributes: {
                'aria-label': 'Reason'
            },
            showCancelButton: true,
            confirmButtonText: 'Continue',
            inputValidator: (value) => !value ? 'A reason is required.' : undefined
        }).then((result) => {
            if (result.isConfirmed) {
                callback(result.value);
            }
        });
    };

    const submitShopAction = function (button, reason = '') {
        let $button = $(button);
        let id = $button.data('id');
        let action = $button.data('action');
        let token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
           url: '/admin/shops/' + id + '/status/' + action,
            type: 'POST',
            data: {
                _token: token,
                reason: reason
            },
            success: function (response) {

                if (response.success) {

                    if (typeof window.appNotify === 'function') {
                        window.appNotify('success', response.message);
                    }

                    if (shopActionModal) {
                        shopActionModal.hide();
                    }

                    reinitializeShopTable();

                    let row = $button.closest('tr');
                    let badge = row.find('.status-badge');

                    badge
                        .removeClass('bg-success bg-danger bg-warning bg-secondary')
                        .addClass(response.badge_class)
                        .text(response.status_text);
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                if (typeof window.appNotify === 'function') {
                    window.appNotify('error', xhr.responseJSON?.message || 'Action failed.');
                }
            }
        });
    };

    let shopTable = $('#shop-table').DataTable(datatableOptions);

    // =========================
    // ACTION BUTTONS (AJAX)
    // =========================
    $(document).on('click', '.action-btn', function (e) {
        e.preventDefault();
        let $button = $(this);
        let action = $button.data('action');

        promptReasonIfNeeded(action, function (reason) {
            submitShopAction($button, reason);
        });
    });

    $(document).on('click', '.edit-shop-btn', function (e) {
        e.preventDefault();

        populateShopActionModal($(this));

        if (shopActionModal) {
            shopActionModal.show();
        }
    });
});


// =========================
// IMPERSONATE CONFIRMATION
// =========================
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
