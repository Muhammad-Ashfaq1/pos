@extends('layouts.app')

@section('title', 'Discount Groups')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Discount Groups</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Discount Groups</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Customer discount group</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountGroupModal">
                <i class="ti tabler-plus me-1"></i> Add New group
            </button>
        </div>
        <div class="card-datatable table-responsive p-5">
            <table class="discount-groups-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>Title Name</th>
                        <th>Slug</th>
                        <th>Discount Value</th>
                        <th>Type</th>
                        <th>Is Active</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data will be loaded via AJAX --}}
                </tbody>
            </table>
        </div>
    </div>



    @include('tenant.ecommerce.discounts.group.add-discount-modal')
@endsection

@section('scripts')
    <script>
        $(function() {
            // Initialize Select2 dropdowns
            if (typeof $.fn.select2 === 'function') {
                $('.select2').each(function() {
                    const $this = $(this);
                    const dropdownParentSelector = $this.data('dropdown-parent');

                    if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
                        $this.wrap('<div class="position-relative"></div>');
                    }

                    $this.select2({
                        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this
                            .parent(),
                        placeholder: $this.data('placeholder'),
                        allowClear: Boolean($this.data('allow-clear')),
                        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
                    });
                });
            }
        });


        // Handle form submission
        $('#addDiscountGroupForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: '{{ route('tenant.discounts.group.store') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#addDiscountGroupModal').modal('hide');
                    $('#addDiscountGroupForm')[0].reset();

                    // Reload DataTable or show success message
                    if (typeof discountGroupsTable !== 'undefined') {
                        discountGroupsTable.ajax.reload();
                    }

                    alert('Discount group added successfully!');
                },
                error: function(xhr) {
                    // Handle validation errors or other errors
                    console.log(xhr.responseJSON);
                }
            });
        });
    </script>
@endsection
