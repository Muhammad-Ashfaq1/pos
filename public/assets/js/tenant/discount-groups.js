$(function () {
    // Initialize Select2 dropdowns
    if (typeof $.fn.select2 === 'function') {
        $('.select2').each(function () {
            const $this = $(this);
            const dropdownParentSelector = $this.data('dropdown-parent');

            if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
                $this.wrap('<div class="position-relative"></div>');
            }

            $this.select2({
                dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
                placeholder: $this.data('placeholder'),
                allowClear: Boolean($this.data('allow-clear')),
                minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
            });
        });
    }

    // Handle form submission
    $('#addDiscountGroupForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const formData = $form.serialize();
        const id = $('#discount_group_id').val();

        let url = $form.data('store-url');
        if (id) {
            url = $form.data('update-url').replace(':id', id);
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#addDiscountGroupModal').modal('hide');
                const isUpdate = id !== '';
                $form[0].reset();

                const rowHtml = `
                    <td>${response.data.name}</td>
                    <td>${response.data.slug}</td>
                    <td>${response.data.type === 'percentage' ? response.data.value + '%' : '$' + response.data.value}</td>
                    <td>${response.data.type}</td>
                    <td><span class="badge bg-label-success">Yes</span></td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a href="javascript:void(0);" class="text-primary edit-discount-group"
                                data-id="${response.data.id}"
                                data-title="${response.data.name}"
                                data-type="${response.data.type}"
                                data-value="${response.data.value}"
                            ><i class="ti tabler-edit"></i></a>
                            <a href="javascript:void(0);" class="text-danger delete-discount-group" 
                                data-id="${response.data.id}"
                                data-url="${$('#discount-groups-body').closest('table').data('delete-url-pattern').replace(':id', response.data.id)}"
                            ><i class="ti tabler-trash"></i></a>
                        </div>
                    </td>
                `;

                if (isUpdate) {
                    // Update existing row
                    $(`.edit-discount-group[data-id="${response.data.id}"]`).closest('tr').html(rowHtml);
                } else {
                    // Prepend new row
                    $('#discount-groups-body').prepend(`<tr>${rowHtml}</tr>`);
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            },
            error: function (xhr) {
                console.log(xhr.responseJSON);
            }
        });
    });

    // Reset modal when clicking "Add New group"
    $('[data-bs-target="#addDiscountGroupModal"]').on('click', function () {
        $('#addDiscountGroupForm')[0].reset();
        $('#discount_group_id').val('');
        $('#form_method').val(''); // Clear PUT method
        $('#addDiscountGroupModalLabel').text('Customer Discount Group');
        $('.add-discount-group').text('Save');
        $('#discount_type').val('').trigger('change');
    });

    // Handle discount group edit
    $(document).on('click', '.edit-discount-group', function () {
        const $this = $(this);
        const id = $this.data('id');
        const title = $this.data('title');
        const type = $this.data('type');
        const value = $this.data('value');

        // Populate modal
        $('#discount_group_id').val(id);
        $('#form_method').val('PUT'); // Set method to PUT for update
        $('#group_title').val(title);
        $('#discount_type').val(type).trigger('change');
        $('#discount_value').val(value);

        // Update modal UI
        $('#addDiscountGroupModalLabel').text('Edit Discount Group');
        $('.add-discount-group').text('Update');

        const modalElement = document.getElementById('addDiscountGroupModal');
        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modal.show();
    });

    $(document).on('click', '.delete-discount-group', function () {
        const $this = $(this);
        const id = $this.data('id');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: `You want to delete this discount group`,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $this.data('url'),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            $this.closest('tr').remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
                }
            });
        }
    });
});
