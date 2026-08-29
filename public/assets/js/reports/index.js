(function ($) {
  'use strict';

  const config = window.reportConfig || {};
  const $table = $('.reports-datatable');
  let reportTable = null;

  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const escapeHtml = function (value) {
    return $('<div>').text(value === null || value === undefined ? '' : value).html();
  };

  // Collect the current filter selection into a flat object shared by the
  // DataTable request and the export link.
  const collectFilters = function () {
    const params = {
      period: $('#report-period').val()
    };

    if (params.period === 'custom') {
      params.date_from = $('#report-start').val();
      params.date_to = $('#report-end').val();
    }

    if (config.hasDateColumn) {
      params.date_column = $('#report-date-column').val();
    }

    (config.filterKeys || []).forEach(function (key) {
      const value = $('#report-filter-' + key).val();
      if (value !== '' && value !== null && value !== undefined) {
        params[key] = value;
      }
    });

    return params;
  };

  const updateExportLink = function () {
    const params = collectFilters();
    const query = $.param(params);
    $('#report-export').attr('href', config.exportUrl + (query ? '?' + query : ''));
  };

  // The export button is only usable when the current (filtered) result set has rows.
  const setExportEnabled = function (enabled) {
    $('#report-export')
      .toggleClass('disabled', !enabled)
      .attr('aria-disabled', enabled ? 'false' : 'true')
      .attr('tabindex', enabled ? null : '-1');
  };

  // Numbers (currency especially) are isolated as LTR so symbols like AED's
  // don't bidi-reorder next to the digits.
  const ltr = function (value) {
    return '<span dir="ltr" style="unicode-bidi:isolate;white-space:nowrap;">' + escapeHtml(value) + '</span>';
  };

  const getCardMeta = function (label) {
    const l = (label || '').toLowerCase();
    if (l.includes('order')) {
      return { icon: 'tabler-shopping-cart', tone: 'info' };
    }
    if (l.includes('gross') || l.includes('net') || l.includes('sales')) {
      return { icon: 'tabler-currency-dollar', tone: 'primary' };
    }
    if (l.includes('collect') || l.includes('paid')) {
      return { icon: 'tabler-cash', tone: 'success' };
    }
    if (l.includes('outstand') || l.includes('refund') || l.includes('due')) {
      return { icon: 'tabler-clock-dollar', tone: 'danger' };
    }
    if (l.includes('low stock')) {
      return { icon: 'tabler-alert-triangle', tone: 'warning' };
    }
    if (l.includes('product') || l.includes('stock')) {
      return { icon: 'tabler-package', tone: 'primary' };
    }
    if (l.includes('customer')) {
      return { icon: 'tabler-users', tone: 'primary' };
    }
    if (l.includes('lifetime') || l.includes('value')) {
      return { icon: 'tabler-star', tone: 'success' };
    }
    return { icon: 'tabler-chart-bar', tone: 'secondary' };
  };

  const renderSummary = function (summary) {
    const $container = $('#report-summary');

    if (!summary || !summary.length) {
      $container.empty();
      return;
    }

    const perRow = Math.min(summary.length, 4);
    let cols = '';
    summary.forEach(function (card) {
      const meta = getCardMeta(card.label);
      cols +=
        '<div class="col">' +
          '<div class="pos-glass-card pos-tone-' + meta.tone + ' h-100">' +
            '<div class="pos-stat-body">' +
              '<div class="pos-stat-head">' +
                '<span class="pos-stat-icon"><i class="icon-base ti ' + meta.icon + '" aria-hidden="true"></i></span>' +
                '<h6 class="pos-stat-label">' + escapeHtml(card.label) + '</h6>' +
              '</div>' +
              '<p class="pos-stat-value mb-0">' + ltr(card.value) + '</p>' +
            '</div>' +
          '</div>' +
        '</div>';
    });

    $container.html('<div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-' + perRow + '">' + cols + '</div>');
  };

  const buildColumns = function () {
    const columns = [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
          return meta.settings._iDisplayStart + meta.row + 1;
        }
      }
    ];

    (config.columns || []).forEach(function (col) {
      columns.push({
        data: col.key,
        orderable: !!col.orderable,
        className: 'text-' + (col.align || 'start'),
        render: function (data) {
          // End-aligned columns are monetary/numeric — isolate as LTR.
          return col.align === 'end' ? ltr(data) : escapeHtml(data);
        }
      });
    });

    return columns;
  };

  const initDataTable = function () {
    if (typeof DataTable === 'undefined' || !$table.length) {
      return;
    }

    reportTable = new DataTable($table[0], {
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      order: [],
      ajax: {
        global: false, // table/dropdown has its own indicator — skip the global overlay
        url: config.dataUrl,
        data: function (d) {
          $.extend(d, collectFilters());
        }
      },
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      columns: buildColumns(),
      layout: {
        topStart: null,
        topEnd: null,
        bottomStart: {
          rowClass: 'row mx-3 my-md-0 me-3 ms-0 justify-content-between',
          features: ['info', { pageLength: { menu: [10, 25, 50, 100], text: '_MENU_' } }]
        },
        bottomEnd: 'paging'
      },
      language: {
        emptyTable: config.emptyLabel || 'No records found',
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      drawCallback: function () {
        const json = this.api().ajax.json();
        renderSummary(json ? json.summary : []);
        updateExportLink();
        setExportEnabled(!!json && Number(json.recordsTotal) > 0);
      }
    });
  };

  // Restore every control to its default and reload once.
  const resetFilters = function () {
    $('#report-period').val('month');
    $('#report-date-column').each(function () {
      this.selectedIndex = 0;
    });
    $('select.report-filter').not('#report-period').not('#report-date-column').val('');
    $('input.report-filter').val('');
    $('#report-search').val('');

    if (typeof $.fn.select2 === 'function') {
      $('select.report-filter').each(function () {
        if ($(this).data('select2')) {
          $(this).trigger('change.select2');
        }
      });
    }

    $('.report-custom-range').addClass('d-none');

    if (reportTable) {
      reportTable.search('').draw();
    }
  };

  // Give every filter <select> the same Select2 dropdown UI used across the app.
  const initSelect2 = function () {
    if (typeof $.fn.select2 !== 'function') {
      return;
    }

    $('select.report-filter').each(function () {
      const $el = $(this);
      if ($el.data('select2')) {
        return;
      }

      if (!$el.parent().hasClass('position-relative')) {
        $el.wrap('<div class="position-relative"></div>');
      }

      const hasAllOption = $el.find('option[value=""]').length > 0;

      $el.select2({
        dropdownParent: $el.parent(),
        placeholder: $el.data('placeholder') || ($el.find('option[value=""]').text() || 'Select'),
        allowClear: hasAllOption,
        width: '100%'
      });
    });
  };

  const bindControls = function () {
    $('#report-period').on('change', function () {
      $('.report-custom-range').toggleClass('d-none', $(this).val() !== 'custom');
    });

    // Custom search box in the card header drives the server-side DataTable search.
    let searchTimer = null;
    $('#report-search').on('keyup search', function () {
      const value = this.value;
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () {
        if (reportTable) {
          reportTable.search(value).draw();
        }
      }, 300);
    });

    $(document).on('change select2:select select2:clear keyup', '.report-filter', function (e) {
      // Debounce number inputs lightly; reload on change otherwise.
      if (e.type === 'keyup' && this.type !== 'number') {
        return;
      }
      if (reportTable) {
        reportTable.ajax.reload(null, false);
      }
    });

    $('#report-export').on('click', function (e) {
      if ($(this).hasClass('disabled')) {
        e.preventDefault();
        return;
      }
      updateExportLink();
    });

    $('#report-reset').on('click', resetFilters);
  };

  $(function () {
    initSelect2();
    initDataTable();
    bindControls();
    updateExportLink();
  });
})(window.jQuery);
