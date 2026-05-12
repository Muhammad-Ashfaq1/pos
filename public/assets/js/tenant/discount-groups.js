(function ($) {
    'use strict';

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $modal = $('#addDiscountGroupModal');
    const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
    const $form = $('#addDiscountGroupForm');
    const $submitButton = $('.add-discount-group');
    const $table = $('#discountGroupsTable');
    let discountGroupsTable = null;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json'
        }
    });

    const showAlert = function (type, message) {
        if (typeof window.appNotify === 'function') {
            window.appNotify(type, message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success' : 'Error',
                text: message,
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        }
    };

    const setSubmitButtonState = function (loading) {
        const id = $('#discount_group_id').val();
        const defaultText = id ? 'Update' : 'Save';

        if (typeof window.appSetButtonLoading === 'function') {
            window.appSetButtonLoading($submitButton, loading, 'Saving...', defaultText);
            return;
        }

        if (loading) {
            $submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
            return;
        }

        $submitButton.prop('disabled', false).text(defaultText);
    };

    const resetValidationState = function () {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');
    };

    const formatMoney = function (value) {
        return '$' + (Number(value || 0)).toFixed(2);
    };

    const formatDiscountValue = function (group) {
        return group.type === 'percentage'
            ? Number(group.value || 0).toFixed(2) + '%'
            : formatMoney(group.value);
    };

    const formatMinLimit = function (group) {
        return group.type === 'fixed' && group.min_limit !== null && group.min_limit !== undefined
            ? formatMoney(group.min_limit)
            : '-';
    };

    const formatActiveBadge = function (isActive) {
        return isActive
            ? '<span class="badge bg-label-success">Yes</span>'
            : '<span class="badge bg-label-danger">No</span>';
    };

    const toggleMinLimit = function () {
        const isFixed = $('#discount_type').val() === 'fixed';
        $('#discount_min_limit')
            .prop('disabled', !isFixed)
            .toggleClass('bg-label-secondary', !isFixed);

        if (!isFixed) {
            $('#discount_min_limit').val('');
        }
    };

    const initDataTable = function () {
        if (typeof DataTable === 'undefined' || !$table.length) {
            return;
        }

        discountGroupsTable = new DataTable($table[0], {
            order: [[0, 'asc']],
            dom: '<"row mx-2"' +
                '<"col-md-2"<"me-3"l>>' +
                '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"f>>' +
                '>t' +
                '<"row mx-2"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                '>',
            language: {
                sLengthMenu: '_MENU_',
                search: '',
                searchPlaceholder: 'Search Group'
            },
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ]
        });
    };

    const resetForm = function () {
        $form[0].reset();
        $('#discount_group_id').val('');
        $('#form_method').val('');
        $('#addDiscountGroupModalLabel').text('Customer Discount Group');
        $submitButton.text('Save');
        $('#discount_type').val('').trigger('change');
        toggleMinLimit();
        resetValidationState();
    };

    // Initialize Select2
    if (typeof $.fn.select2 === 'function') {
        $('.select2').each(function () {
            const $this = $(this);
            const dropdownParentSelector = $this.data('dropdown-parent');
            $this.select2({
                dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
                placeholder: $this.data('placeholder'),
                allowClear: Boolean($this.data('allow-clear')),
                minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
            });
        });
    }

    // Event Handlers
    $(document).on('change', '#discount_type', toggleMinLimit);

    $('[data-bs-target="#addDiscountGroupModal"]').on('click', function () {
        resetForm();
    });

    $(document).on('click', '.edit-discount-group', function () {
        const $this = $(this);
        const id = $this.data('id');
        const title = $this.data('title');
        const type = $this.data('type');
        const value = $this.data('value');
        const minLimit = $this.data('min-limit');
        const isActive = Number($this.data('is-active')) === 1;

        resetValidationState();
        $('#discount_group_id').val(id);
        $('#form_method').val('PUT');
        $('#group_title').val(title);
        $('#discount_type').val(type).trigger('change');
        $('#discount_value').val(value);
        $('#discount_min_limit').val(minLimit || '');
        $('#discount_group_is_active').prop('checked', isActive);
        toggleMinLimit();

        $('#addDiscountGroupModalLabel').text('Edit Discount Group');
        $submitButton.text('Update');

        if (modal) {
            modal.show();
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        resetValidationState();
        setSubmitButtonState(true);

        const id = $('#discount_group_id').val();
        let url = $form.data('store-url');
        if (id) {
            url = $form.data('update-url').replace(':id', id);
        }

        if (window.appLoading && typeof window.appLoading.show === 'function') {
            window.appLoading.show('Saving discount group...');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: $form.serialize()
        })
            .done(function (response) {
                if (modal) {
                    modal.hide();
                }
                
                showAlert('success', response.message || 'Discount group saved successfully.');

                const rowHtml = `
                    <td>${response.data.name}</td>
                    <td>${response.data.slug}</td>
                    <td>${formatDiscountValue(response.data)}</td>
                    <td>${response.data.type}</td>
                    <td>${formatMinLimit(response.data)}</td>
                    <td>${formatActiveBadge(Boolean(response.data.is_active))}</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="btn btn-icon btn-text-primary rounded-pill waves-effect edit-discount-group"
                                data-id="${response.data.id}"
                                data-title="${response.data.name}"
                                data-type="${response.data.type}"
                                data-value="${response.data.value}"
                                data-min-limit="${response.data.min_limit || ''}"
                                data-is-active="${response.data.is_active ? 1 : 0}"
                                data-bs-toggle="tooltip" data-bs-custom-class="tooltip-primary" title="Edit"
                            ><i class="icon-base ti tabler-edit icon-md"></i></button>
                            <button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-discount-group"
                                data-id="${response.data.id}"
                                data-url="${$('#discount-groups-body').closest('table').data('delete-url-pattern').replace(':id', response.data.id)}"
                                data-name="${response.data.name}"
                                data-bs-toggle="tooltip" data-bs-custom-class="tooltip-primary" title="Delete"
                            ><i class="icon-base ti tabler-trash icon-md text-danger"></i></button>
                        </div>
                    </td>
                `;

                if (id) {
                    const $tr = $(`.edit-discount-group[data-id="${id}"]`).closest('tr');
                    if (discountGroupsTable) {
                        discountGroupsTable.row($tr).node().innerHTML = rowHtml;
                        discountGroupsTable.row($tr).invalidate().draw(false);
                    } else {
                        $tr.html(rowHtml);
                    }
                } else {
                    if (discountGroupsTable) {
                        discountGroupsTable.row.add($(`<tr>${rowHtml}</tr>`)).draw(false);
                    } else {
                        $('#discount-groups-body').prepend(`<tr>${rowHtml}</tr>`);
                    }
                }
                
                if (window.Helpers && window.Helpers.initToolTip) {
                    window.Helpers.initToolTip(document);
                }
            })
            .fail(function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    let firstMessage = '';
                    
                    Object.keys(errors).forEach(function (key) {
                        // Handle field highlighting
                        let fieldName = key;
                        if (key === 'title') fieldName = 'name'; // adjust if needed
                        
                        const $input = $(`[name="${fieldName}"], #group_title`); // match common field IDs
                        $input.addClass('is-invalid');
                        $input.siblings('.invalid-feedback').text(errors[key][0]);
                        if (!firstMessage) firstMessage = errors[key][0];
                    });
                    
                    showAlert('error', firstMessage || 'Please fix the validation errors.');
                } else {
                    showAlert('error', xhr.responseJSON?.message || 'Unable to save discount group.');
                }
            })
            .always(function () {
                setSubmitButtonState(false);
                if (window.appLoading && typeof window.appLoading.hide === 'function') {
                    window.appLoading.hide(200);
                }
            });
    });

    $(document).on('click', '.delete-discount-group', function () {
        const $this = $(this);
        const name = $this.data('name') || 'this group';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete discount group?',
                text: 'This will remove "' + name + '" from the system.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    if (window.appLoading && typeof window.appLoading.show === 'function') {
                        window.appLoading.show('Deleting...');
                    }

                    $.ajax({
                        url: $this.data('url'),
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        }
                    })
                        .done(function (response) {
                            if (discountGroupsTable) {
                                discountGroupsTable.row($this.closest('tr')).remove().draw(false);
                            } else {
                                $this.closest('tr').remove();
                            }
                            showAlert('success', response.message || 'Deleted successfully.');
                        })
                        .fail(function (xhr) {
                            showAlert('error', xhr.responseJSON?.message || 'Unable to delete.');
                        })
                        .always(function () {
                            if (window.appLoading && typeof window.appLoading.hide === 'function') {
                                window.appLoading.hide(200);
                            }
                        });
                }
            });
        }
    });

    initDataTable();
    toggleMinLimit();

    if (window.Helpers && window.Helpers.initToolTip) {
        window.Helpers.initToolTip(document);
    }

})(jQuery);
