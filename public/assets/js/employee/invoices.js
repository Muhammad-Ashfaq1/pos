(function ($) {
  'use strict';

  if (typeof $ === 'undefined') return;
  if (window.__employeeInvoicesInitialized) return;
  window.__employeeInvoicesInitialized = true;

  const config = window.employeeInvoicesConfig || {};
  const $page = $('.employee-invoices-page');

  if (!$page.length || !config.listingUrl) {
    return;
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': config.csrfToken || $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const state = {
    q: '',
    date_from: '',
    date_to: '',
    amount_min: '',
    amount_max: '',
    status: '',
    page: 1,
    per_page: 20,
    loading: false,
    last_page: 1
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const debounce = function (callback, delay) {
    let timeout = null;

    return function () {
      const args = arguments;
      const context = this;
      window.clearTimeout(timeout);
      timeout = window.setTimeout(function () {
        callback.apply(context, args);
      }, delay);
    };
  };

  const notify = function (type, message) {
    if (window.Notiflix && window.Notiflix.Notify) {
      window.Notiflix.Notify[type](message);
      return;
    }

    window.alert(message);
  };

  const statusBadge = function (invoice) {
    const map = {
      paid: 'bg-label-success',
      partially_paid: 'bg-label-warning',
      pending: 'bg-label-secondary',
      returned: 'bg-label-danger'
    };
    const cls = map[invoice.status] || 'bg-label-secondary';

    return '<span class="badge rounded ' + cls + '">' + escapeHtml(invoice.status_label || invoice.status) + '</span>';
  };

  const renderRows = function (invoices) {
    const $tbody = $page.find('[data-invoice-list]');
    const $empty = $page.find('[data-invoice-empty]');

    if (!invoices.length) {
      $tbody.html('');
      $empty.removeClass('d-none');
      return;
    }

    $empty.addClass('d-none');

    const html = invoices.map(function (invoice) {
      return [
        '<tr data-invoice-row data-show-url="' + escapeHtml(invoice.show_url) + '">',
        '<td><span class="fw-bold text-primary">' + escapeHtml(invoice.order_number) + '</span></td>',
        '<td>' + escapeHtml(invoice.invoice_date_label) + '</td>',
        '<td>' + escapeHtml(invoice.due_date_label) + '</td>',
        '<td>' + escapeHtml(invoice.customer_name) + '</td>',
        '<td>' + escapeHtml(invoice.item_description) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.unit_price_label) + '</td>',
        '<td class="text-center">' + escapeHtml(invoice.quantity_label) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.tax_amount_label) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.total_amount_label) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.service_fee_amount_label) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.subtotal_amount_label) + '</td>',
        '<td class="text-end">' + escapeHtml(invoice.balance_due_label) + '</td>',
        '<td class="text-center">' + statusBadge(invoice) + '</td>',
        '<td class="text-center">',
        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-invoice-email',
        ' data-share-url="' + escapeHtml(invoice.share_url) + '"',
        ' data-email="' + escapeHtml(invoice.customer_email || '') + '"',
        ' title="Send Email"><i class="icon-base ti tabler-mail icon-md"></i></button>',
        '</td>',
        '<td class="text-center">',
        '<a class="btn btn-sm btn-icon btn-outline-secondary" href="' + escapeHtml(invoice.print_url) + '" target="_blank" rel="noopener" title="Print"',
        ' data-invoice-print><i class="icon-base ti tabler-printer icon-md"></i></a>',
        '</td>',
        '</tr>'
      ].join('');
    }).join('');

    $tbody.html(html);
  };

  const updatePagination = function (pagination) {
    const $wrap = $page.find('[data-invoice-pagination]');
    if (!pagination || !pagination.total) {
      $wrap.addClass('d-none');
      return;
    }

    state.last_page = pagination.last_page || 1;
    state.page = pagination.current_page || state.page;
    state.per_page = pagination.per_page || state.per_page;

    const firstItem = pagination.total
      ? ((pagination.current_page - 1) * pagination.per_page) + 1
      : 0;
    const lastItem = Math.min(
      pagination.current_page * pagination.per_page,
      pagination.total
    );

    $wrap.removeClass('d-none');
    $page.find('[data-invoice-page-label]').text(
      'Showing ' + firstItem + ' to ' + lastItem + ' of ' + pagination.total + ' entries'
    );
    $page.find('[data-invoice-current-page]').text(String(pagination.current_page));
    $page.find('[data-invoice-per-page]').val(String(pagination.per_page));

    const atFirst = pagination.current_page <= 1;
    const atLast = !pagination.has_more;
    $page.find('[data-invoice-prev]').prop('disabled', atFirst);
    $page.find('[data-invoice-next]').prop('disabled', atLast);
    $page.find('[data-invoice-prev-item]').toggleClass('disabled', atFirst);
    $page.find('[data-invoice-next-item]').toggleClass('disabled', atLast);
  };

  let listRequest = null;

  const loadInvoices = function () {
    if (listRequest) {
      listRequest.abort();
      listRequest = null;
    }

    state.loading = true;

    const $tbody = $page.find('[data-invoice-list]');
    $tbody.html(
      '<tr data-invoice-loading><td colspan="15" class="text-center text-muted py-5">Loading invoices…</td></tr>'
    );
    $page.find('[data-invoice-empty]').addClass('d-none');

    listRequest = $.ajax({
      url: config.listingUrl,
      method: 'GET',
      data: {
        q: state.q || undefined,
        date_from: state.date_from || undefined,
        date_to: state.date_to || undefined,
        amount_min: state.amount_min !== '' ? state.amount_min : undefined,
        amount_max: state.amount_max !== '' ? state.amount_max : undefined,
        status: state.status || undefined,
        page: state.page,
        per_page: state.per_page,
        sort: 'latest'
      }
    }).done(function (response) {
      renderRows(response.invoices || []);
      updatePagination(response.pagination || null);
    }).fail(function (xhr) {
      if (xhr.statusText === 'abort') {
        return;
      }
      $tbody.html(
        '<tr><td colspan="15" class="text-center text-danger py-5">Failed to load invoices.</td></tr>'
      );
      notify('failure', (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load invoices.');
    }).always(function (xhr, textStatus) {
      if (textStatus === 'abort') {
        return;
      }
      state.loading = false;
      listRequest = null;
    });
  };

  const initStaticSelect2 = function () {
    const $selects = $page.find('.select2');

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

  const readFiltersFromForm = function () {
    state.date_from = $page.find('[data-invoice-date-from]').val() || '';
    state.date_to = $page.find('[data-invoice-date-to]').val() || '';
    state.amount_min = $page.find('[data-invoice-amount-min]').val() || '';
    state.amount_max = $page.find('[data-invoice-amount-max]').val() || '';
    state.status = $page.find('[data-invoice-status]').val() || '';
    state.page = 1;
  };

  const resetFilters = function () {
    state.q = '';
    state.date_from = '';
    state.date_to = '';
    state.amount_min = '';
    state.amount_max = '';
    state.status = '';
    state.page = 1;
    $page.find('[data-invoice-search]').val('');
    $page.find('[data-invoice-date-from]').val('');
    $page.find('[data-invoice-date-to]').val('');
    $page.find('[data-invoice-amount-min]').val('');
    $page.find('[data-invoice-amount-max]').val('');
    $page.find('[data-invoice-status]').val('').trigger('change');
    loadInvoices();
  };

  $page.on('input', '[data-invoice-search]', debounce(function () {
    state.q = $(this).val().trim();
    state.page = 1;
    loadInvoices();
  }, 350));

  $page.on('change', '[data-invoice-filter-control]', function () {
    readFiltersFromForm();
    loadInvoices();
  });

  $page.on('click', '[data-invoice-reset]', function () {
    resetFilters();
  });

  $page.on('click', '[data-invoice-prev]', function () {
    if ($(this).prop('disabled') || state.page <= 1) return;
    state.page -= 1;
    loadInvoices();
  });

  $page.on('click', '[data-invoice-next]', function () {
    if ($(this).prop('disabled') || state.page >= state.last_page) return;
    state.page += 1;
    loadInvoices();
  });

  $page.on('change', '[data-invoice-per-page]', function () {
    const val = parseInt($(this).val(), 10);
    if (!val || val === state.per_page) return;
    state.per_page = val;
    state.page = 1;
    loadInvoices();
  });

  $page.on('click', '[data-invoice-row]', function (event) {
    if ($(event.target).closest('[data-invoice-email], [data-invoice-print]').length) {
      return;
    }

    let url = $(this).attr('data-show-url');
    if (!url) {
      return;
    }

    if (!/(?:^|[?&])from=/.test(url)) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + 'from=invoices';
    }

    window.location.href = url;
  });

  $page.on('click', '[data-invoice-email]', function (event) {
    event.preventDefault();
    event.stopPropagation();

    const $btn = $(this);
    $page.find('[data-invoice-share-url]').val($btn.data('share-url') || '');
    $page.find('[data-invoice-share-email]').val($btn.data('email') || '');

    const modalEl = document.getElementById('invoiceShareModal');
    if (modalEl && window.bootstrap) {
      window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
  });

  $page.on('submit', '#invoice-share-form', function (event) {
    event.preventDefault();

    const shareUrl = $page.find('[data-invoice-share-url]').val();
    const email = $page.find('[data-invoice-share-email]').val();
    const $submit = $page.find('[data-invoice-share-submit]');

    if (!shareUrl || !email) {
      notify('failure', 'Email and invoice are required.');
      return;
    }

    $submit.prop('disabled', true);

    $.ajax({
      url: shareUrl,
      method: 'POST',
      data: { email: email }
    }).done(function (response) {
      notify('success', response.message || 'Invoice emailed successfully.');
      const modalEl = document.getElementById('invoiceShareModal');
      if (modalEl && window.bootstrap) {
        const instance = window.bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();
      }
    }).fail(function (xhr) {
      notify('failure', (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to send invoice email.');
    }).always(function () {
      $submit.prop('disabled', false);
    });
  });

  initStaticSelect2();
  loadInvoices();
})(window.jQuery);
