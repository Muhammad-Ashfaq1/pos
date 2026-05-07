(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#subCategoryModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#subCategoryForm');
  const $submitButton = $('#subCategorySubmitBtn');
  const $table = $('.subcategories-datatables');
  const $formCategory = $('#category_id');
  const $filterCategory = $('#subcategory_filter_category');
  let subCategoryTable = null;

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
    const isEdit = Boolean($('#sub_category_id').val());
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

  const resetValidationState = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    setSelect2ErrorState($formCategory, false);
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
    const $selects = $('.select2');

    if (typeof $.fn.select2 !== 'function' || ! $selects.length) {
      return;
    }

    $selects.each(function () {
      const $this = $(this);

      if ($this.data('select2')) {
        return;
      }

      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder'),
        allowClear: Boolean($this.data('allow-clear')),
        minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0
      });
    });
  };

  const initCategorySelect = function ($element) {
    if (typeof $.fn.select2 !== 'function' || ! $element.length || $element.data('select2')) {
      return;
    }

    const dropdownParentSelector = $element.data('dropdown-parent');
    const dropdownParent = dropdownParentSelector ? $(dropdownParentSelector) : $element.parent();

    if (! dropdownParentSelector && ! $element.parent().hasClass('position-relative')) {
      $element.wrap('<div class="position-relative"></div>');
    }

    $element.select2({
      dropdownParent: dropdownParentSelector ? dropdownParent : $element.parent(),
      placeholder: $element.data('placeholder'),
      allowClear: Boolean($element.data('allow-clear')),
      ajax: {
        url: window.categoryDropdownUrl,
        delay: 250,
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term || '',
            page: params.page || 1,
            active_only: $element.data('active-only') ? 1 : 0
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
    $('#sub_category_id').val('');
    $('#subcategory_sort_order').val(0);
    $('#subcategory_is_active').prop('checked', true);
    $('#subCategoryModalLabel').text('Add Sub Category');
    ensureSelectOption($formCategory, null, null);
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function ($button) {
    $('#sub_category_id').val($button.data('id'));
    ensureSelectOption($formCategory, $button.data('category-id'), $button.data('category-name'));
    $('#subcategory_name').val($button.data('name'));
    $('#subcategory_code').val($button.data('code'));
    $('#subcategory_description').val($button.data('description'));
    $('#subcategory_sort_order').val($button.data('sort-order'));
    $('#subcategory_is_active').prop('checked', String($button.data('is-active')) === '1');
    $('#subCategoryModalLabel').text('Edit Sub Category');
    setSubmitButtonState(false);
    resetValidationState();
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
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-subcategory-btn" ' +
        'data-id="' + row.id + '" ' +
        'data-category-id="' + row.category_id + '" ' +
        'data-category-name="' + escapeHtml(row.category_name || '') + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' +
        'data-code="' + escapeHtml(row.code || '') + '" ' +
        'data-description="' + escapeHtml(row.description || '') + '" ' +
        'data-sort-order="' + row.sort_order + '" ' +
        'data-is-active="' + (row.is_active ? 1 : 0) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-subcategory-btn" ' +
        'data-url="' + row.delete_url + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
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
        category_id: {
          required: true
        },
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
        category_id: {
          required: 'Please select a category.'
        },
        name: {
          required: 'Please enter a sub category name.',
          maxlength: 'The sub category name may not be greater than 150 characters.'
        },
        code: {
          maxlength: 'The sub category code may not be greater than 50 characters.'
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
    if (typeof DataTable === 'undefined' || ! $table.length) {
      return;
    }

    subCategoryTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.subCategoryListingUrl,
        data: function (d) {
          d.status = $('#subcategory_status').val();
          d.category_id = $filterCategory.val();
          d.sort = $('#subcategory_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by category, name, slug or code',
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
        emptyTable: 'No sub categories found',
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
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });
  };

  const bindFilters = function () {
    $('#subcategory_status, #subcategory_sort').on('change', function () {
      if (subCategoryTable) {
        subCategoryTable.ajax.reload(null, false);
      }
    });

    $filterCategory.on('change', function () {
      if (subCategoryTable) {
        subCategoryTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addSubCategoryBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-subcategory-btn', function () {
      const modalEl = document.getElementById('subCategoryModal');
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
        window.appLoading.show('Saving sub category...');
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

          if (subCategoryTable) {
            subCategoryTable.ajax.reload(null, false);
          }

          showAlert('success', response.message || 'Sub category saved successfully.');
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

          showAlert('error', xhr.responseJSON?.message || 'Unable to save sub category.');
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
    $(document).on('click', '.delete-subcategory-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      Swal.fire({
        title: 'Delete sub category?',
        text: 'This will remove "' + name + '" from the tenant catalog.',
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
        if (! result.isConfirmed) {
          return;
        }

        $button.prop('disabled', true);
        if (window.appLoading && typeof window.appLoading.show === 'function') {
          window.appLoading.show('Deleting sub category...');
        }

        $.ajax({
          url: deleteUrl,
          method: 'DELETE'
        })
          .done(function (response) {
            if (subCategoryTable) {
              subCategoryTable.ajax.reload(null, false);
            }

            showAlert('success', response.message || 'Sub category deleted successfully.');
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete sub category.');
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
    initCategorySelect($filterCategory);
    initCategorySelect($formCategory);
    const validator = bindFormValidation();
    initDataTable();
    bindFilters();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteButton();
  });
})(jQuery);
