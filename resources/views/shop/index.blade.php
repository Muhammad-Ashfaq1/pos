@extends('layouts.app')

@section('title', 'Shop List')

@section('content')

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

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Shop Directory</h5>
                    <p class="text-muted mb-0">Central admin view across all registered tenants</p>
                </div>
            </div>

            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table" id="shop-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Owner Name</th>
                            <th>Email</th>
                            <th>Shop Name</th>
                            <th>Status</th>
                            <th>Impersonate</th>
                            <th>Action</th>
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
        processing: true
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

                        toastr.success(response.message);

                        shopTable.destroy();

                        $('#shop-table-body').load(location.href + ' #shop-table-body>*', function () {

                            shopTable = $('#shop-table').DataTable({
                                responsive: true,
                                processing: true
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
                    toastr.error(xhr.responseJSON?.message || 'Action failed.');
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
