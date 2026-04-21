(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#categoryModal');
  const modal = $modal.length ? new bootstrap.Modal($modal[0]) : null;
  const $form = $('#categoryForm');
  const $submitButton = $('#categorySubmitBtn');
  const $alerts = $('#categoryAlerts');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const showAlert = function (type, message) {
    if (! $alerts.length) {
      return;
    }

    const className = type === 'success' ? 'alert-success' : 'alert-danger';

    $alerts.html(
      '<div class="alert ' + className + ' alert-dismissible fade show" role="alert">' +
        message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
      '</div>'
    );
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
    if (! $form.length) {
      return;
    }

    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
  };

  const resetForm = function () {
    if (! $form.length) {
      return;
    }

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

  const bindFormValidation = function () {
    if (! $form.length || typeof $.fn.validate !== 'function') {
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

  const bindFilterForm = function () {
    const $form = $('#categoryFilterForm');

    if (! $form.length) {
      return;
    }

    let timer = null;

    $form.find('.filter-control').on('change', function () {
      $form.trigger('submit');
    });

    $form.find('input[name="search"]').on('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        $form.trigger('submit');
      }, 500);
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addCategoryBtn, #emptyStateAddCategoryBtn', function () {
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
    if (! $form.length) {
      return;
    }

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
          window.location.reload();
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

  const bindStatusToggle = function () {
    $(document).on('change', '.category-status-toggle', function () {
      const $toggle = $(this);
      const $row = $toggle.closest('tr');
      const previousState = ! $toggle.is(':checked');

      $toggle.prop('disabled', true);

      $.ajax({
        url: $toggle.data('url'),
        method: 'PATCH'
      })
        .done(function (response) {
          const data = response.data || {};
          const $badge = $row.find('[data-status-badge]');

          $badge
            .text(data.status_label || 'Updated')
            .removeClass('bg-label-success bg-label-secondary')
            .addClass(data.status_badge_class || 'bg-label-secondary');

          showAlert('success', response.message || 'Category status updated successfully.');
        })
        .fail(function (xhr) {
          $toggle.prop('checked', previousState);
          showAlert('error', xhr.responseJSON?.message || 'Unable to update category status.');
        })
        .always(function () {
          $toggle.prop('disabled', false);
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
            $button.closest('tr').remove();
            showAlert('success', response.message || 'Category deleted successfully.');

            if ($('tbody tr[id^="category-row-"]').length === 0) {
              window.location.reload();
            }
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
    bindModalActions(validator);
    bindSaveForm(validator);
    bindFilterForm();
    bindStatusToggle();
    bindDeleteButton();
  });
})(jQuery);
