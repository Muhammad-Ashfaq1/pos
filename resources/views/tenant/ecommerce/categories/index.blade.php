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

                <tbody id="operationTableBody" class="table-border-bottom-0 scroll-y">

                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $category->name }}</td>

                            <td class="text-center">
                                @if($category->is_active)
                                    <span class="badge bg-label-success me-1">Active</span>
                                @else
                                    <span class="badge bg-label-danger me-1">Inactive</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">

                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-eye"></i>
                                    </a>

                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-edit icon-md"></i>
                                    </a>

                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-trash icon-md text-danger"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.categories.category-modal')
@endsection


@section('scripts')

<script>
$(document).ready(function () {

    let editCategoryId = null;


    const table = $('#category-table').DataTable();


    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: "3000"
    };


    $(document).on('click', '#saveCategoryButton', function (e) {
        e.preventDefault();

        const name = $('#categoryName').val().trim();
        const is_active = $('#categoryIsActive').is(':checked') ? 1 : 0;

        const url = editCategoryId
            ? `/tenant/ecommerce/categories/${editCategoryId}`
            : "{{ route('tenant.ecommerce.categories.store') }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: editCategoryId ? 'PUT' : 'POST',
                name,
                is_active
            },

            success: function (response) {

                $('#addCategoryModal').modal('hide');
                resetForm();

                toastr.success(response.message ?? 'Category saved successfully');

                location.reload(); // optional (better: use DataTable add row)
            },

            error: function (xhr) {

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let message = Object.values(errors).map(e => e[0]).join('\n');
                    toastr.error(message);
                } else {
                    toastr.error('Something went wrong!');
                }
            }
        });
    });


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


    $(document).on('click', '.delete-operation', function () {

        const id = $(this).data('id');
        const row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#d33',
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

                        // remove row safely
                        table.row(row).remove().draw();

                        Swal.fire('Deleted!', response.message, 'success');
                    }
                },

                error: function () {
                    toastr.error('Delete failed!');
                }
            });
        });
    });


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


    function resetForm() {
        $('#categoryName').val('');
        $('#categoryIsActive').prop('checked', true);
        editCategoryId = null;
    }

    $('#addCategoryModal').on('hidden.bs.modal', resetForm);

});
</script>
@endsection
