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
                                <i class="ti tabler-plus me-1"></i>Create Category
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
             <tbody id="categories-table-body">
                @include('tenant.ecommerce.categories.data-table')
            </tbody>
        </table>
    </div>
    <!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
             <h5 class="modal-title" id="addCategoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" id="addCategoryForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input
                            name="name"
                            type="text"
                            id="categoryName"
                            class="form-control"
                            placeholder="Enter category name"
                            required
                        >
                        <div class="invalid-feedback">
                            Category name is required.
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="categoryIsActive"
                            name="is_active"
                            value="1"
                            checked
                        >
                        <label class="form-check-label" for="categoryIsActive">Status (Active)</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button class="btn btn-primary" type="button" id="saveCategoryButton">
                     Save Category
                </button>
                </div>
            </form>
        </div>
    </div>
</div>





@endsection


@section('scripts')


<script>
$(document).ready(function () {

    let editCategoryId = null;

    const table = $('#category-table').DataTable();

    // ================= SAVE (CREATE / UPDATE) =================
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
                name: name,
                is_active: is_active
            },

       success: function(response) {
    if(response.success){

        $('#addCategoryModal').modal('hide');

        $('#categories-table-body').html(response.html);


        toastr.success(response.message);
    }
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

                    $('#addCategoryModalTitle').text('Edit Category');
                    $('#addCategoryModal').modal('show');
                }
            },

            error: function (xhr) {
            console.log(xhr.responseText);
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
        $('#addCategoryModalTitle').text('Add Category');
    }

    $('#addCategoryModal').on('hidden.bs.modal', resetForm);

});
</script>

@endsection
