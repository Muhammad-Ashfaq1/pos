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

@endsection

@section('scripts')

<script>
$(document).ready(function () {

    let shopTable = $('#shop-table').DataTable({
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
    });

    // =========================
    // ACTION BUTTONS (AJAX)
    // =========================
    $(document).on('click', '.action-btn', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let action = $(this).data('action');
        let button = $(this);
        let token = $('meta[name="csrf-token"]').attr('content');
        let requiresReason = ['reject', 'suspend'].includes(action);

        let submitAction = function(reason = '') {
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

                        shopTable.destroy();

                        $('#shop-table-body').load(location.href + ' #shop-table-body>*', function () {

                            shopTable = $('#shop-table').DataTable({
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
                            });
                        });

                        let row = button.closest('tr');
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

        if (!requiresReason) {
            submitAction();
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
                submitAction(result.value);
            }
        });
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
