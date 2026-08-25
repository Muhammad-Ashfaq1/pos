(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#serviceCategoryModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#serviceCategoryForm');
  const $submitButton = $('#serviceCategorySubmitBtn');
  const $table = $('.service-categories-datatables');
  const $name = $('#name');
  const $slug = $('#slug');
  let serviceCategoryTable = null;

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

  const syncSlugFromName = function () {
    $slug.val(window.appSlugify($name.val()));
  };

  const alignServiceCategoryActionsWithSearch = function (table) {
    if (window.PosListingToolbar && typeof window.PosListingToolbar.align === 'function') {
      window.PosListingToolbar.align(table, '#serviceCategoryTableActions');
    }
  };

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#service_category_id').val());
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
    $('#service_category_id').val('');
    $('#sort_order').val(0);
    $('#is_active').prop('checked', true);
    $slug.val('');
    $('#serviceCategoryModalLabel').text('Add Service Category');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function ($button) {
    $('#service_category_id').val($button.data('id'));
    $name.val($button.data('name'));
    $slug.val($button.data('slug') || window.appSlugify($button.data('name')));
    $('#description').val($button.data('description'));
    $('#sort_order').val($button.data('sort-order'));
    $('#is_active').prop('checked', String($button.data('is-active')) === '1');
    $('#serviceCategoryModalLabel').text('Edit Service Category');
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
        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-category-btn" ' +
        'data-id="' + row.id + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' +
        'data-slug="' + escapeHtml(row.slug || '') + '" ' +
        'data-description="' + escapeHtml(row.description || '') + '" ' +
        'data-sort-order="' + row.sort_order + '" ' +
        'data-is-active="' + (row.is_active ? 1 : 0) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-category-btn category-delete-btn" ' +
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
          required: 'Please enter a service category name.',
          maxlength: 'The service category name may not be greater than 150 characters.'
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

    serviceCategoryTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        global: false, // table/dropdown has its own indicator — skip the global overlay
        url: window.serviceCategoryListingUrl,
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
            placeholder: 'Search by name or slug',
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
        emptyTable: 'No service categories found',
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
          data: 'description',
          render: function (data) {
            const value = data || '—';
            return escapeHtml(value.length > 70 ? value.slice(0, 67) + '...' : value);
          }
        },
        { data: 'sort_order' },
        {
          data: null,
          orderable: false,
          searchable: false,
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
        alignServiceCategoryActionsWithSearch(this.api());
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });

    alignServiceCategoryActionsWithSearch(serviceCategoryTable);
  };

  const bindFilters = function () {
    $('#status, #sort').on('change', function () {
      if (serviceCategoryTable) {
        serviceCategoryTable.ajax.reload(null, false);
      }
    });
  };

  const bindSlugGeneration = function () {
    $name.on('input', function () {
      // Keep the read-only slug in sync for both new and existing categories.
      syncSlugFromName();
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addServiceCategoryBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-category-btn', function () {
      const modalEl = document.getElementById('serviceCategoryModal');
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
    $form.off('submit.serviceCategorySave').on('submit.serviceCategorySave', function (event) {
      event.preventDefault();
      resetValidationState();

      if (!$slug.val()) {
        syncSlugFromName();
      }

      if (validator && ! $form.valid()) {
        return;
      }

      setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving service category...');
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

          if (serviceCategoryTable) {
            serviceCategoryTable.ajax.reload(null, false);
          }

          showAlert('success', response.message || 'Service category saved successfully.');
        })
        .fail(function (xhr) {
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = xhr.responseJSON.errors;

            if (errors.id) {
              showAlert('error', errors.id[0]);
            }

            if (errors.name && errors.name[0]) {
              showAlert('error', errors.name[0]);
            }

            if (validator) {
              validator.showErrors(Object.fromEntries(
                Object.entries(errors).map(function (entry) {
                  return [entry[0], entry[1][0]];
                })
              ));
            }

            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save service category.');
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
    $(document).on('click', '.category-delete-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      if (!window.PosConfirm || typeof window.PosConfirm.open !== 'function') {
        return;
      }

      window.PosConfirm.open({
        title: 'Delete service category?',
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
              if (serviceCategoryTable) {
                serviceCategoryTable.ajax.reload(null, false);
              }

              showAlert('success', response.message || 'Service category deleted successfully.');
            },
            function (xhr) {
              throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete service category.');
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
    bindSlugGeneration();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteButton();
  });
})(jQuery);
