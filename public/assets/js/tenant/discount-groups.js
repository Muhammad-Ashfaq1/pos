$(function () {
    let discountGroupsTable = null;

    const initDataTable = function () {
        const $table = $('#discountGroupsTable');
        if (typeof DataTable === 'undefined' || !$table.length) {
            return;
        }

        discountGroupsTable = new DataTable($table[0], {
            order: [[0, 'asc']],
            dom: '<"row"' +
                '<"col-md-12 d-flex justify-content-start"f>' +
                '>t' +
                '<"row"' +
                '<"col-sm-12 col-md-6 d-flex align-items-center justify-content-start"i<"ms-3"l>>' +
                '<"col-sm-12 col-md-6 d-flex justify-content-end"p>' +
                '>',
            language: {
                sLengthMenu: '_MENU_',
                search: '',
                searchPlaceholder: 'Search Group'
            },
            columnDefs: [
                {
                    // Actions column
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ]
        });
    };

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

                const currencySymbol = (window.appCurrencySymbol && window.appCurrencySymbol()) ||
                    (window.appCurrency && window.appCurrency.symbol) ||
                    '$';
                const rowHtml = `
                    <td>${response.data.name}</td>
                    <td>${response.data.slug}</td>
                    <td>${response.data.type === 'percentage' ? response.data.value + '%' : currencySymbol + response.data.value}</td>
                    <td>${response.data.type}</td>
                    <td>${response.data.type === 'fixed' ? currencySymbol + response.data.min_limit : '-'}</td>
                    <td>${response.data.earns_credit
                        ? `<span class="badge rounded bg-label-info">${response.data.credit_earn_type === 'percentage' ? response.data.credit_earn_rate + '%' : currencySymbol + response.data.credit_earn_rate}</span>`
                        : '<span class="text-muted">-</span>'}</td>
                    <td class="text-center">
                        <span class="badge rounded bg-label-${response.data.is_active ? 'success' : 'danger'}">${response.data.is_active
                        ? 'Yes'
                        : 'No'}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-discount-group"
                                data-id="${response.data.id}"
                                data-title="${response.data.name}"
                                data-type="${response.data.type}"
                                data-value="${response.data.value}"
                                data-min-value="${response.data.min_limit}"
                                data-is-active="${response.data.is_active}"
                                data-earns-credit="${response.data.earns_credit ? 1 : 0}"
                                data-credit-earn-type="${response.data.credit_earn_type || 'percentage'}"
                                data-credit-earn-rate="${response.data.credit_earn_rate || 0}"
                                data-credit-min-spend="${response.data.credit_min_spend || 0}"
                                title="Edit"
                            ><i class="icon-base ti tabler-edit icon-md"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-discount-group"
                                data-id="${response.data.id}"
                                data-url="${$('#discount-groups-body').closest('table').data('delete-url-pattern').replace(':id', response.data.id)}"
                                title="Delete"
                            ><i class="icon-base ti tabler-trash icon-md"></i></button>
                        </div>
                    </td>
                `;

                if (isUpdate) {
                    // Update existing row
                    const $tr = $(`.edit-discount-group[data-id="${response.data.id}"]`).closest('tr');
                    if (discountGroupsTable) {
                        discountGroupsTable.row($tr).node().innerHTML = rowHtml;
                        discountGroupsTable.row($tr).invalidate().draw(false);
                    } else {
                        $tr.html(rowHtml);
                    }
                } else {
                    // Prepend new row
                    if (discountGroupsTable) {
                        discountGroupsTable.row.add($(`<tr>${rowHtml}</tr>`)).draw(false);
                    } else {
                        $('#discount-groups-body').prepend(`<tr>${rowHtml}</tr>`);
                    }
                }

                if (typeof window.appNotify === 'function') {
                    window.appNotify('success', response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    const firstError = Object.values(errors)[0][0];
                    if (typeof window.appNotify === 'function') {
                        window.appNotify('error', firstError);
                    }
                } else {
                    console.log(xhr.responseJSON);
                }
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
        $('#earns_credit').prop('checked', false);
        $('#credit_earn_type').val('percentage').trigger('change');
        toggleCreditFields();
    });

    // Toggle min_value_div based on discount type
    $('#discount_type').on('change', function () {
        if ($(this).val() === 'fixed') {
            $('#min_limit_div').removeClass('d-none');
        } else {
            $('#min_limit_div').addClass('d-none');
        }
    });

    // Toggle credit-earn fields
    const toggleCreditFields = function () {
        if ($('#earns_credit').is(':checked')) {
            $('.credit-earn-field').removeClass('d-none');
        } else {
            $('.credit-earn-field').addClass('d-none');
        }
    };
    $('#earns_credit').on('change', toggleCreditFields);

    // Switch earn-rate label between % and currency symbol
    const currencySymbol = (window.appCurrencySymbol && window.appCurrencySymbol()) ||
        (window.appCurrency && window.appCurrency.symbol) ||
        '$';
    $('#credit_earn_type').on('change', function () {
        $('#credit_earn_rate_label').text(
            $(this).val() === 'fixed' ? `Earn Amount (${currencySymbol})` : 'Earn Rate (%)'
        );
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
        $('#min_limit').val($this.data('min-value'));
        $('#is_active').prop('checked', $this.data('is-active') == 1);

        // Credit-earn fields
        $('#earns_credit').prop('checked', $this.data('earns-credit') == 1);
        $('#credit_earn_type').val($this.data('credit-earn-type') || 'percentage').trigger('change');
        $('#credit_earn_rate').val($this.data('credit-earn-rate'));
        $('#credit_min_spend').val($this.data('credit-min-spend'));
        toggleCreditFields();

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
                    confirmButton: 'btn btn-danger me-3',
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
                            if (discountGroupsTable) {
                                discountGroupsTable.row($this.closest('tr')).remove().draw(false);
                            } else {
                                $this.closest('tr').remove();
                            }
                            if (typeof window.appNotify === 'function') {
                                window.appNotify('success', response.message);
                            }
                        }
                    });
                }
            });
        }
    });
    initDataTable();
});
