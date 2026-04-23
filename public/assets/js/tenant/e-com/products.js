(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const $modal = $('#productModal');
  const modal = $modal.length ? bootstrap.Modal.getOrCreateInstance($modal[0]) : null;
  const $form = $('#productForm');
  const $submitButton = $('#productSubmitBtn');
  const $table = $('.products-datatables');
  const $formCategory = $('#product_category_id');
  const $formSubCategory = $('#product_sub_category_id');
  const $filterCategory = $('#product_filter_category');
  const $filterSubCategory = $('#product_filter_sub_category');
  const $trackInventoryToggle = $('#product_track_inventory_toggle');
  const mediaDropzoneElement = document.getElementById('product_images_dropzone');
  const mediaManager = window.AppMediaDropzone ? window.AppMediaDropzone.create(mediaDropzoneElement, {
    removedInputName: 'removed_image_ids[]'
  }) : null;
  let productTable = null;

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

  const parseJsonAttribute = function (value) {
    if (!value) {
      return [];
    }

    try {
      return JSON.parse(value);
    } catch (error) {
      return [];
    }
  };

  const setSubmitButtonState = function (loading) {
    const isEdit = Boolean($('#product_id').val());
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

  const setInventoryFieldsState = function () {
    const enabled = $trackInventoryToggle.is(':checked');
    $('.inventory-field').prop('disabled', !enabled).toggleClass('bg-label-secondary', !enabled);
  };

  const resetValidationState = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
    setSelect2ErrorState($formCategory, false);
    setSelect2ErrorState($formSubCategory, false);
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

  const initSubCategorySelect = function ($element, getCategoryId) {
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
        url: window.subCategoryDropdownUrl,
        delay: 250,
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term || '',
            page: params.page || 1,
            category_id: getCategoryId()
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

  const clearSubCategorySelect = function ($select) {
    $select.val(null).trigger('change');
    $select.find('option').not(':first').remove();
  };

  const resetForm = function () {
    $form[0].reset();
    $('#product_id').val('');
    $('#product_cost_price').val('0.00');
    $('#product_sale_price').val('0.00');
    $('#product_opening_stock').val('0');
    $('#product_current_stock').val('0');
    $('#product_minimum_stock_level').val('0');
    $('#product_reorder_level').val('0');
    $('#product_is_active').prop('checked', true);
    $('#product_track_inventory_toggle').prop('checked', true);
    $('#product_type').val(Object.keys(window.productTypes)[0]).trigger('change');
    ensureSelectOption($formCategory, null, null);
    clearSubCategorySelect($formSubCategory);
    if (mediaManager) {
      mediaManager.reset();
    }
    $('#productModalLabel').text('Add Product');
    setSubmitButtonState(false);
    resetValidationState();
    setInventoryFieldsState();
  };

  const fillForm = function ($button) {
    $('#product_id').val($button.data('id'));
    ensureSelectOption($formCategory, $button.data('category-id'), $button.data('category-name'));
    ensureSelectOption($formSubCategory, $button.data('sub-category-id'), $button.data('sub-category-name'));
    $('#product_type').val(String($button.data('product-type'))).trigger('change');
    $('#product_name').val($button.data('name'));
    $('#product_sku').val($button.data('sku'));
    $('#product_barcode').val($button.data('barcode'));
    $('#product_brand').val($button.data('brand'));
    $('#product_unit').val($button.data('unit'));
    $('#product_description').val($button.data('description'));
    $('#product_cost_price').val($button.data('cost-price'));
    $('#product_sale_price').val($button.data('sale-price'));
    $('#product_tax_percentage').val($button.data('tax-percentage'));
    $('#product_opening_stock').val($button.data('opening-stock'));
    $('#product_current_stock').val($button.data('current-stock'));
    $('#product_minimum_stock_level').val($button.data('minimum-stock-level'));
    $('#product_reorder_level').val($button.data('reorder-level'));
    $('#product_is_active').prop('checked', String($button.data('is-active')) === '1');
    $('#product_track_inventory_toggle').prop('checked', String($button.data('track-inventory')) === '1');
    if (mediaManager) {
      mediaManager.loadExisting(parseJsonAttribute($button.attr('data-images')));
    }
    $('#productModalLabel').text('Edit Product');
    setSubmitButtonState(false);
    resetValidationState();
    setInventoryFieldsState();
  };

  const money = function (value) {
    const amount = Number(value || 0);
    return '$' + amount.toFixed(2);
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center">';
    const imagePayload = escapeHtml(JSON.stringify(row.images || []));

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-product-btn" ' +
        'data-bs-toggle="modal" data-bs-target="#productModal" ' +
        'data-id="' + row.id + '" ' +
        'data-category-id="' + (row.category_id || '') + '" ' +
        'data-category-name="' + escapeHtml(row.category_name || '') + '" ' +
        'data-sub-category-id="' + (row.sub_category_id || '') + '" ' +
        'data-sub-category-name="' + escapeHtml(row.sub_category_name || '') + '" ' +
        'data-product-type="' + escapeHtml(row.product_type) + '" ' +
        'data-name="' + escapeHtml(row.name) + '" ' +
        'data-sku="' + escapeHtml(row.sku || '') + '" ' +
        'data-barcode="' + escapeHtml(row.barcode || '') + '" ' +
        'data-brand="' + escapeHtml(row.brand || '') + '" ' +
        'data-unit="' + escapeHtml(row.unit || '') + '" ' +
        'data-description="' + escapeHtml(row.description || '') + '" ' +
        'data-cost-price="' + row.cost_price + '" ' +
        'data-sale-price="' + row.sale_price + '" ' +
        'data-tax-percentage="' + (row.tax_percentage || '') + '" ' +
        'data-opening-stock="' + row.opening_stock + '" ' +
        'data-current-stock="' + row.current_stock + '" ' +
        'data-minimum-stock-level="' + row.minimum_stock_level + '" ' +
        'data-reorder-level="' + row.reorder_level + '" ' +
        'data-track-inventory="' + (row.track_inventory ? 1 : 0) + '" ' +
        'data-is-active="' + (row.is_active ? 1 : 0) + '" ' +
        'data-images="' + imagePayload + '" title="Edit">' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-product-btn" ' +
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
        product_type: {
          required: true
        },
        name: {
          required: true,
          maxlength: 150
        },
        sku: {
          maxlength: 80
        },
        barcode: {
          maxlength: 80
        },
        brand: {
          maxlength: 120
        },
        unit: {
          maxlength: 50
        },
        description: {
          maxlength: 2000
        },
        cost_price: {
          required: true,
          number: true,
          min: 0
        },
        sale_price: {
          required: true,
          number: true,
          min: 0
        },
        tax_percentage: {
          number: true,
          min: 0,
          max: 100
        },
        opening_stock: {
          number: true,
          min: 0
        },
        current_stock: {
          number: true,
          min: 0
        },
        minimum_stock_level: {
          number: true,
          min: 0
        },
        reorder_level: {
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

    productTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        url: window.productListingUrl,
        data: function (d) {
          d.status = $('#product_status').val();
          d.category_id = $filterCategory.val();
          d.sub_category_id = $filterSubCategory.val();
          d.product_type = $('#product_type_filter').val();
          d.track_inventory = $('#product_track_inventory').val();
          d.sort = $('#product_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search by name, SKU, barcode or brand',
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
        emptyTable: 'No products found',
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
          data: 'sub_category_name',
          render: function (data) {
            return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: 'name',
          render: function (data, type, row) {
            const sku = row.sku ? '<small class="text-muted d-block">' + escapeHtml(row.sku) + '</small>' : '';
            const thumbnail = row.primary_image_url
              ? '<img src="' + escapeHtml(row.primary_image_url) + '" alt="' + escapeHtml(data) + '" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">'
              : '<span class="avatar avatar-sm rounded bg-label-secondary me-2"><i class="ti tabler-photo"></i></span>';

            return '<div class="d-flex align-items-center"><div class="flex-shrink-0">' + thumbnail + '</div><div><span class="fw-semibold">' + escapeHtml(data) + '</span>' + sku + '</div></div>';
          }
        },
        {
          data: 'product_type_label',
          render: function (data) {
            return '<span class="badge bg-label-info">' + escapeHtml(data) + '</span>';
          }
        },
        {
          data: 'sku',
          render: function (data) {
            return escapeHtml(data || '—');
          }
        },
        {
          data: 'brand',
          render: function (data) {
            return escapeHtml(data || '—');
          }
        },
        {
          data: 'sale_price',
          render: function (data) {
            return '<span class="text-nowrap">' + money(data) + '</span>';
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            return '<div class="text-nowrap">' +
              '<span class="d-block fw-medium">' + escapeHtml(row.current_stock) + '</span>' +
              '<small class="badge ' + row.stock_badge_class + '">' + escapeHtml(row.stock_status_label) + '</small>' +
              '</div>';
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
    if (productTable) {
      productTable.ajax.reload(null, false);
    }
  };

  const bindFilters = function () {
    $('#product_status, #product_type_filter, #product_track_inventory, #product_sort').on('change', reloadTable);

    $filterCategory.on('change', function () {
      clearSubCategorySelect($filterSubCategory);
      reloadTable();
    });

    $filterSubCategory.on('change', reloadTable);
  };

  const bindFormInteractions = function () {
    $trackInventoryToggle.on('change', setInventoryFieldsState);

    $formCategory.on('change', function () {
      clearSubCategorySelect($formSubCategory);
    });
  };

  const bindModalActions = function (validator) {
    $(document).on('click', '#addProductBtn', function () {
      resetForm();
      if (validator) {
        validator.resetForm();
      }
    });

    $(document).on('click', '.edit-product-btn', function () {
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

  const appendServerErrors = function (errors, validator) {
    if (errors.images && mediaManager) {
      mediaManager.showError(errors.images[0]);
    }

    if (errors.primary_image_ref && mediaManager) {
      mediaManager.showError(errors.primary_image_ref[0]);
    }

    if (errors.removed_image_ids && mediaManager) {
      mediaManager.showError(errors.removed_image_ids[0]);
    }

    if (validator) {
      validator.showErrors(Object.fromEntries(
        Object.entries(errors).map(function (entry) {
          return [entry[0], entry[1][0]];
        })
      ));
    }
  };

  const bindSaveForm = function (validator) {
    $form.on('submit', function (event) {
      event.preventDefault();
      resetValidationState();
      if (mediaManager) {
        mediaManager.clearError();
      }

      if (validator && !$form.valid()) {
        return;
      }

      const formData = new FormData($form[0]);

      setSubmitButtonState(true);
      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Saving product...');
      }

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
      })
        .done(function (response) {
          if (modal) {
            modal.hide();
          }

          reloadTable();
          showAlert('success', response.message || 'Product saved successfully.');
        })
        .fail(function (xhr) {
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            if (xhr.responseJSON.errors.id) {
              showAlert('error', xhr.responseJSON.errors.id[0]);
            }

            appendServerErrors(xhr.responseJSON.errors, validator);
            return;
          }

          showAlert('error', xhr.responseJSON?.message || 'Unable to save product.');
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
    $(document).on('click', '.delete-product-btn', function () {
      const $button = $(this);
      const deleteUrl = $button.data('url');
      const name = $button.data('name');

      Swal.fire({
        title: 'Delete product?',
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
        if (!result.isConfirmed) {
          return;
        }

        $button.prop('disabled', true);
        if (window.appLoading && typeof window.appLoading.show === 'function') {
          window.appLoading.show('Deleting product...');
        }

        $.ajax({
          url: deleteUrl,
          method: 'DELETE'
        })
          .done(function (response) {
            reloadTable();
            showAlert('success', response.message || 'Product deleted successfully.');
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete product.');
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
    initSubCategorySelect($formSubCategory, function () {
      return $formCategory.val();
    });
    initSubCategorySelect($filterSubCategory, function () {
      return $filterCategory.val();
    });

    const validator = bindFormValidation();

    initDataTable();
    bindFilters();
    bindFormInteractions();
    bindModalActions(validator);
    bindSaveForm(validator);
    bindDeleteButton();
    resetForm();
  });
})(jQuery);
