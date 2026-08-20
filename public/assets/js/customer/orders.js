'use strict';

(function ($, api) {
  if (!$ || !api) {
    return;
  }

  const $list = $('#cp-orders-list');
  const $footer = $('#cp-orders-pagination');
  let currentPage = 1;

  const orderShowUrl = function (id) {
    return (api.routes.orderShow || '/portal/orders/__ID__').replace('__ID__', id);
  };

  const render = function (orders) {
    if (!orders || !orders.length) {
      $list.html(api.emptyHtml('tabler-clipboard-list', 'No visits yet.'));
      return;
    }

    $list.html(orders.map(function (order) {
      let creditHtml = '';
      if (Number(order.credit_earned) > 0) {
        creditHtml = '<div class="small text-success">+' +
          api.escapeHtml(order.credit_earned_label || order.credit_earned) +
          ' credit earned</div>';
      }

      return '<a href="' + api.escapeHtml(orderShowUrl(order.id)) + '" class="cp-list-item">' +
        '<div>' +
        '<div class="fw-semibold">' + api.escapeHtml(order.order_number) + '</div>' +
        '<div class="small text-muted">' + api.escapeHtml(order.created_at_label || '') +
        ' · ' + api.escapeHtml(String(order.items_count || 0)) + ' item(s)</div>' +
        creditHtml +
        '</div>' +
        '<div class="text-end">' +
        '<div class="fw-bold">' + api.escapeHtml(order.total_amount_label) + '</div>' +
        '<span class="badge bg-label-' + api.escapeHtml(order.status_class || 'warning') +
        ' text-capitalize">' + api.escapeHtml(order.status_label || order.status || '') + '</span>' +
        '</div></a>';
    }).join(''));
  };

  const load = function (page) {
    currentPage = page || 1;
    $list.html(api.loadingHtml('Loading service history...'));

    api.get('/orders', { per_page: 15, page: currentPage })
      .then(function (payload) {
        render(payload.data || []);
        api.renderPagination($footer, payload.meta, load);
      })
      .catch(function (error) {
        const message = api.showError(error, 'Unable to load orders.');
        $list.html(api.emptyHtml('tabler-alert-circle', message));
        $footer.addClass('d-none').empty();
      });
  };

  $(function () {
    load(1);
  });
})(window.jQuery, window.CustomerApi);
