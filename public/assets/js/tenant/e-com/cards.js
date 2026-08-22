(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#cardModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#cardForm');
  const $submitButton = $('#cardSubmitBtn');
  const $table = $('.cards-datatables');
  const $discountType = $('#card_discount_type');
  const cardType = window.cardType || 'discount';
  const cardSingular = window.cardSingular || 'Card';
  const isDiscount = cardType === 'discount';
  const isReward = cardType === 'reward';
  let cardTable = null;

  const cardEditUrl = function (cardId) {
    return (window.cardEditUrlTemplate || '').replace('__CARD__', cardId);
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

  const alignCreateButtonWithSearch = function (table, actionsSelector) {
    if (window.PosListingToolbar && typeof window.PosListingToolbar.align === 'function') {
      window.PosListingToolbar.align(table, actionsSelector);
    }
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#card_id').val());
    const defaultText = isEdit ? $submitButton.data('update-text') : $submitButton.data('create-text');
    $submitButton.data('default-text', defaultText);

    if (window.CardForm) {
      window.CardForm.setButtonLoading($submitButton, loading, 'Saving...');
      return;
    }

    $submitButton.prop('disabled', loading).text(loading ? 'Saving...' : defaultText);
  };

  const setSelect2ErrorState = function ($element, invalid) {
    if (window.CardForm) {
      window.CardForm.setSelect2ErrorState($element, invalid);
      return;
    }

    $element.next('.select2').find('.select2-selection').toggleClass('is-invalid', invalid);
  };

  const resetValidationState = function () {
    if (window.CardForm) {
      window.CardForm.clearValidation($form);
      return;
    }

    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    if ($discountType.length) {
      setSelect2ErrorState($discountType, false);
    }
    setSelect2ErrorState($('#card_product_ids'), false);
  };

  const initStaticSelect2 = function () {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $('.select2').not('.card-product-select').each(function () {
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
        if (window.CardForm) {
          window.CardForm.clearFieldError($this);
          return;
        }

        setSelect2ErrorState($this, false);
        $this.closest('.position-relative').find('.invalid-feedback').text('');
      });
    });

    if (window.CardForm) {
      // Init after the modal is visible so Select2 can read the <option> list.
      $modal.on('shown.bs.modal', function () {
        window.CardForm.initProductSelects({ $root: $modal });
      });
    }
  };

  const updateTypeDependentFields = function () {
    if (!isDiscount) {
      return;
    }

    if (window.CardForm) {
      window.CardForm.updateDiscountFields($form, {
        currencySymbol: window.currencySymbol || window.appCurrency?.symbol || ''
      });
      return;
    }

    const isPercentage = $discountType.val() === 'percentage';
    const valueLabel = isPercentage ? 'Discount Percentage' : 'Fixed Amount';
    $form.find('[data-card-value-label]').html(valueLabel + ' <span class="text-danger">*</span>');
    $form.find('[data-card-value]').attr('max', isPercentage ? '100' : null);
  };

  const todayDateString = function () {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    return now.getFullYear() + '-' + month + '-' + day;
  };

  const setValidUntilMin = function (minDate) {
    const min = minDate || todayDateString();
    if (window.AppDatepicker && typeof window.AppDatepicker.setMin === 'function') {
      window.AppDatepicker.setMin('#card_valid_until', min);
      return;
    }
    $('#card_valid_until').attr('min', min);
  };

  const resetForm = function () {
    $form[0].reset();
    $('#card_id').val('');
    $('#card_type').val(cardType);
    if ($discountType.length) {
      $discountType.val('percentage').trigger('change');
    }
    $('#card_product_ids').val(null).trigger('change');
    $('#card_minimum_spend').val('0');
    $('#card_is_active').prop('checked', true);
    setValidUntilMin(todayDateString());
    $('#cardModalLabel').text('Add ' + cardSingular);
    setSubmitButtonState(false);
    resetValidationState();
    updateTypeDependentFields();
  };

  const fillForm = function (card) {
    const today = todayDateString();
    const existingDate = card.valid_until || '';
    // Friendly edit: keep showing an already-expired date; new picks stay today+.
    const minDate = existingDate && existingDate < today ? existingDate : today;

    $('#card_id').val(card.id);
    $('#card_type').val(cardType);
    $('#card_name').val(card.name);
    if ($discountType.length) {
      $discountType.val(card.discount_type || 'percentage').trigger('change');
    }
    $('#card_value').val(card.value);
    $('#card_minimum_spend').val(card.minimum_spend);
    $('#card_product_ids').val(card.product_ids || []).trigger('change');
    setValidUntilMin(minDate);
    $('#card_valid_until').val(existingDate);
    $('#card_is_active').prop('checked', Boolean(card.is_active));
    $('#cardModalLabel').text('Edit ' + cardSingular);
    setSubmitButtonState(false);
    resetValidationState();
    updateTypeDependentFields();
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
        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-card-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || cardEditUrl(row.id)) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-card-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
        '<i class="icon-base ti tabler-trash"></i>' +
        '</button>';
    }

    html += '</div>';

    return html;
  };

  const bindFormValidation = function () {
    if (typeof $.fn.validate !== 'function') {
      return null;
    }

    const rules = {
      name: { required: true, maxlength: 150 },
      value: { required: true, number: true, min: 0.01 },
      minimum_spend: { required: true, number: true, min: 0 }
    };

    const messages = {
      name: { required: 'Please enter a card name.' },
      value: { required: 'Please enter a card value.' },
      minimum_spend: { required: 'Please enter a minimum spend amount.' }
    };

    if (isDiscount) {
      rules.discount_type = { required: true };
      messages.discount_type = { required: 'Please select a discount type.' };
    }

    return $form.validate({
      ignore: [],
      rules: rules,
      messages: messages,
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

    cardTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        global: false,
        url: window.cardListingUrl,
        data: function (d) {
          d.status = $('#card_status').val();
          d.sort = $('#card_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name',
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
        emptyTable: 'No ' + cardSingular.toLowerCase() + 's found',
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
          render: function (data) {
            return '<span class="fw-semibold">' + escapeHtml(data) + '</span>';
          }
        },
        {
          data: 'value_label',
          render: function (data, type, row) {
            let html = '<div><span class="fw-semibold">' + escapeHtml(data || '—') + '</span>';
            if (row.discount_type_label) {
              html += '<div class="small text-muted">' + escapeHtml(row.discount_type_label) + '</div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: 'minimum_spend_label',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: 'products_label',
          orderable: false,
          render: function (data) {
            return '<span class="text-break">' + escapeHtml(data || 'All products') + '</span>';
          }
        },
        {
          data: 'valid_until_label',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || 'No expiry') + '</span>';
          }
        },
        {
          data: null,
          orderable: false,
          render: function (data, type, row) {
            return '<span class="badge rounded ' + row.status_badge_class + '">' + escapeHtml(row.status_label) + '</span>';
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
        alignCreateButtonWithSearch(this.api(), '#cardTableActions');
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });

    alignCreateButtonWithSearch(cardTable, '#cardTableActions');
  };

  const renderValidationErrors = function (errors) {
    if (window.CardForm) {
      window.CardForm.renderValidationErrors($form, errors);
      return;
    }

    Object.entries(errors || {}).forEach(function (entry) {
      const field = entry[0];
      const message = Array.isArray(entry[1]) ? entry[1][0] : entry[1];
      let $element = $form.find('[name="' + field + '"]');

      if (!$element.length && field.indexOf('.') !== -1) {
        const baseField = field.split('.')[0];
        $element = $form.find('[name="' + baseField + '[]"], [name="' + baseField + '"]').first();
      }

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
    $('#card_status, #card_sort').on('change', function () {
      if (cardTable) {
        cardTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addCardBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-card-btn', function () {
      const editUrl = $(this).data('edit-url') || cardEditUrl($(this).data('id'));

      resetForm();
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading card...');
      }

      $.get(editUrl)
        .done(function (response) {
          fillForm(response.data || {});
          if (modal) {
            modal.show();
          }
        })
        .fail(function (xhr) {
          showAlert('error', xhr.responseJSON?.message || 'Unable to load card.');
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
      $('#card_type').val(cardType);

      if (validator && !$form.valid()) {
        return;
      }

      setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving card...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          showAlert('success', response.message || cardSingular + ' saved successfully.');
          if (modal) {
            modal.hide();
          }
          if (cardTable) {
            cardTable.ajax.reload(null, false);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 422) {
            renderValidationErrors(xhr.responseJSON?.errors || {});
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save card.');
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
    $(document).on('click', '.delete-card-btn', function () {
      const url = $(this).data('url');
      const name = $(this).data('name') || 'this ' + cardSingular.toLowerCase();

      if (!window.PosConfirm || typeof window.PosConfirm.open !== 'function') {
        return;
      }

      window.PosConfirm.open({
        title: 'Delete ' + cardSingular + '?',
        message: 'This action will permanently remove ' + name + '.',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel',
        tone: 'danger',
        onConfirm: function () {
          return $.ajax({
            url: url,
            method: 'DELETE'
          }).then(
            function (response) {
              showAlert('success', response.message || cardSingular + ' deleted successfully.');
              if (cardTable) {
                cardTable.ajax.reload(null, false);
              }
            },
            function (xhr) {
              throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete card.');
            }
          );
        }
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
    if ($discountType.length) {
      $discountType.on('change', updateTypeDependentFields);
    }
    resetForm();
  });
})(window.jQuery);
