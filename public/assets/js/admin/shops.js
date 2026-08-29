(function ($) {
  'use strict';

  const config = window.adminShops || {};
  const $table = $('#shop-table');
  const $shopSaveModal = $('#shopSaveModal');
  const $form = $('#shopSaveForm');

  if (!$table.length || !$form.length) {
    return;
  }

  if (config.csrfToken) {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json'
      }
    });
  }

  const showToast = function (type, message) {
    if (!message) {
      return;
    }

    if (typeof window.appNotify === 'function') {
      window.appNotify(type, message);
      return;
    }

    if (typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify) {
      const methodMap = {
        success: 'success',
        error: 'failure',
        warning: 'warning',
        info: 'info'
      };
      const method = methodMap[type] || 'info';

      if (typeof window.Notiflix.Notify[method] === 'function') {
        window.Notiflix.Notify[method](message);
      }
    }
  };

  const getModalInstance = function () {
    if (!$shopSaveModal.length || !window.bootstrap || !window.bootstrap.Modal) {
      return null;
    }

    return window.bootstrap.Modal.getOrCreateInstance($shopSaveModal[0]);
  };

  const buildUrl = function (template, replacements) {
    let url = template || '';

    Object.keys(replacements || {}).forEach(function (key) {
      url = url.replace('__' + key.toUpperCase() + '__', replacements[key]);
    });

    return url;
  };

  const $searchInput = $('#shopTableSearch');

  const formatDate = function (date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  };

  const applySelectedPlanExpiry = function () {
    const $selected = $('#shop_plan_id option:selected');
    const duration = parseInt($selected.data('duration'), 10);
    if (!$selected.val() || !duration) {
      return;
    }
    const expiry = new Date();
    expiry.setDate(expiry.getDate() + duration);
    const formatted = formatDate(expiry);
    if (window.AppDatepicker && typeof window.AppDatepicker.set === 'function') {
      window.AppDatepicker.set('#shop_plan_expires_at', formatted);
      return;
    }
    $('#shop_plan_expires_at').val(formatted);
  };

  const bindShopSearch = function () {
    $searchInput.off('input.shopSearch').on('input.shopSearch', function () {
      if (shopTable) {
        shopTable.search(this.value).draw();
      }
    });
  };

  const datatableOptions = {
    // Keep every management column available at high browser zoom. The surrounding
    // responsive container supplies horizontal scrolling instead of collapsing
    // columns into a details row.
    responsive: false,
    processing: true,
    order: [],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    layout: {
      topStart: null,
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
      emptyTable: 'No shops found',
      search: '',
      searchPlaceholder: 'Search shops'
    },
    columnDefs: [
      { orderable: false, targets: [7, 8] }
    ]
  };

  let shopTable = $table.DataTable(datatableOptions);
  bindShopSearch();

  const reinitializeShopTable = function () {
    const searchValue = $searchInput.val() || '';

    shopTable.destroy();

    $.get(window.location.href, function (html) {
      const $rows = $('<div>').html(html).find('#shop-table-body').contents();
      $('#shop-table-body').empty().append($rows);
      shopTable = $table.DataTable(datatableOptions);
      bindShopSearch();

      if (searchValue) {
        $searchInput.val(searchValue);
        shopTable.search(searchValue).draw(false);
      }
    });
  };

  const clearFormErrors = function () {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
  };

  const resetShopForm = function () {
    $form[0].reset();
    $('#shop_id').val('');
    clearFormErrors();
    $('#shopModalTitle').text('Add Shop');
    $('#shopSubmitBtn').text($('#shopSubmitBtn').data('create-text') || 'Save Shop');
    $('#shop_password_star').removeClass('d-none');
    $('#shopPasswordHelp').addClass('d-none');
    $('#shop_plan_id').val('');
    $('#shop_plan_expires_at').val('');
  };

  const notify = function (type, message) {
    showToast(type, message);
  };

  const postStatusChangeRequest = function (shopId, action, reason) {
    const payload = { _token: config.csrfToken };

    if (reason) {
      payload.reason = reason;
    }

    return $.ajax({
      url: buildUrl(config.statusUrl, { id: shopId, action: action }),
      method: 'POST',
      dataType: 'json',
      data: payload
    });
  };

  const postStatusChange = function (shopId, action, reason, options) {
    options = options || {};

    return postStatusChangeRequest(shopId, action, reason)
      .done(function (response) {
        if (response && response.success) {
          if (options.showSuccessToast !== false) {
            showToast('success', response.message || 'Status updated.');
          }
          reinitializeShopTable();
        }
      })
      .fail(function (xhr) {
        showToast('error', xhr.responseJSON?.message || 'Unable to update shop status.');
      });
  };

  const confirmReject = function (shopId, shopName) {
    if (!window.PosConfirm || typeof window.PosConfirm.prompt !== 'function') {
      return;
    }

    window.PosConfirm.prompt({
      title: 'Reject shop?',
      message: shopName ? 'This will reject "' + shopName + '".' : 'This shop registration will be rejected.',
      label: 'Reason',
      placeholder: 'Add a short audit note',
      required: true,
      confirmText: 'Yes, reject it',
      cancelText: 'Cancel',
      tone: 'danger',
      onConfirm: function (reason) {
        return postStatusChangeRequest(shopId, 'reject', reason).then(
          function (response) {
            if (!response || !response.success) {
              throw new Error((response && response.message) || 'Unable to reject shop.');
            }

            reinitializeShopTable();
          },
          function (xhr) {
            throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to reject shop.');
          }
        );
      }
    });
  };

  $('#addShopBtn').on('click', resetShopForm);

  $('#shop_plan_id').on('change', function () {
    if (!$(this).val()) {
      $('#shop_plan_expires_at').val('');
      return;
    }
    applySelectedPlanExpiry();
  });

  $shopSaveModal.on('shown.bs.modal', function () {
    if (window.AppDatepicker && typeof window.AppDatepicker.init === 'function') {
      window.AppDatepicker.init($shopSaveModal[0]);
    }
  });

  $(document).on('click', '.edit-shop-btn', function (e) {
    e.preventDefault();
    const shopId = $(this).data('id');

    resetShopForm();
    $('#shopModalTitle').text('Edit Shop');
    $('#shopSubmitBtn').text($('#shopSubmitBtn').data('update-text') || 'Update Shop');
    $('#shop_password_star').addClass('d-none');
    $('#shopPasswordHelp').removeClass('d-none');

    if (window.appLoading && typeof window.appLoading.show === 'function') {
      window.appLoading.show('Loading shop...');
    }

    $.ajax({
      url: buildUrl(config.editUrl, { id: shopId }),
      method: 'GET'
    })
      .done(function (response) {
        if (!response.success || !response.data) {
          return;
        }

        const data = response.data;
        $('#shop_id').val(data.id);
        $('#shop_owner_name').val(data.owner_name);
        $('#shop_owner_email').val(data.owner_email);
        $('#shop_name_input').val(data.shop_name);
        $('#shop_status_select').val(data.status);
        $('#shop_website_url').val(data.website_url);
        $('#shop_business_type').val(data.business_type);
        $('#shop_country').val(data.country);
        $('#shop_state').val(data.state);
        $('#shop_city').val(data.city);
        $('#shop_phone').val(data.phone);
        $('#shop_address').val(data.address);
        $('#shop_plan_id').val(data.plan_id || '');
        if (window.AppDatepicker && typeof window.AppDatepicker.set === 'function') {
          window.AppDatepicker.set('#shop_plan_expires_at', data.plan_expires_at || '');
        } else {
          $('#shop_plan_expires_at').val(data.plan_expires_at || '');
        }

        const modal = getModalInstance();
        if (modal) {
          modal.show();
        }
      })
      .fail(function (xhr) {
        notify('error', xhr.responseJSON?.message || 'Unable to load shop details.');
      })
      .always(function () {
        if (window.appLoading && typeof window.appLoading.hide === 'function') {
          window.appLoading.hide(200);
        }
      });
  });

  $form.on('submit', function (e) {
    e.preventDefault();
    clearFormErrors();

    const websiteUrl = $('#shop_website_url').val().trim();
    if (websiteUrl !== '' && !/^https?:\/\//i.test(websiteUrl)) {
      $('#shop_website_url').addClass('is-invalid');
      $('#shop_website_url').siblings('.invalid-feedback').text('Website URL must start with http:// or https://');
      return;
    }

    const $submitBtn = $('#shopSubmitBtn');
    $submitBtn.prop('disabled', true);

    $.ajax({
      url: config.saveUrl || $form.attr('action'),
      method: 'POST',
      data: $form.serialize()
    })
      .done(function (response) {
        if (response.success) {
          notify('success', response.message);
          const modal = getModalInstance();
          if (modal) {
            modal.hide();
          }
          reinitializeShopTable();
        }
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          Object.entries(xhr.responseJSON.errors).forEach(function (entry) {
            const field = entry[0];
            const messages = entry[1];
            const $input = $form.find('[name="' + field + '"]');

            if ($input.length) {
              $input.addClass('is-invalid');
              $input.siblings('.invalid-feedback').text(messages[0]);
            }
          });
          return;
        }

        notify('error', xhr.responseJSON?.message || 'Save failed.');
      })
      .always(function () {
        $submitBtn.prop('disabled', false);
      });
  });

  $(document).on('click', '.shop-action-btn', function () {
    const shopId = $(this).data('id');
    const action = $(this).data('action');
    const shopName = $(this).closest('tr').find('td').eq(4).text().trim();

    if (action === 'reject') {
      confirmReject(shopId, shopName);
      return;
    }

    postStatusChange(shopId, action);
  });

  $(document).on('click', '.shop-impersonate-btn', function () {
    const shopId = $(this).data('id');
    const shopName = $(this).data('shop-name') || 'this shop';

    if (!window.PosConfirm || typeof window.PosConfirm.open !== 'function') {
      return;
    }

    window.PosConfirm.open({
      title: 'Impersonate shop?',
      message: 'You will sign in as "' + shopName + '". You can stop impersonation from the sidebar.',
      confirmText: 'Yes, impersonate',
      cancelText: 'Cancel',
      tone: 'warning',
      onConfirm: function () {
        window.location.href = buildUrl(config.impersonateUrl, { id: shopId });
      }
    });
  });
})(jQuery);
