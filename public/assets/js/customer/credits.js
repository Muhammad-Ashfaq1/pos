'use strict';

(function ($, api) {
  if (!$ || !api) {
    return;
  }

  const $balance = $('#cp-credits-balance');
  const $meta = $('#cp-credits-meta');
  const $list = $('#cp-credits-list');
  const $footer = $('#cp-credits-pagination');
  const $filters = $('#cp-credits-filters');
  let currentType = '';
  let currentPage = 1;

  const orderShowUrl = function (id) {
    return (api.routes.orderShow || '/portal/orders/__ID__').replace('__ID__', id);
  };

  const badgeClass = function (type) {
    if (type === 'earn') return 'bg-label-success';
    if (type === 'redeem') return 'bg-label-danger';
    if (type === 'adjust') return 'bg-label-warning';
    return 'bg-label-secondary';
  };

  const syncFilters = function () {
    $filters.find('[data-type]').each(function () {
      const type = String($(this).data('type') || '');
      $(this)
        .toggleClass('btn-primary', type === currentType)
        .toggleClass('btn-outline-secondary', type !== currentType);
    });
  };

  const renderMeta = function (meta) {
    $balance.text(meta.balance_label || '—');
    if (meta.can_redeem) {
      $meta.html('<span class="badge bg-label-success">Ready to use at checkout</span>');
    } else {
      $meta.html(
        '<div class="cp-hero-meta mt-3">Usable when balance reaches ' +
        api.escapeHtml(meta.min_redeem_balance_label || '') + '.</div>'
      );
    }
  };

  const render = function (rows) {
    if (!rows || !rows.length) {
      $list.html(api.emptyHtml('tabler-wallet', 'No credit activity yet.'));
      return;
    }

    $list.html(rows.map(function (row) {
      let orderHtml = '';
      if (row.order_id && row.order_number) {
        orderHtml = '<a href="' + api.escapeHtml(orderShowUrl(row.order_id)) +
          '" class="fw-semibold">' + api.escapeHtml(row.order_number) + '</a>';
      }

      return '<div class="cp-list-item"><div>' +
        '<div class="d-flex align-items-center gap-2 mb-1">' +
        '<span class="badge ' + badgeClass(row.type) + ' text-capitalize">' +
        api.escapeHtml(row.type) + '</span>' + orderHtml +
        '</div>' +
        '<div class="small text-muted">' + api.escapeHtml(row.created_at_label || '') +
        (row.description ? ' · ' + api.escapeHtml(row.description) : '') +
        '</div></div>' +
        '<div class="text-end">' +
        '<div class="fw-bold ' + (Number(row.amount) >= 0 ? 'text-success' : 'text-danger') + '">' +
        api.escapeHtml(row.amount_label || '') + '</div>' +
        '<div class="small text-muted">Bal: ' + api.escapeHtml(row.balance_after_label || '') +
        '</div></div></div>';
    }).join(''));
  };

  const load = function (page) {
    currentPage = page || 1;
    syncFilters();
    $list.html(api.loadingHtml('Loading credit history...'));

    const params = { per_page: 20, page: currentPage };
    if (currentType) {
      params.type = currentType;
    }

    api.get('/credits', params)
      .then(function (payload) {
        renderMeta(payload.meta || {});
        render(payload.data || []);
        api.renderPagination($footer, payload.meta, load);
      })
      .catch(function (error) {
        const message = api.showError(error, 'Unable to load credits.');
        $list.html(api.emptyHtml('tabler-alert-circle', message));
        $footer.addClass('d-none').empty();
      });
  };

  $filters.on('click', '[data-type]', function (event) {
    event.preventDefault();
    currentType = String($(this).data('type') || '');
    load(1);
  });

  $(function () {
    const params = new URLSearchParams(window.location.search);
    currentType = params.get('type') || '';
    load(1);
  });
})(window.jQuery, window.CustomerApi);
