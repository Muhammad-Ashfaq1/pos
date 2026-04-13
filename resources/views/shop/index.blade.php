@extends('layouts.app')

@section('title', 'Shop List')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">All Shops</h5>
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

        $.ajax({
           url: '/admin/shops/' + action + '/' + id,
            type: 'POST',
            data: {
                _token: token
            },
            success: function (response) {

                if (response.success) {

                    toastr.success(response.message);

                    // refresh table body
                    shopTable.destroy();

                    $('#shop-table-body').load(location.href + ' #shop-table-body>*', function () {

                        shopTable = $('#shop-table').DataTable({
                            responsive: true,
                            processing: true
                        });
                    });

                    // update status badge live
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
                toastr.error('Action failed!');
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
        text: "You will login as this shop admin",
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