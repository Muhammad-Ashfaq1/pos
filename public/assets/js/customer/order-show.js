'use strict';

(function ($, api) {
  if (!$ || !api) {
    return;
  }

  const orderId = Number(window.customerOrderId || 0);
  const $root = $('#cp-order-show');
  const $title = $('#cp-order-title');
  const $subtitle = $('#cp-order-subtitle');
  const $status = $('#cp-order-status');
  const $pdf = $('#cp-order-pdf');
  const $body = $('#cp-order-body');

  if (!orderId) {
    return;
  }

  const load = function () {
    $body.html(api.loadingHtml('Loading order...'));

    api.get('/orders/' + orderId)
      .then(function (payload) {
        const order = payload.data || {};
        $title.text(order.order_number || 'Order');
        $subtitle.text(order.created_at_label || '');
        $status
          .attr('class', 'badge bg-label-' + (order.status_class || 'warning'))
          .text(order.status_label || order.status || '');

        if (api.routes.orderPdf) {
          $pdf.attr('href', api.routes.orderPdf.replace('__ID__', order.id || orderId)).removeClass('d-none');
        }

        const items = (order.items || []).map(function (item) {
          return '<tr>' +
            '<td>' + api.escapeHtml(item.product_name) + '</td>' +
            '<td class="text-end">' + api.escapeHtml(item.quantity_label || item.quantity) + '</td>' +
            '<td class="text-end">' + api.escapeHtml(item.line_total_label || '') + '</td>' +
            '</tr>';
        }).join('');

        let html = '<table class="table table-sm mb-3"><thead><tr>' +
          '<th>Item</th><th class="text-end">Qty</th><th class="text-end">Total</th>' +
          '</tr></thead><tbody>' + (items || '<tr><td colspan="3" class="text-muted">No items</td></tr>') +
          '</tbody></table>';

        html += '<div class="d-flex justify-content-between"><span>Subtotal</span><strong>' +
          api.escapeHtml(order.subtotal_amount_label || '') + '</strong></div>';

        if (Number(order.discount_amount) > 0) {
          html += '<div class="d-flex justify-content-between text-danger"><span>Discount</span><strong>-' +
            api.escapeHtml(order.discount_amount_label || '') + '</strong></div>';
        }

        if (Number(order.tax_amount) > 0) {
          html += '<div class="d-flex justify-content-between"><span>Tax</span><strong>' +
            api.escapeHtml(order.tax_amount_label || '') + '</strong></div>';
        }

        if (Number(order.credit_applied) > 0) {
          html += '<div class="d-flex justify-content-between text-primary"><span>Store credit used</span><strong>-' +
            api.escapeHtml(order.credit_applied_label || '') + '</strong></div>';
        }

        html += '<div class="d-flex justify-content-between border-top pt-2 mt-2">' +
          '<span class="fw-bold">Total</span><strong>' +
          api.escapeHtml(order.total_amount_label || '') + '</strong></div>';

        if (Number(order.credit_earned) > 0) {
          html += '<div class="alert alert-success mt-3 mb-0 py-2">' +
            '<i class="ti tabler-coin me-1"></i>You earned ' +
            api.escapeHtml(order.credit_earned_label || '') +
            ' in store credit on this visit.</div>';
        }

        if (order.payment_history && order.payment_history.length) {
          html += '<h6 class="mt-4 mb-2 fw-bold">Payments</h6><table class="table table-sm mb-0"><tbody>';
          html += order.payment_history.map(function (payment) {
            return '<tr>' +
              '<td class="text-muted small">' + api.escapeHtml(payment.created_at_label || '') + '</td>' +
              '<td>' + api.escapeHtml(payment.payment_method_label || '') + '</td>' +
              '<td class="text-end">' + api.escapeHtml(payment.amount_label || '') + '</td>' +
              '</tr>';
          }).join('');
          html += '</tbody></table>';
        }

        $body.html(html);
        $root.removeClass('d-none');
      })
      .catch(function (error) {
        const message = api.showError(error, 'Unable to load order.');
        $body.html(api.emptyHtml('tabler-alert-circle', message));
      });
  };

  $(load);
})(window.jQuery, window.CustomerApi);
