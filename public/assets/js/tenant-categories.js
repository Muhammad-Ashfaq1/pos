(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#categoryModal');
  const modal = $modal.length ? new bootstrap.Modal($modal[0]) : null;
  const $form = $('#categoryForm');
  const $submitButton = $('#categorySubmitBtn');
  const $table = $('.categories-datatables');
  let categoryTable = null;

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

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#category_id').val());
    const defaultText = isEdit ? $submitButton.data('update-text') : $submitButton.data('create-text');

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
    $('#category_id').val('');
    $('#sort_order').val(0);
    $('#is_active').prop('checked', true);
    $('#categoryModalLabel').text('Add Category');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const fillForm = function ($button) {
    $('#category_id').val($button.data('id'));
    $('#name').val($button.data('name'));
    $('#code').val($button.data('code'));
    $('#description').val($button.data('description'));
    $('#sort_order').val($button.data('sort-order'));
    $('#is_active').prop('checked', String($button.data('is-active')) === '1');
    $('#categoryModalLabel').text('Edit Category');
    setSubmitButtonState(false);
    resetValidationState();
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center">';

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-category-btn" ' +
        'data-bs-toggle="modal" data-bs-target="#categoryModal" ' +
        'data-id="' + row.id + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' +
        'data-code="' + escapeHtml(row.code || '') + '" ' +
        'data-description="' + escapeHtml(row.description || '') + '" ' +
        'data-sort-order="' + row.sort_order + '" ' +
        'data-is-active="' + (row.is_active ? 1 : 0) + '" title="Edit">' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-category-btn category-delete-btn" ' +
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
          required: 'Please enter a category name.',
          maxlength: 'The category name may not be greater than 150 characters.'
        },
        code: {
          maxlength: 'The category code may not be greater than 50 characters.'
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

    categoryTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: false,
      ajax: {
        url: window.categoryListingUrl,
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
            placeholder: 'Search by name or code',
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
        emptyTable: 'No categories found',
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      columns: [
        {
          data: null,
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

  const bindFilters = function () {
    $('#status, #sort').on('change', function () {
      if (categoryTable) {
        categoryTable.ajax.reload(null, false);
      }
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addCategoryBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-category-btn', function () {
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

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize()
      })
        .done(function (response) {
          if (modal) {
            modal.hide();
          }

          if (categoryTable) {
            categoryTable.ajax.reload(null, false);
          }

          showAlert('success', response.message || 'Category saved successfully.');
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

          showAlert('error', xhr.responseJSON?.message || 'Unable to save category.');
        })
        .always(function () {
          setSubmitButtonState(false);
        });
    });
  };

  const bindDeleteButton = function () {
    $(document).on('click', '.category-delete-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      Swal.fire({
        title: 'Delete category?',
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

        $.ajax({
          url: deleteUrl,
          method: 'DELETE'
        })
          .done(function (response) {
            if (categoryTable) {
              categoryTable.ajax.reload(null, false);
            }

            showAlert('success', response.message || 'Category deleted successfully.');
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete category.');
          })
          .always(function () {
            $button.prop('disabled', false);
          });
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
