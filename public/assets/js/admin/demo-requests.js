(function ($) {
  'use strict';

  const config = window.adminDemoRequests || {};
  const $table = $('#demo-requests-table');
  const $modalEl = $('#demoRequestModal');
  const $searchInput = $('#demoTableSearch');
  let demoTable = null;

  if (!$table.length) {
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

  const notify = function (type, message) {
    if (typeof window.appNotify === 'function') {
      window.appNotify(type, message);
    }
  };

  const getModalInstance = function () {
    if (!$modalEl.length || !window.bootstrap || !window.bootstrap.Modal) {
      return null;
    }

    return window.bootstrap.Modal.getOrCreateInstance($modalEl[0]);
  };

  const buildUrl = function (template, replacements) {
    let url = template || '';

    Object.keys(replacements || {}).forEach(function (key) {
      url = url.replace('__' + key.toUpperCase() + '__', replacements[key]);
    });

    return url;
  };

  const bindDemoSearch = function () {
    $searchInput.off('input.demoSearch').on('input.demoSearch', function () {
      if (demoTable) {
        demoTable.search(this.value).draw();
      }
    });
  };

  const datatableOptions = {
    responsive: false,
    processing: true,
    order: [[5, 'desc']],
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
      emptyTable: 'No demo requests found',
      search: '',
      searchPlaceholder: 'Search name, business, email or phone'
    },
    columnDefs: [
      { orderable: false, targets: [4, 6] }
    ]
  };

  demoTable = $table.DataTable(datatableOptions);
  bindDemoSearch();

  if (window.PosListingToolbar) {
    window.PosListingToolbar.align(demoTable);
  }

  const reinitializeDemoTable = function () {
    const searchValue = $searchInput.val() || '';

    demoTable.destroy();

    $.get(window.location.href, function (html) {
      const $rows = $('<div>').html(html).find('#demo-requests-table-body').contents();
      $('#demo-requests-table-body').empty().append($rows);
      demoTable = $table.DataTable(datatableOptions);
      bindDemoSearch();

      if (searchValue) {
        $searchInput.val(searchValue);
        demoTable.search(searchValue).draw(false);
      }

      if (window.PosListingToolbar) {
        window.PosListingToolbar.align(demoTable);
      }
    });
  };

  $(document).on('click', '.manage-demo-btn', function () {
    const $btn = $(this);

    $('#demoRequestId').val($btn.data('id'));
    $('#demoModalName').text($btn.data('name') || '-');
    $('#demoModalBusiness').text($btn.data('business') || 'No business name provided');
    $('#demoModalEmail').text($btn.data('email') || '-');
    $('#demoModalPhone').text($btn.data('phone') || '-');
    $('#demoModalType').text($btn.data('type') || '—');
    $('#demoModalMessage').text($btn.data('message') || 'No message provided.');
    $('#demoStatusSelect').val(String($btn.data('status')));
    $('#demoNotes').val($btn.data('notes') || '');

    const badge = $btn.closest('tr').find('.status-badge');
    $('#demoModalStatusBadge').html(
      '<span class="badge ' + (badge.attr('class') || '').replace('status-badge', '').trim() + '">' + badge.text() + '</span>'
    );

    const modal = getModalInstance();
    if (modal) {
      modal.show();
    }
  });

  $('#demoSaveBtn').on('click', function () {
    const id = $('#demoRequestId').val();
    const $btn = $(this);

    $btn.prop('disabled', true);

    $.ajax({
      url: buildUrl(config.statusUrl, { id: id }),
      method: 'POST',
      data: {
        _token: config.csrfToken,
        status: $('#demoStatusSelect').val(),
        admin_notes: $('#demoNotes').val()
      }
    })
      .done(function (response) {
        if (response.success) {
          notify('success', response.message || 'Demo request updated.');
          const modal = getModalInstance();
          if (modal) {
            modal.hide();
          }
          reinitializeDemoTable();
        }
      })
      .fail(function (xhr) {
        notify('error', xhr.responseJSON?.message || 'Update failed.');
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });
})(jQuery);
