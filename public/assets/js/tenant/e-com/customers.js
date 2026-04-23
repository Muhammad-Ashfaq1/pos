(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#customerModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#customerForm');
  const $submitButton = $('#customerSubmitBtn');
  const $table = $('.customers-datatables');
  let customerTable = null;

  const customerEditUrl = function (customerId) {
    return (window.customerEditUrlTemplate || '').replace('__CUSTOMER__', customerId);
  };

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
    }
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const money = function (value) {
    const amount = Number(value || 0);
    return '$' + amount.toFixed(2);
  };

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#customer_id').val());
    const defaultText = isEdit ? $submitButton.data('update-text') : $submitButton.data('create-text');

    if (typeof window.appSetButtonLoading === 'function') {
      window.appSetButtonLoading($submitButton, loading, 'Saving...', defaultText);
      return;
    }

    $submitButton.prop('disabled', loading).text(loading ? 'Saving...' : defaultText);
  };

  const setSelect2ErrorState = function ($element, invalid) {
    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  const resetValidationState = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    setSelect2ErrorState($('#customer_type'), false);
  };

  const initStaticSelect2 = function () {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $('.select2').each(function () {
      const $this = $(this);

      if ($this.data('select2')) {
        return;
      }

      const dropdownParentSelector = $this.data('dropdown-parent');

      if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
        $this.wrap('<div class="position-relative"></div>');
      }

      $this.select2({
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      }).on('change', function () {
        setSelect2ErrorState($this, false);
        $this.closest('.position-relative').find('.invalid-feedback').text('');
      });
    });
  };

  const resetForm = function () {
    $form[0].reset();
    $('#customer_id').val('');
    $('#customer_type').val('registered').trigger('change');
    $('#customer_total_visits').val(0);
    $('#customer_lifetime_value').val('0.00');
    $('#customer_loyalty_points_balance').val(0);
    $('#customer_credit_balance').val('0.00');
    $('#customerModalLabel').text('Add Customer');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function (customer) {
    $('#customer_id').val(customer.id);
    $('#customer_type').val(customer.customer_type).trigger('change');
    $('#customer_name').val(customer.name);
    $('#customer_phone').val(customer.phone);
    $('#customer_email').val(customer.email);
    $('#customer_date_of_birth').val(customer.date_of_birth);
    $('#customer_last_visit_at').val(customer.last_visit_at_form);
    $('#customer_address').val(customer.address);
    $('#customer_notes').val(customer.notes);
    $('#customer_total_visits').val(customer.total_visits);
    $('#customer_lifetime_value').val(customer.lifetime_value);
    $('#customer_loyalty_points_balance').val(customer.loyalty_points_balance);
    $('#customer_credit_balance').val(customer.credit_balance);
    $('#customerModalLabel').text('Edit Customer');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center gap-1">';

    if (row.vehicles_index_url) {
      html +=
        '<a href="' + row.vehicles_index_url + '" class="btn btn-icon btn-text-secondary rounded-pill waves-effect" title="View Vehicles">' +
        '<i class="icon-base ti tabler-car icon-md"></i>' +
        '</a>';
    }

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-customer-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || customerEditUrl(row.id)) + '" title="Edit">' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-customer-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.name) + '" title="Delete">' +
        '<i class="icon-base ti tabler-trash icon-md text-danger"></i>' +
        '</button>';
    }

    html += '</div>';

    return html;
  };

  const bindFormValidation = function () {
    if (typeof $.fn.validate !== 'function') {
      return null;
    }

    return $form.validate({
      ignore: [],
      rules: {
        customer_type: { required: true },
        name: { required: true, maxlength: 150 },
        phone: { maxlength: 30 },
        email: { email: true, maxlength: 150 },
        address: { maxlength: 1000 },
        notes: { maxlength: 2000 },
        total_visits: { number: true, min: 0 },
        lifetime_value: { number: true },
        loyalty_points_balance: { number: true, min: 0 },
        credit_balance: { number: true }
      },
      messages: {
        customer_type: { required: 'Please select a customer type.' },
        name: { required: 'Please enter a customer name.', maxlength: 'The customer name may not be greater than 150 characters.' },
        email: { email: 'Please enter a valid email address.' }
      },
      errorElement: 'div',
      errorClass: 'invalid-feedback',
      highlight: function (element) {
        const $element = $(element);
        $element.addClass('is-invalid');
        if ($element.hasClass('select2-hidden-accessible')) {
          setSelect2ErrorState($element, true);
        }
      },
      unhighlight: function (element) {
        const $element = $(element);
        $element.removeClass('is-invalid');
        if ($element.hasClass('select2-hidden-accessible')) {
          setSelect2ErrorState($element, false);
        }
      },
      errorPlacement: function (error, element) {
        const $element = $(element);
        if ($element.hasClass('select2-hidden-accessible')) {
          $element.closest('.position-relative').find('.invalid-feedback').first().text(error.text());
          return;
        }

        const $feedback = $element.siblings('.invalid-feedback').first();
        if ($feedback.length) {
          $feedback.text(error.text());
          return;
        }

        error.insertAfter(element);
      }
    });
  };

  const initDataTable = function () {
    if (typeof DataTable === 'undefined' || !$table.length) {
      return;
    }

    customerTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.customerListingUrl,
        data: function (d) {
          d.customer_type = $('#customer_type_filter').val();
          d.sort = $('#customer_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name, phone, email or vehicle',
            text: '_INPUT_',
            className: 'form-control'
          }
        },
        topEnd: null,
        bottomStart: {
          rowClass: 'row mx-3 my-md-0 me-3 ms-0 justify-content-between',
          features: [
            'info',
            { pageLength: { menu: [10, 25, 50, 100], text: '_MENU_' } }
          ]
        },
        bottomEnd: 'paging'
      },
      language: {
        emptyTable: 'No customers found',
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      columns: [
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (data, type, row, meta) {
            return meta.settings._iDisplayStart + meta.row + 1;
          }
        },
        {
          data: 'name',
          render: function (data, type, row) {
            let html = '<div><span class="fw-semibold">' + escapeHtml(data) + '</span>';
            if (row.default_vehicle_plate) {
              html += '<div class="small text-muted">Default Vehicle: ' + escapeHtml(row.default_vehicle_plate) + '</div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: 'customer_type_label',
          render: function (data) {
            return '<span class="badge bg-label-primary">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            const phone = row.phone ? escapeHtml(row.phone) : '—';
            const email = row.email ? escapeHtml(row.email) : '—';
            return '<div><div>' + phone + '</div><div class="small text-muted">' + email + '</div></div>';
          }
        },
        {
          data: 'vehicles_count',
          render: function (data) {
            return '<span class="badge bg-label-info">' + escapeHtml(String(data ?? 0)) + '</span>';
          }
        },
        { data: 'total_visits' },
        {
          data: 'lifetime_value',
          render: function (data) {
            return '<span class="text-nowrap">' + money(data) + '</span>';
          }
        },
        {
          data: 'last_visit_at_label',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-center',
          render: function (data, type, row) {
            return actionButtonsHtml(row);
          }
        }
      ]
    });
  };

  const renderValidationErrors = function (errors) {
    Object.entries(errors || {}).forEach(function (entry) {
      const field = entry[0];
      const message = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
      const $element = $form.find('[name="' + field + '"]');

      if (!$element.length) {
        return;
      }

      $element.addClass('is-invalid');

      if ($element.hasClass('select2-hidden-accessible')) {
        setSelect2ErrorState($element, true);
        $element.closest('.position-relative').find('.invalid-feedback').first().text(message);
        return;
      }

      const $feedback = $element.siblings('.invalid-feedback').first();
      if ($feedback.length) {
        $feedback.text(message);
      }
    });
  };

  const bindFilters = function () {
    $('#customer_type_filter, #customer_sort').on('change', function () {
      if (customerTable) {
        customerTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addCustomerBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-customer-btn', function () {
      const editUrl = $(this).data('edit-url') || customerEditUrl($(this).data('id'));

      resetForm();
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading customer...');
      }

      $.get(editUrl)
        .done(function (response) {
          fillForm(response.data || {});
          if (modal) {
            modal.show();
          }
        })
        .fail(function (xhr) {
          showAlert('error', xhr.responseJSON?.message || 'Unable to load customer.');
        })
        .always(function () {
          if (window.appLoading && typeof window.appLoading.hide === 'function') {
            window.appLoading.hide(200);
          }
        });
    });

    $modal.on('hidden.bs.modal', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });
  };

  const bindSaveForm = function (validator) {
    $form.on('submit', function (event) {
      event.preventDefault();
      resetValidationState();

      if (validator && !$form.valid()) {
        return;
      }

      setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving customer...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          showAlert('success', response.message || 'Customer saved successfully.');
          if (modal) {
            modal.hide();
          }
          if (customerTable) {
            customerTable.ajax.reload(null, false);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 422) {
            renderValidationErrors(xhr.responseJSON?.errors || {});
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save customer.');
        })
        .always(function () {
          setSubmitButtonState(false);
          if (window.appLoading && typeof window.appLoading.hide === 'function') {
            window.appLoading.hide(200);
          }
        });
    });
  };

  const bindDeleteActions = function () {
    $(document).on('click', '.delete-customer-btn', function () {
      const url = $(this).data('url');
      const name = $(this).data('name') || 'this customer';

      Swal.fire({
        title: 'Delete Customer?',
        text: 'This will also remove linked vehicles for ' + name + '.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (!result.isConfirmed) {
          return;
        }

        if (window.appLoading && typeof window.appLoading.show === 'function') {
          window.appLoading.show('Deleting customer...');
        }

        $.ajax({
          url: url,
          method: 'DELETE'
        })
          .done(function (response) {
            showAlert('success', response.message || 'Customer deleted successfully.');
            if (customerTable) {
              customerTable.ajax.reload(null, false);
            }
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete customer.');
          })
          .always(function () {
            if (window.appLoading && typeof window.appLoading.hide === 'function') {
              window.appLoading.hide(200);
            }
          });
      });
    });
  };

  $(function () {
    initStaticSelect2();
    const validator = bindFormValidation();
    initDataTable();
    bindFilters();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteActions();
    resetForm();
  });
})(window.jQuery);
