'use strict';

(function ($, api) {
  if (!$ || !api) {
    return;
  }

  const $creditValue = $('#cp-dashboard-credit');
  const $creditMeta = $('#cp-dashboard-credit-meta');
  const $shopMeta = $('#cp-dashboard-shop');
  const $visits = $('#cp-dashboard-visits');
  const $lifetime = $('#cp-dashboard-lifetime');
  const $recent = $('#cp-dashboard-recent');

  const orderShowUrl = function (id) {
    const template = (api.routes.orderShow || '/portal/orders/__ID__');
    return template.replace('__ID__', id);
  };

  const renderRecent = function (orders) {
    if (!orders || !orders.length) {
      $recent.html(api.emptyHtml('tabler-clipboard-list', 'No visits yet.'));
      return;
    }

    const html = orders.map(function (order) {
      const vehicle = order.vehicle || {};
      let vehicleHtml = '';
      if (vehicle.label || vehicle.plate_number) {
        vehicleHtml = '<div class="small text-muted mt-1"><i class="ti tabler-car me-1"></i>' +
          api.escapeHtml(vehicle.label || '') +
          (vehicle.plate_number ? ' · ' + api.escapeHtml(vehicle.plate_number) : '') +
          '</div>';
      }

      let creditHtml = '';
      if (Number(order.credit_earned) > 0) {
        creditHtml = '<div class="small text-success">+' + api.escapeHtml(order.credit_earned_label || order.credit_earned) +
          ' credit earned</div>';
      }

      return '<a href="' + api.escapeHtml(orderShowUrl(order.id)) + '" class="cp-list-item">' +
        '<div class="d-flex align-items-start gap-3">' +
        '<span class="cp-order-icon"><i class="ti tabler-file-invoice"></i></span>' +
        '<div>' +
        '<div class="fw-semibold">' + api.escapeHtml(order.order_number) + '</div>' +
        '<div class="small text-muted">' + api.escapeHtml(order.created_at_label || '') +
        ' · ' + api.escapeHtml(String(order.items_count || 0)) + ' item(s)</div>' +
        vehicleHtml + creditHtml +
        '</div></div>' +
        '<div class="text-end">' +
        '<div class="fw-bold">' + api.escapeHtml(order.total_amount_label) + '</div>' +
        '<span class="badge bg-label-' + api.escapeHtml(order.status_class || 'warning') +
        ' text-capitalize">' + api.escapeHtml(order.status_label || order.status || '') + '</span>' +
        '</div></a>';
    }).join('');

    $recent.html(html);
  };

  const load = function () {
    $recent.html(api.loadingHtml('Loading recent visits...'));

    api.get('/dashboard', { recent_limit: 5 })
      .then(function (payload) {
        const data = payload.data || {};
        const customer = data.customer || {};
        const credit = data.credit || {};
        const shop = customer.shop || {};

        $creditValue.text(credit.balance_label || customer.credit_balance_label || '—');
        $shopMeta.text(shop.name ? ('at ' + shop.name) : '');

        if (credit.can_redeem) {
          $creditMeta.html('<span class="badge bg-label-success">Ready to use at checkout</span>');
        } else {
          $creditMeta.html(
            '<div class="cp-hero-meta mt-3">Usable when balance reaches ' +
            api.escapeHtml(credit.min_redeem_balance_label || '') + '.</div>'
          );
        }

        $visits.text(customer.total_visits != null ? customer.total_visits : '—');
        $lifetime.text(customer.lifetime_value_label || '—');
        renderRecent(data.recent_orders || []);
      })
      .catch(function (error) {
        const message = api.showError(error, 'Unable to load dashboard.');
        $recent.html(api.emptyHtml('tabler-alert-circle', message));
      });
  };

  $(load);
})(window.jQuery, window.CustomerApi);
