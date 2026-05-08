(function ($) {
  'use strict';

  const $table = $('.customers-datatables');
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

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const money = function (value) {
    const amount = Number(value || 0);
    return '$' + amount.toFixed(2);
  };

  const tooltipAttrs = function (title) {
    return window.Helpers && window.Helpers.getTooltipAttributes
      ? window.Helpers.getTooltipAttributes(title)
      : 'title="' + title + '"';
  };

  const actionButtonsHtml = function (row) {
    let html = '<div class="d-flex align-items-center justify-content-center gap-1">';

    if (row.vehicles_index_url) {
      html +=
        '<a href="' + row.vehicles_index_url + '" class="btn btn-icon btn-text-secondary rounded-pill waves-effect" ' + tooltipAttrs('View Vehicles') + '>' +
        '<i class="icon-base ti tabler-car icon-md"></i>' +
        '</a>';
    }

    if (row.can_update) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-customer-btn" ' +
        'data-id="' + row.id + '" data-edit-url="' + escapeHtml(row.edit_url || customerEditUrl(row.id)) + '" ' + tooltipAttrs('Edit') + '>' +
        '<i class="icon-base ti tabler-edit icon-md"></i>' +
        '</button>';
    }

    if (row.can_delete && row.delete_url) {
      html +=
        '<button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-customer-btn" ' +
        'data-url="' + row.delete_url + '" data-name="' + escapeHtml(row.name) + '" ' + tooltipAttrs('Delete') + '>' +
        '<i class="icon-base ti tabler-trash icon-md text-danger"></i>' +
        '</button>';
    }

    html += '</div>';

    return html;
  };

  const initDataTable = function () {
    if (typeof DataTable === 'undefined' || !$table.length) {
      return;
    }

    customerTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      ajax: {
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
            placeholder: 'Search by name, phone, email or vehicle',
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
            if (row.default_vehicle_plate) {
              html += '<div class="small text-muted">Default Vehicle: ' + escapeHtml(row.default_vehicle_plate) + '</div>';
            }
            html += '</div>';
            return html;
          }
        },
        {
          data: 'customer_type_label',
          render: function (data) {
            return '<span class="badge bg-label-primary">' + escapeHtml(data || '—') + '</span>';
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            const phone = row.phone ? escapeHtml(row.phone) : '—';
            const email = row.email ? escapeHtml(row.email) : '—';
            return '<div><div>' + phone + '</div><div class="small text-muted">' + email + '</div></div>';
          }
        },
        {
          data: 'vehicles_count',
          render: function (data) {
            return '<span class="badge bg-label-info">' + escapeHtml(String(data ?? 0)) + '</span>';
          }
        },
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
      ],
      drawCallback: function () {
        if (window.Helpers && window.Helpers.initToolTip) {
          window.Helpers.initToolTip(this.api().table().container());
        }
      }
    });
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

      Swal.fire({
        title: 'Delete Customer?',
        text: 'This will also remove linked vehicles for ' + name + '.',
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
          window.appLoading.show('Deleting customer...');
        }

        $.ajax({
          url: url,
          method: 'DELETE'
        })
          .done(function (response) {
            showAlert('success', response.message || 'Customer deleted successfully.');
            if (customerTable) {
              customerTable.ajax.reload(null, false);
            }
          })
          .fail(function (xhr) {
            showAlert('error', xhr.responseJSON?.message || 'Unable to delete customer.');
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
  });
})(window.jQuery);

