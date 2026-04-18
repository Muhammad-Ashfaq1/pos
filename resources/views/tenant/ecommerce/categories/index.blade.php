@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">E-commerce /</span> Categories
    </h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="row col-12">
                    <div class="col-lg-4 col-md-6 ">
                        <div class="mb-0 position-relative flex-grow-1 w-100">
                            <input type="text" class="form-control pe-5" placeholder="Search products...">
                            <i class="ti tabler-search position-absolute text-muted"
                                style="top: 50%; right: 1rem; transform: translateY(-50%);"></i>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-6 p-0 d-flex justify-content-end align-items-end">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-primary text-nowrap"
                                data-bs-target="#addCategoryModal"
                                data-bs-toggle="modal">
                                <i class="ti tabler-plus me-1"></i>Add Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="category-table" class="table table-operations dataTable mb-0">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                  <tbody></tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.categories.category-modal')
@endsection


@section('scripts')


<script>
$(document).ready(function () {

    let editCategoryId = null;

    // ================= DATATABLE INIT =================
    const table = $('#category-table').DataTable({
        ajax: {
           url: "{{ route('tenant.ecommerce.categories.list') }}",
            dataSrc: 'data'
        },

        columns: [
            {
                data: null,
                render: (data, type, row, meta) => meta.row + 1
            },

            { data: 'name' },

            {
                data: 'is_active',
                className: 'text-center',
                render: function (data) {
                    return data == 1
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
                        <div class="d-flex justify-content-center">
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
    $(document).on('click', '#saveCategoryButton', function (e) {
        e.preventDefault();

        const name = $('#categoryName').val().trim();
        const is_active = $('#categoryIsActive').is(':checked') ? 1 : 0;

        $.ajax({
            url: "{{ route('tenant.ecommerce.categories.save') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: editCategoryId,
                name,
                is_active
            },

            success: function (response) {

                $('#addCategoryModal').modal('hide');
                resetForm();

                // ✅ ONLY reload table (NO manual row add)
                table.ajax.reload(null, false);

                toastr.success(response.message ?? 'Saved successfully');
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    toastr.error(Object.values(errors).map(e => e[0]).join('\n'));
                } else {
                    toastr.error('Something went wrong!');
                }
            }
        });
    });


    // ================= EDIT =================
    $(document).on('click', '.edit-operation', function () {

        editCategoryId = $(this).data('id');

        $.ajax({
            url: `/tenant/ecommerce/categories/${editCategoryId}/edit`,
            type: 'GET',

            success: function (response) {
                if (response.success) {
                    $('#categoryName').val(response.data.name);
                    $('#categoryIsActive').prop('checked', response.data.is_active == 1);
                    $('#addCategoryModal').modal('show');
                }
            },

            error: function () {
                toastr.error('Failed to load category data!');
            }
        });
    });

    // ================= DELETE =================
    $(document).on('click', '.delete-operation', function () {

        const id = $(this).data('id');
        const row = table.row($(this).closest('tr'));

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                url: `/tenant/ecommerce/categories/${id}`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },

                success: function (response) {

                    if (response.success) {
                        row.remove().draw();
                        Swal.fire('Deleted!', response.message, 'success');
                    }
                },

                error: function () {
                    toastr.error('Delete failed!');
                }
            });
        });
    });

    // ================= VIEW =================
    $(document).on('click', '.view-operation', function () {

        const id = $(this).data('id');

        $.ajax({
            url: `/tenant/ecommerce/categories/${id}/edit`,
            type: 'GET',

            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: response.data.name,
                        text: response.data.is_active ? 'Active Category' : 'Inactive Category',
                        icon: 'info'
                    });
                }
            },

            error: function () {
                toastr.error('Failed to load category!');
            }
        });
    });

    // ================= RESET =================
    function resetForm() {
        $('#categoryName').val('');
        $('#categoryIsActive').prop('checked', true);
        editCategoryId = null;
    }

    $('#addCategoryModal').on('hidden.bs.modal', resetForm);

});
</script

@endsection
