(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#vehicleModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#vehicleForm');
  const $submitButton = $('#vehicleSubmitBtn');
  const $table = $('.vehicles-datatables');
  const $formCustomer = $('#vehicle_customer_id');
  const $filterCustomer = $('#vehicle_filter_customer');
  const initialCustomerId = new URLSearchParams(window.location.search).get('customer_id') || '';
  let vehicleTable = null;

  const vehicleEditUrl = function (vehicleId) {
    return (window.vehicleEditUrlTemplate || '').replace('__VEHICLE__', vehicleId);
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

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#vehicle_id').val());
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
    setSelect2ErrorState($formCustomer, false);
  };

  const ensureSelectOption = function ($select, id, text) {
    if (!id) {
      $select.val(null).trigger('change');
      return;
    }

    let option = $select.find('option[value="' + id + '"]');
    if (!option.length) {
      option = new Option(text || 'Selected item', id, true, true);
      $select.append(option);
    }

    $select.val(String(id)).trigger('change');
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

      if (!$this.parent().hasClass('position-relative')) {
        $this.wrap('<div class="position-relative"></div>');
      }

      $this.select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      });
    });
  };

  const initCustomerSelect = function ($element) {
    if (typeof $.fn.select2 !== 'function' || !$element.length || $element.data('select2')) {
      return;
    }

    const dropdownParentSelector = $element.data('dropdown-parent');

    if (!dropdownParentSelector && !$element.parent().hasClass('position-relative')) {
      $element.wrap('<div class="position-relative"></div>');
    }

    $element.select2({
      dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $element.parent(),
      placeholder: $element.data('placeholder'),
      allowClear: Boolean($element.data('allow-clear')),
      ajax: {
        url: window.customerDropdownUrl,
        delay: 250,
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term || '',
            page: params.page || 1
          };
        },
        processResults: function (response, params) {
          params.page = params.page || 1;

          return {
            results: response.results || [],
            pagination: response.pagination || { more: false }
          };
        }
      }
    }).on('change', function () {
      setSelect2ErrorState($element, false);
      $element.closest('.position-relative').find('.invalid-feedback').text('');
    });
  };

  const resetForm = function () {
    $form[0].reset();
    $('#vehicle_id').val('');
    ensureSelectOption($formCustomer, initialCustomerId || null, null);
    $('#vehicle_is_default').prop('checked', false);
    $('#vehicleModalLabel').text('Add Vehicle');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function (vehicle) {
    $('#vehicle_id').val(vehicle.id);
    ensureSelectOption($formCustomer, vehicle.customer_id, vehicle.customer_name);
    $('#vehicle_plate_number').val(vehicle.plate_number);
    $('#vehicle_registration_number').val(vehicle.registration_number);
    $('#vehicle_make').val(vehicle.make);
    $('#vehicle_model').val(vehicle.model);
    $('#vehicle_year').val(vehicle.year);
    $('#vehicle_color').val(vehicle.color);
    $('#vehicle_engine_type').val(vehicle.engine_type);
    $('#vehicle_odometer').val(vehicle.odometer);
    $('#vehicle_notes').val(vehicle.notes);
    $('#vehicle_is_default').prop('checked', Boolean(vehicle.is_default));
    $('#vehicleModalLabel').text('Edit Vehicle');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center">';

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-vehicle-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || vehicleEditUrl(row.id)) + '" title="Edit">' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-vehicle-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.plate_number) + '" title="Delete">' +
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
        customer_id: { required: true },
        plate_number: { required: true, maxlength: 50 },
        registration_number: { maxlength: 80 },
        make: { maxlength: 100 },
        model: { maxlength: 100 },
        year: { number: true, min: 1900 },
        color: { maxlength: 50 },
        engine_type: { maxlength: 80 },
        odometer: { number: true, min: 0 },
        notes: { maxlength: 2000 }
      },
      messages: {
        customer_id: { required: 'Please select a customer.' },
        plate_number: { required: 'Please enter a plate number.' }
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

    vehicleTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.vehicleListingUrl,
        data: function (d) {
          d.customer_id = $filterCustomer.val() || initialCustomerId;
          d.is_default = $('#vehicle_default_filter').val();
          d.sort = $('#vehicle_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by customer, plate or vehicle details',
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
        emptyTable: 'No vehicles found',
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
          data: 'customer_name',
          render: function (data, type, row) {
            let html = '<div><span class="fw-semibold">' + escapeHtml(data || '—') + '</span>';
            if (row.customer_phone) {
              html += '<div class="small text-muted">' + escapeHtml(row.customer_phone) + '</div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: 'plate_number',
          render: function (data) {
            return '<span class="fw-semibold">' + escapeHtml(data) + '</span>';
          }
        },
        {
          data: 'registration_number',
          render: function (data) {
            return escapeHtml(data || '—');
          }
        },
        {
          data: 'vehicle_label',
          render: function (data, type, row) {
            let html = '<div>' + escapeHtml(data || 'Vehicle') + '</div>';
            const meta = [row.color, row.engine_type].filter(Boolean).join(' • ');
            if (meta) {
              html += '<div class="small text-muted">' + escapeHtml(meta) + '</div>';
            }
            return html;
          }
        },
        {
          data: 'odometer',
          render: function (data) {
            return escapeHtml(data ? data + ' km' : '—');
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            return '<span class="badge ' + row.default_badge_class + '">' + escapeHtml(row.default_label) + '</span>';
          }
        },
        {
          data: 'created_at',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '') + '</span>';
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
    $('#vehicle_default_filter, #vehicle_sort').on('change', function () {
      if (vehicleTable) {
        vehicleTable.ajax.reload(null, false);
      }
    });

    $filterCustomer.on('change', function () {
      if (vehicleTable) {
        vehicleTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addVehicleBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-vehicle-btn', function () {
      const editUrl = $(this).data('edit-url') || vehicleEditUrl($(this).data('id'));

      resetForm();
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading vehicle...');
      }

      $.get(editUrl)
        .done(function (response) {
          fillForm(response.data || {});
          if (modal) {
            modal.show();
          }
        })
        .fail(function (xhr) {
          showAlert('error', xhr.responseJSON?.message || 'Unable to load vehicle.');
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
        window.appLoading.show('Saving vehicle...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          showAlert('success', response.message || 'Vehicle saved successfully.');
          if (modal) {
            modal.hide();
          }
          if (vehicleTable) {
            vehicleTable.ajax.reload(null, false);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 422) {
            renderValidationErrors(xhr.responseJSON?.errors || {});
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save vehicle.');
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
    $(document).on('click', '.delete-vehicle-btn', function () {
      const url = $(this).data('url');
      const name = $(this).data('name') || 'this vehicle';

      Swal.fire({
        title: 'Delete Vehicle?',
        text: 'This action will permanently remove ' + name + '.',
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
          window.appLoading.show('Deleting vehicle...');
        }

        $.ajax({
          url: url,
          method: 'DELETE'
        })
          .done(function (response) {
            showAlert('success', response.message || 'Vehicle deleted successfully.');
            if (vehicleTable) {
              vehicleTable.ajax.reload(null, false);
            }
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete vehicle.');
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
    initCustomerSelect($formCustomer);
    initCustomerSelect($filterCustomer);
    if (initialCustomerId) {
      $filterCustomer.val(initialCustomerId);
    }
    const validator = bindFormValidation();
    initDataTable();
    bindFilters();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteActions();
    resetForm();
  });
})(window.jQuery);
