(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#serviceModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#serviceForm');
  const $submitButton = $('#serviceSubmitBtn');
  const $table = $('.services-datatables');
  const $formCategory = $('#service_category_id');
  const $filterCategory = $('#service_filter_category');
  const $mappingsTableBody = $('#serviceMappingsTable tbody');
  let serviceTable = null;

  const serviceEditUrl = function (serviceId) {
    if (!window.serviceEditUrlTemplate) {
      return '';
    }

    return window.serviceEditUrlTemplate.replace('__SERVICE__', serviceId);
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
    const isEdit = Boolean($('#service_id').val());
    const defaultText = isEdit ? $submitButton.data('update-text') : $submitButton.data('create-text');

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

  const setSelect2ErrorState = function ($element, invalid) {
    const $selection = $element.next('.select2').find('.select2-selection');
    $selection.toggleClass('is-invalid', invalid);
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

  const convertErrorKeyToInputName = function (key) {
    if (!key.includes('.')) {
      return key;
    }

    const parts = key.split('.');
    let inputName = parts.shift();

    parts.forEach(function (part) {
      inputName += '[' + part + ']';
    });

    return inputName;
  };

  const resetValidationState = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    setSelect2ErrorState($formCategory, false);
    $mappingsTableBody.find('.service-mapping-row').each(function () {
      const $row = $(this);
      setSelect2ErrorState($row.find('.service-product-select'), false);
    });
  };

  const initStaticSelect2 = function () {
    const $selects = $('.select2');

    if (typeof $.fn.select2 !== 'function' || !$selects.length) {
      return;
    }

    $selects.each(function () {
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
      });
    });
  };

  const initCategorySelect = function ($element) {
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
        url: window.categoryDropdownUrl,
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

  const initProductSelect = function ($element) {
    if (typeof $.fn.select2 !== 'function' || !$element.length || $element.data('select2')) {
      return;
    }

    if (!$element.parent().hasClass('position-relative')) {
      $element.wrap('<div class="position-relative"></div>');
    }

    $element.select2({
      dropdownParent: $modal,
      placeholder: $element.data('placeholder'),
      allowClear: true,
      ajax: {
        url: window.serviceProductDropdownUrl,
        delay: 250,
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term || '',
            page: params.page || 1,
            active_only: 1
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

  const mappingRowHtml = function () {
    return '' +
      '<tr class="service-mapping-row">' +
        '<td>' +
          '<div class="position-relative">' +
            '<select class="form-select service-product-select" data-placeholder="Select a product"></select>' +
            '<div class="invalid-feedback"></div>' +
          '</div>' +
        '</td>' +
        '<td>' +
          '<input type="number" step="0.001" min="0.001" class="form-control service-mapping-quantity">' +
          '<div class="invalid-feedback"></div>' +
        '</td>' +
        '<td>' +
          '<input type="text" class="form-control service-mapping-unit" maxlength="50" placeholder="Optional unit">' +
          '<div class="invalid-feedback"></div>' +
        '</td>' +
        '<td>' +
          '<input type="hidden" class="service-mapping-required-hidden" value="0">' +
          '<div class="form-check form-switch mt-2">' +
            '<input class="form-check-input service-mapping-required" type="checkbox" role="switch" value="1" checked>' +
          '</div>' +
          '<div class="invalid-feedback d-block"></div>' +
        '</td>' +
        '<td class="text-center">' +
          '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect remove-service-mapping-row">' +
            '<i class="icon-base ti tabler-trash icon-md text-danger"></i>' +
          '</button>' +
        '</td>' +
      '</tr>';
  };

  const reindexMappingRows = function () {
    $mappingsTableBody.find('.service-mapping-row').each(function (index) {
      const $row = $(this);
      $row.find('.service-product-select').attr('name', 'mappings[' + index + '][product_id]');
      $row.find('.service-mapping-quantity').attr('name', 'mappings[' + index + '][quantity]');
      $row.find('.service-mapping-unit').attr('name', 'mappings[' + index + '][unit]');
      $row.find('.service-mapping-required-hidden').attr('name', 'mappings[' + index + '][is_required]');
      $row.find('.service-mapping-required').attr('name', 'mappings[' + index + '][is_required]');
    });
  };

  const addMappingRow = function (mapping) {
    const $row = $(mappingRowHtml());
    $mappingsTableBody.append($row);

    const $productSelect = $row.find('.service-product-select');
    const $quantity = $row.find('.service-mapping-quantity');
    const $unit = $row.find('.service-mapping-unit');
    const $required = $row.find('.service-mapping-required');

    initProductSelect($productSelect);

    if (mapping && mapping.product_id) {
      const productLabel = [mapping.product_name, mapping.product_sku ? '(' + mapping.product_sku + ')' : '']
        .filter(Boolean)
        .join(' ');
      const option = new Option(productLabel || mapping.product_name || 'Selected product', mapping.product_id, true, true);
      $productSelect.append(option).trigger('change');
    }

    $quantity.val(mapping && mapping.quantity ? mapping.quantity : '');
    $unit.val(mapping && mapping.unit ? mapping.unit : '');
    $required.prop('checked', mapping ? Boolean(mapping.is_required) : true);

    reindexMappingRows();
  };

  const renderMappingRows = function (mappings) {
    $mappingsTableBody.empty();

    if (Array.isArray(mappings) && mappings.length) {
      mappings.forEach(function (mapping) {
        addMappingRow(mapping);
      });
    } else {
      addMappingRow();
    }
  };

  const resetForm = function () {
    $form[0].reset();
    $('#service_id').val('');
    $('#service_standard_price').val('0.00');
    $('#service_is_active').prop('checked', true);
    $('#service_requires_technician').prop('checked', false);
    ensureSelectOption($formCategory, null, null);
    renderMappingRows([]);
    $('#serviceModalLabel').text('Add Service');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function (service) {
    $('#service_id').val(service.id);
    ensureSelectOption($formCategory, service.category_id, service.category_name);
    $('#service_name').val(service.name);
    $('#service_code').val(service.code);
    $('#service_description').val(service.description);
    $('#service_standard_price').val(service.standard_price);
    $('#service_estimated_duration_minutes').val(service.estimated_duration_minutes);
    $('#service_tax_percentage').val(service.tax_percentage);
    $('#service_reminder_interval_days').val(service.reminder_interval_days);
    $('#service_mileage_interval').val(service.mileage_interval);
    $('#service_is_active').prop('checked', Boolean(service.is_active));
    $('#service_requires_technician').prop('checked', Boolean(service.requires_technician));
    renderMappingRows(Array.isArray(service.mappings) ? service.mappings : []);
    $('#serviceModalLabel').text('Edit Service');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center">';

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-service-btn" ' +
        'data-bs-toggle="modal" data-bs-target="#serviceModal" ' +
        'data-id="' + row.id + '" ' +
        'data-edit-url="' + escapeHtml(row.edit_url || serviceEditUrl(row.id)) + '" title="Edit">' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-service-btn" ' +
        'data-url="' + row.delete_url + '" ' +
        'data-name="' + escapeHtml(row.name) + '" title="Delete">' +
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
        name: {
          required: true,
          maxlength: 150
        },
        code: {
          maxlength: 50
        },
        description: {
          maxlength: 2000
        },
        standard_price: {
          required: true,
          number: true,
          min: 0
        },
        estimated_duration_minutes: {
          number: true,
          min: 0
        },
        tax_percentage: {
          number: true,
          min: 0,
          max: 100
        },
        reminder_interval_days: {
          number: true,
          min: 0
        },
        mileage_interval: {
          number: true,
          min: 0
        }
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

    serviceTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.serviceListingUrl,
        data: function (d) {
          d.status = $('#service_status').val();
          d.category_id = $filterCategory.val();
          d.requires_technician = $('#service_requires_technician_filter').val();
          d.sort = $('#service_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name, code, description or category',
            text: '_INPUT_',
            className: 'form-control'
          }
        },
        topEnd: null,
        bottomStart: {
          rowClass: 'row mx-3 my-md-0 me-3 ms-0 justify-content-between',
          features: [
            'info',
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            }
          ]
        },
        bottomEnd: 'paging'
      },
      language: {
        emptyTable: 'No services found',
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
          data: 'category_name',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: 'name',
          render: function (data, type, row) {
            const description = row.description ? '<small class="text-muted d-block">' + escapeHtml(row.description.length > 60 ? row.description.slice(0, 57) + '...' : row.description) + '</small>' : '';
            return '<div><span class="fw-semibold">' + escapeHtml(data) + '</span>' + description + '</div>';
          }
        },
        {
          data: 'code',
          render: function (data) {
            return escapeHtml(data || '—');
          }
        },
        {
          data: 'standard_price',
          render: function (data) {
            return '<span class="text-nowrap">' + money(data) + '</span>';
          }
        },
        {
          data: 'estimated_duration_minutes',
          render: function (data) {
            return data ? '<span class="text-nowrap">' + escapeHtml(String(data)) + ' min</span>' : '—';
          }
        },
        {
          data: 'mapped_products_count',
          render: function (data) {
            return '<span class="badge bg-label-info">' + escapeHtml(String(data || 0)) + '</span>';
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return '<span class="badge ' + row.requires_technician_badge_class + '">' + escapeHtml(row.requires_technician_label) + '</span>';
          }
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return '<span class="badge ' + row.status_badge_class + '">' + escapeHtml(row.status_label) + '</span>';
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

  const reloadTable = function () {
    if (serviceTable) {
      serviceTable.ajax.reload(null, false);
    }
  };

  const appendServerErrors = function (errors, validator) {
    $('#service_mappings_feedback').text('');

    if (validator) {
      validator.showErrors(Object.fromEntries(
        Object.entries(errors).map(function (entry) {
          return [convertErrorKeyToInputName(entry[0]), entry[1][0]];
        })
      ));
    }

    Object.entries(errors).forEach(function (entry) {
      const key = entry[0];
      const message = entry[1][0];

      if (!key.startsWith('mappings.')) {
        return;
      }

      const parts = key.split('.');
      const rowIndex = parseInt(parts[1], 10);
      const field = parts[2];
      const $row = $mappingsTableBody.find('.service-mapping-row').eq(rowIndex);

      if (!$row.length) {
        $('#service_mappings_feedback').text(message);
        return;
      }

      if (field === 'product_id') {
        const $select = $row.find('.service-product-select');
        setSelect2ErrorState($select, true);
        $select.closest('.position-relative').find('.invalid-feedback').first().text(message);
        return;
      }

      const fieldSelectorMap = {
        quantity: '.service-mapping-quantity',
        unit: '.service-mapping-unit',
        is_required: '.service-mapping-required'
      };

      const $field = $row.find(fieldSelectorMap[field] || '');

      if ($field.length) {
        $field.addClass('is-invalid');
        const $feedback = $field.siblings('.invalid-feedback').first();

        if ($feedback.length) {
          $feedback.text(message);
        } else {
          $row.find('.invalid-feedback').last().text(message);
        }
      }
    });
  };

  const bindFilters = function () {
    $('#service_status, #service_requires_technician_filter, #service_sort').on('change', reloadTable);
    $filterCategory.on('change', reloadTable);
  };

  const bindMappingRows = function () {
    $(document).on('click', '#addServiceMappingRowBtn', function () {
      addMappingRow();
    });

    $(document).on('click', '.remove-service-mapping-row', function () {
      const rowCount = $mappingsTableBody.find('.service-mapping-row').length;

      if (rowCount === 1) {
        renderMappingRows([]);
        return;
      }

      $(this).closest('.service-mapping-row').remove();
      reindexMappingRows();
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addServiceBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-service-btn', function () {
      const $button = $(this);
      const editUrl = $button.data('edit-url') || serviceEditUrl($button.data('id'));

      resetForm();
      setSubmitButtonState(true);

      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading service...');
      }

      $.ajax({
        url: editUrl,
        method: 'GET'
      })
        .done(function (response) {
          fillForm(response.data || {});
          if (validator) {
            validator.resetForm();
          }
        })
        .fail(function (xhr) {
          if (modal) {
            modal.hide();
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to load service details.');
        })
        .always(function () {
          setSubmitButtonState(false);
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
        window.appLoading.show('Saving service...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          if (modal) {
            modal.hide();
          }

          reloadTable();
          showAlert('success', response.message || 'Service saved successfully.');
        })
        .fail(function (xhr) {
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            if (xhr.responseJSON.errors.id) {
              showAlert('error', xhr.responseJSON.errors.id[0]);
            }

            appendServerErrors(xhr.responseJSON.errors, validator);
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save service.');
        })
        .always(function () {
          setSubmitButtonState(false);
          if (window.appLoading && typeof window.appLoading.hide === 'function') {
            window.appLoading.hide(200);
          }
        });
    });
  };

  const bindDeleteButton = function () {
    $(document).on('click', '.delete-service-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      Swal.fire({
        title: 'Delete service?',
        text: 'This will remove "' + name + '" and its product mappings from the tenant catalog.',
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

        $button.prop('disabled', true);
        if (window.appLoading && typeof window.appLoading.show === 'function') {
          window.appLoading.show('Deleting service...');
        }

        $.ajax({
          url: deleteUrl,
          method: 'DELETE'
        })
          .done(function (response) {
            reloadTable();
            showAlert('success', response.message || 'Service deleted successfully.');
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete service.');
          })
          .always(function () {
            $button.prop('disabled', false);
            if (window.appLoading && typeof window.appLoading.hide === 'function') {
              window.appLoading.hide(200);
            }
          });
      });
    });
  };

  $(function () {
    initStaticSelect2();
    initCategorySelect($formCategory);
    initCategorySelect($filterCategory);

    const validator = bindFormValidation();

    initDataTable();
    bindFilters();
    bindMappingRows();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteButton();
    resetForm();
  });
})(jQuery);
