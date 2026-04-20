@extends('layouts.app')

@section('title', 'Sub Categories')

@section('content')

<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">E-commerce /</span> Sub Categories
</h4>

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#addSubCategoryModal">
        <i class="ti tabler-plus me-1"></i> Add Sub Category
    </button>
</div>

        <div class="table-responsive">
            <table id="subcategory-table" class="table mb-0">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

@include('tenant.ecommerce.sub-categories.modal')

@endsection


@section('scripts')

<script>
$(document).ready(function () {

    let editId = null;

    // ================= DATA TABLE =================
    const table = $('#subcategory-table').DataTable({
        ajax: {
            url: "{{ route('tenant.ecommerce.subcategories.list') }}",
            dataSrc: 'data'
        },

        columns: [
            {
                data: null,
                render: (d, t, r, m) => m.row + 1
            },

            { data: 'name' },

            {
                data: 'category.name',
                defaultContent: '-'
            },

            {
                data: 'is_active',
                className: 'text-center',
                render: function (d) {
                    return d == 1
                        ? `<span class="badge bg-label-success">Active</span>`
                        : `<span class="badge bg-label-danger">Inactive</span>`;
                }
            },

            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function (id) {
                    return `
                        <div class="d-flex justify-content-center gap-1">

                            <a href="javascript:void(0);" data-id="${id}" class="btn btn-icon view-operation">
                                <i class="ti tabler-eye"></i>
                            </a>

                            <a href="javascript:void(0);" data-id="${id}" class="btn btn-icon edit-operation">
                                <i class="ti tabler-edit"></i>
                            </a>

                            <a href="javascript:void(0);" data-id="${id}" class="btn btn-icon delete-operation">
                                <i class="ti tabler-trash text-danger"></i>
                            </a>

                        </div>
                    `;
                }
            }
        ]
    });


    // ================= SAVE =================
    $('#saveBtn').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('tenant.ecommerce.subcategories.save') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: editId,
                name: $('#name').val(),
                category_id: $('#category_id').val(),
                is_active: $('#is_active').is(':checked') ? 1 : 0
            },

            success: function (res) {

                $('#addSubCategoryModal').modal('hide');
                resetForm();

                table.ajax.reload(null, false);

                toastr.success(res.message ?? 'Saved successfully');
            },

             error: function(xhr){
                if(xhr.status === 422){
                    var errors = xhr.responseJSON.errors;
                    var message = '';
                    $.each(errors, function(key, value){ message += value[0] + '\n'; });
                    toastr.error(message);
                } else {
                    toastr.error('Something went wrong!');
                    console.log(xhr.responseText);
                }
            }
        });
    });


    // ================= EDIT =================
    $(document).on('click', '.edit-operation', function () {

        editId = $(this).data('id');

        $.ajax({
                 url: `/tenant/ecommerce/subcategories/${editId}/edit`,

            type: 'GET',

            success: function (res) {

                $('#name').val(res.data.name);
                $('#category_id').val(res.data.category_id);
                $('#is_active').prop('checked', res.data.is_active == 1);

                $('#addSubCategoryModal').modal('show');
            },

            error: function () {
                toastr.error('Failed to load data!');
            }
        });
    });


    // ================= DELETE =================
    $(document).on('click', '.delete-operation', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            text: "This will be deleted permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                          url: `/tenant/ecommerce/subcategories/${id}`,

                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },

                success: function () {

                    table.ajax.reload(null, false);

                    Swal.fire('Deleted!', 'SubCategory removed.', 'success');
                },

                error: function () {
                    toastr.error('Delete failed!');
                }
            });

        });
    });


    // ================= VIEW =================
    $(document).on('click', '.view-operation', function () {

        let id = $(this).data('id');

        $.ajax({
        url: `/tenant/ecommerce/subcategories/${id}/edit`,
            type: 'GET',

            success: function (res) {

                Swal.fire({
                    title: res.data.name,
                    html: `
                        <b>Category:</b> ${res.data.category?.name ?? '-'} <br>
                        <b>Status:</b> ${res.data.is_active == 1 ? 'Active' : 'Inactive'}
                    `,
                    icon: 'info'
                });
            },

            error: function () {
                toastr.error('Failed to load view');
            }
        });
    });


    // ================= RESET FORM =================
    function resetForm() {
        $('#name').val('');
        $('#category_id').val('');
        $('#is_active').prop('checked', true);
        editId = null;
    }

    $('#addSubCategoryModal').on('hidden.bs.modal', resetForm);

});
</script>

@endsection
