(function ($) {
  'use strict';

  const $table = $('.customers-datatables');
  const vehicleRequired = window.customerSettings?.vehicleRequired !== undefined
    ? Boolean(window.customerSettings.vehicleRequired)
    : true;
  let customerTable = null;
  let customerManager = null;

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const customerEditUrl = function (customerId) {
    return (window.customerEditUrlTemplate || '').replace('__CUSTOMER__', customerId);
  };

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

  const money = function (value) {
    const amount = Number(value || 0);
    return ((window.appCurrencySymbol && window.appCurrencySymbol()) ||
        (window.appCurrency && window.appCurrency.symbol) ||
        '$') + amount.toFixed(2);
  };

  const tooltipAttrs = function (title) {
    return window.Helpers && window.Helpers.getTooltipAttributes
      ? window.Helpers.getTooltipAttributes(title)
      : 'title="' + title + '"';
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center gap-1">';

    if (vehicleRequired && row.vehicles_index_url) {
      html +=
        '<a href="' + row.vehicles_index_url + '" class="btn btn-sm btn-icon btn-outline-secondary" ' + tooltipAttrs('View Vehicles') + '>' +
        '<i class="icon-base ti tabler-car"></i>' +
        '</a>';
    }

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-customer-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || customerEditUrl(row.id)) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit"></i>' +
        '</button>';
    }

    if (row.impersonate_portal_url) {
      html +=
        '<a href="' + escapeHtml(row.impersonate_portal_url) + '" class="btn btn-sm btn-icon btn-text-warning impersonate-btn" ' +
        'title="Impersonate ' + escapeHtml(row.name) + '" data-name="' + escapeHtml(row.name) + '">' +
        '<i class="ti tabler-user-check"></i>' +
        '</a>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-customer-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
        '<i class="icon-base ti tabler-trash"></i>' +
        '</button>';
    }

    html += '</div>';

    return html;
  };

  const initDataTable = function () {
    if (typeof DataTable === 'undefined' || !$table.length) {
      return;
    }

    const columns = [
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
          if (vehicleRequired && row.default_vehicle_plate) {
            html += '<div class="small text-muted">Default Vehicle: ' + escapeHtml(row.default_vehicle_plate) + '</div>';
          }
          html += '</div>';
          return html;
        }
      },
      {
        data: 'customer_type_label',
        render: function (data) {
          return '<span class="badge rounded bg-label-primary">' + escapeHtml(data || '—') + '</span>';
        }
      },
      {
        data: null,
        render: function (data, type, row) {
          const phone = row.phone ? escapeHtml(row.phone) : '—';
          const email = row.email ? escapeHtml(row.email) : '—';
          return '<div><div>' + phone + '</div><div class="small text-muted">' + email + '</div></div>';
        }
      }
    ];

    if (vehicleRequired) {
      columns.push({
        data: 'vehicles_count',
        render: function (data) {
          return '<span class="badge rounded bg-label-info">' + escapeHtml(String(data ?? 0)) + '</span>';
        }
      });
    }

    columns.push(
      { data: 'total_visits' },
      {
        data: 'lifetime_value',
        render: function (data) {
          return '<span class="text-nowrap">' + money(data) + '</span>';
        }
      },
      {
        data: 'last_visit_at_label',
        render: function (data) {
          return '<span class="text-nowrap">' + escapeHtml(data || '—') + '</span>';
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
    );

    customerTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
        global: false, // table/dropdown has its own indicator — skip the global overlay
        url: window.customerListingUrl,
        data: function (d) {
          d.customer_type = $('#customer_type_filter').val();
          d.sort = $('#customer_sort').val();
        }
      },
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      layout: {
        topStart: {
          search: {
            placeholder: vehicleRequired
              ? 'Search by name, phone, email or vehicle'
              : 'Search by name, phone or email',
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
        emptyTable: 'No customers found',
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      columns: columns,
      drawCallback: function () {
        alignCreateButtonWithSearch(this.api(), '#customerTableActions');
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });

    alignCreateButtonWithSearch(customerTable, '#customerTableActions');
  };

  const bindFilters = function () {
    $('#customer_type_filter, #customer_sort').on('change', function () {
      if (customerTable) {
        customerTable.ajax.reload(null, false);
      }
    });
  };

  const bindEditActions = function () {
    $(document).on('click', '.edit-customer-btn', function () {
      const editUrl = $(this).data('edit-url') || customerEditUrl($(this).data('id'));

      if (window.appLoading && typeof window.appLoading.show === 'function') {
        window.appLoading.show('Loading customer...');
      }

      $.get(editUrl)
        .done(function (response) {
          if (customerManager) {
            customerManager.fillForm(response.data || {});
            if (customerManager.modal) {
              customerManager.modal.show();
            }
          }
        })
        .fail(function (xhr) {
          showAlert('error', xhr.responseJSON?.message || 'Unable to load customer.');
        })
        .always(function () {
          if (window.appLoading && typeof window.appLoading.hide === 'function') {
            window.appLoading.hide(200);
          }
        });
    });
  };

  const bindDeleteActions = function () {
    $(document).on('click', '.delete-customer-btn', function () {
      const url = $(this).data('url');
      const name = $(this).data('name') || 'this customer';

      if (!window.PosConfirm || typeof window.PosConfirm.open !== 'function') {
        return;
      }

      window.PosConfirm.open({
        title: 'Delete Customer?',
        message: 'This will also remove linked vehicles for ' + name + '.',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel',
        tone: 'danger',
        onConfirm: function () {
          return $.ajax({
            url: url,
            method: 'DELETE'
          }).then(
            function (response) {
              showAlert('success', response.message || 'Customer deleted successfully.');
              if (customerTable) {
                customerTable.ajax.reload(null, false);
              }
            },
            function (xhr) {
              throw new Error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete customer.');
            }
          );
        }
      });
    });
  };

  const bindImpersonateActions = function () {
    $(document).on('click', '.impersonate-btn', function (e) {
      e.preventDefault();

      const name = $(this).data('name') || 'this customer';
      const href = $(this).attr('href');

      if (!href) {
        return;
      }

      Swal.fire({
        title: 'Impersonate User?',
        text: 'You will be logged in as "' + name + '". You can stop impersonation from the sidebar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, impersonate',
      }).then(function (result) {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    });
  };

  $(function () {
    if (typeof window.CustomerManager === 'function') {
      customerManager = new window.CustomerManager({
        onSaveSuccess: function () {
          if (customerTable) {
            customerTable.ajax.reload(null, false);
          }
        }
      });
    }

    initDataTable();
    bindFilters();
    bindEditActions();
    bindDeleteActions();
    bindImpersonateActions();
  });
})(window.jQuery);
