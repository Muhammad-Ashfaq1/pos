(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#discountModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#discountForm');
  const $submitButton = $('#discountSubmitBtn');
  const $table = $('.discounts-datatables');
  const $discountType = $('#discount_type');
  let discountTable = null;

  const discountEditUrl = function (discountId) {
    return (window.discountEditUrlTemplate || '').replace('__DISCOUNT__', discountId);
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
    const isEdit = Boolean($('#discount_id').val());
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
    setSelect2ErrorState($('#discount_type'), false);
    setSelect2ErrorState($('#discount_applies_to'), false);
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

  const updateValueFieldPlaceholder = function () {
    const isPercentage = $discountType.val() === 'percentage';
    $('#discount_value').attr('max', isPercentage ? '100' : null);
    $('#discount_value').attr('placeholder', isPercentage ? 'Enter percentage' : 'Enter amount');
  };

  const resetForm = function () {
    $form[0].reset();
    $('#discount_id').val('');
    $('#discount_type').val('fixed').trigger('change');
    $('#discount_applies_to').val('bill').trigger('change');
    $('#discount_is_active').prop('checked', true);
    $('#discount_is_combinable').prop('checked', true);
    $('#discount_requires_reason').prop('checked', false);
    $('#discount_requires_manager_approval').prop('checked', false);
    $('#discountModalLabel').text('Add Discount');
    setSubmitButtonState(false);
    resetValidationState();
    updateValueFieldPlaceholder();
  };

  const fillForm = function (discount) {
    $('#discount_id').val(discount.id);
    $('#discount_name').val(discount.name);
    $('#discount_code').val(discount.code);
    $('#discount_description').val(discount.description);
    $('#discount_type').val(discount.discount_type).trigger('change');
    $('#discount_applies_to').val(discount.applies_to).trigger('change');
    $('#discount_value').val(discount.value);
    $('#discount_max_discount_amount').val(discount.max_discount_amount);
    $('#discount_starts_at').val(discount.starts_at);
    $('#discount_ends_at').val(discount.ends_at);
    $('#discount_usage_limit').val(discount.usage_limit);
    $('#discount_is_active').prop('checked', Boolean(discount.is_active));
    $('#discount_is_combinable').prop('checked', Boolean(discount.is_combinable));
    $('#discount_requires_reason').prop('checked', Boolean(discount.requires_reason));
    $('#discount_requires_manager_approval').prop('checked', Boolean(discount.requires_manager_approval));
    $('#discountModalLabel').text('Edit Discount');
    setSubmitButtonState(false);
    resetValidationState();
    updateValueFieldPlaceholder();
  };

  const tooltipAttrs = function (title) {
    return window.Helpers && window.Helpers.getTooltipAttributes
      ? window.Helpers.getTooltipAttributes(title)
      : 'title="' + title + '"';
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center">';

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-discount-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || discountEditUrl(row.id)) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-discount-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
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
        name: { required: true, maxlength: 150 },
        code: { maxlength: 50 },
        description: { maxlength: 2000 },
        discount_type: { required: true },
        applies_to: { required: true },
        value: { required: true, number: true, min: 0.01 },
        max_discount_amount: { number: true, min: 0 },
        usage_limit: { digits: true, min: 1 }
      },
      messages: {
        name: { required: 'Please enter a discount name.' },
        discount_type: { required: 'Please select a discount type.' },
        applies_to: { required: 'Please select where this discount applies.' },
        value: { required: 'Please enter a discount value.' }
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

    discountTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.discountListingUrl,
        data: function (d) {
          d.status = $('#discount_status').val();
          d.discount_type = $('#discount_type_filter').val();
          d.applies_to = $('#discount_applies_to_filter').val();
          d.sort = $('#discount_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name, code or discount target',
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
        emptyTable: 'No discounts found',
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
            if (row.code) {
              html += '<div class="small text-muted"><code>' + escapeHtml(row.code) + '</code></div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: 'discount_type_label',
          render: function (data) {
            return '<span class="badge bg-label-info">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: 'applies_to_label',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: 'value_label',
          render: function (data, type, row) {
            let html = '<div><span class="fw-semibold">' + escapeHtml(data || '—') + '</span>';
            if (row.max_discount_amount_label) {
              html += '<div class="small text-muted">Cap: ' + escapeHtml(row.max_discount_amount_label) + '</div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            let html = '<div class="d-flex flex-column gap-1">';
            html += '<span class="badge ' + row.combinable_badge_class + '">' + escapeHtml(row.combinable_label) + '</span>';
            if (row.requires_reason) {
              html += '<span class="badge bg-label-secondary">Reason Required</span>';
            }
            if (row.requires_manager_approval) {
              html += '<span class="badge bg-label-danger">Manager Approval</span>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            const start = row.starts_at_label || 'Immediate';
            const end = row.ends_at_label || 'No End';
            return '<div><div>' + escapeHtml(start) + '</div><div class="small text-muted">' + escapeHtml(end) + '</div></div>';
          }
        },
        {
          data: null,
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
      ],
      drawCallback: function () {
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
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
    $('#discount_status, #discount_type_filter, #discount_applies_to_filter, #discount_sort').on('change', function () {
      if (discountTable) {
        discountTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addDiscountBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-discount-btn', function () {
      const editUrl = $(this).data('edit-url') || discountEditUrl($(this).data('id'));

      resetForm();
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading discount...');
      }

      $.get(editUrl)
        .done(function (response) {
          fillForm(response.data || {});
          if (modal) {
            modal.show();
          }
        })
        .fail(function (xhr) {
          showAlert('error', xhr.responseJSON?.message || 'Unable to load discount.');
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
        window.appLoading.show('Saving discount...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          showAlert('success', response.message || 'Discount saved successfully.');
          if (modal) {
            modal.hide();
          }
          if (discountTable) {
            discountTable.ajax.reload(null, false);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 422) {
            renderValidationErrors(xhr.responseJSON?.errors || {});
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save discount.');
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
    $(document).on('click', '.delete-discount-btn', function () {
      const url = $(this).data('url');
      const name = $(this).data('name') || 'this discount';

      Swal.fire({
        title: 'Delete Discount?',
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
          window.appLoading.show('Deleting discount...');
        }

        $.ajax({
          url: url,
          method: 'DELETE'
        })
          .done(function (response) {
            showAlert('success', response.message || 'Discount deleted successfully.');
            if (discountTable) {
              discountTable.ajax.reload(null, false);
            }
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete discount.');
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
    $discountType.on('change', updateValueFieldPlaceholder);
    resetForm();
  });
})(window.jQuery);
