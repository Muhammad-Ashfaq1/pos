(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#productTypeModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#productTypeForm');
  const $submitButton = $('#productTypeSubmitBtn');
  const $table = $('.product-types-datatables');
  let productTypeTable = null;

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

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#product_type_id').val());
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

  const resetValidationState = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
  };

  const resetForm = function () {
    $form[0].reset();
    $('#product_type_id').val('');
    $('#sort_order').val(0);
    $('#is_active').prop('checked', true);
    $('#productTypeModalLabel').text('Add Product Type');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function ($button) {
    $('#product_type_id').val($button.data('id'));
    $('#name').val($button.data('name'));
    $('#code').val($button.data('code'));
    $('#description').val($button.data('description'));
    $('#sort_order').val($button.data('sort-order'));
    $('#is_active').prop('checked', String($button.data('is-active')) === '1');
    $('#productTypeModalLabel').text('Edit Product Type');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
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
        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-product-type-btn" ' +
        'data-id="' + row.id + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' +
        'data-code="' + escapeHtml(row.code || '') + '" ' +
        'data-description="' + escapeHtml(row.description || '') + '" ' +
        'data-sort-order="' + row.sort_order + '" ' +
        'data-is-active="' + (row.is_active ? 1 : 0) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-product-type-btn product-type-delete-btn" ' +
        'data-url="' + row.delete_url + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
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
          maxlength: 1000
        },
        sort_order: {
          required: true,
          number: true,
          min: 0
        }
      },
      messages: {
        name: {
          required: 'Please enter a product type name.',
          maxlength: 'The product type name may not be greater than 150 characters.'
        },
        code: {
          maxlength: 'The product type code may not be greater than 50 characters.'
        },
        description: {
          maxlength: 'The description may not be greater than 1000 characters.'
        },
        sort_order: {
          required: 'Please enter a sort order.',
          number: 'Sort order must be numeric.',
          min: 'Sort order must be zero or greater.'
        }
      },
      errorElement: 'div',
      errorClass: 'invalid-feedback',
      highlight: function (element) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element) {
        $(element).removeClass('is-invalid');
      },
      errorPlacement: function (error, element) {
        const $feedback = element.siblings('.invalid-feedback').first();

        if ($feedback.length) {
          $feedback.text(error.text());
          return;
        }

        error.insertAfter(element);
      }
    });
  };

  const initDataTable = function () {
    if (typeof DataTable === 'undefined' || ! $table.length) {
      return;
    }

    productTypeTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        global: false, // table/dropdown has its own indicator — skip the global overlay
        url: window.productTypeListingUrl,
        data: function (d) {
          d.status = $('#status').val();
          d.sort = $('#sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name, slug or code',
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
        emptyTable: 'No product types found',
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
          data: 'slug',
          render: function (data) {
            return '<code>' + escapeHtml(data || '—') + '</code>';
          }
        },
        {
          data: 'code',
          render: function (data) {
            return escapeHtml(data || '—');
          }
        },
        {
          data: 'description',
          render: function (data) {
            const value = data || '—';
            return escapeHtml(value.length > 70 ? value.slice(0, 67) + '...' : value);
          }
        },
        {
          data: 'products_count',
          orderable: false,
          searchable: false,
          render: function (data) {
            return '<span class="badge bg-label-primary">' + (data || 0) + '</span>';
          }
        },
        { data: 'sort_order' },
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
      ],
      drawCallback: function () {
        alignCreateButtonWithSearch(this.api(), '#productTypeTableActions');
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });

    alignCreateButtonWithSearch(productTypeTable, '#productTypeTableActions');
  };

  const bindFilters = function () {
    $('#status, #sort').on('change', function () {
      if (productTypeTable) {
        productTypeTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addProductTypeBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-product-type-btn', function () {
      const modalEl = document.getElementById('productTypeModal');
      if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }

      fillForm($(this));
      if (validator) {
        validator.resetForm();
      }
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

      if (validator && ! $form.valid()) {
        return;
      }

      setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving product type...');
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

          if (productTypeTable) {
            productTypeTable.ajax.reload(null, false);
          }

          showAlert('success', response.message || 'Product type saved successfully.');
        })
        .fail(function (xhr) {
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            if (xhr.responseJSON.errors.id) {
              showAlert('error', xhr.responseJSON.errors.id[0]);
            }

            if (validator) {
              validator.showErrors(Object.fromEntries(
                Object.entries(xhr.responseJSON.errors).map(function (entry) {
                  return [entry[0], entry[1][0]];
                })
              ));
            }

            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save product type.');
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
    $(document).on('click', '.product-type-delete-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      if (!window.PosConfirm || typeof window.PosConfirm.open !== 'function') {
        return;
      }

      window.PosConfirm.open({
        title: 'Delete product type?',
        message: 'This will remove "' + name + '" from the tenant catalog.',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel',
        tone: 'danger',
        onConfirm: function () {
          return $.ajax({
            url: deleteUrl,
            method: 'DELETE'
          }).then(
            function (response) {
              if (productTypeTable) {
                productTypeTable.ajax.reload(null, false);
              }
              showAlert('success', response.message || 'Product type deleted successfully.');
            },
            function (xhr) {
              throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete product type.');
            }
          );
        }
      });
    });
  };

  $(function () {
    const validator = bindFormValidation();
    initDataTable();
    bindFilters();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteButton();
  });
})(jQuery);
