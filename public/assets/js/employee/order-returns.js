$(function() {
    const config = window.employeeReturnsConfig || {};
    const listingUrl = config.listingUrl || '';
    const historyUrl = config.historyUrl || '';
    const returnUrlTemplate = config.returnUrlTemplate || '';
    const returnDays = config.returnDays || 30;

    let selectedOrder = null;
    let returnModal = null;

    // Initialize modal
    returnModal = new bootstrap.Modal(document.getElementById('returnConfirmationModal'));

    // Load returns listing
    function loadReturns(search = '') {
        const $list = $('[data-return-list]');
        const $loading = $('[data-return-loading]');
        const $empty = $('[data-return-empty]');

        $list.addClass('d-none');
        $empty.addClass('d-none');
        $loading.removeClass('d-none');

        $.ajax({
            url: listingUrl,
            method: 'GET',
            data: { q: search },
            success: function(response) {
                $loading.addClass('d-none');

                if (response.orders && response.orders.length > 0) {
                    $list.removeClass('d-none');
                    renderReturns(response.orders);
                } else {
                    $empty.removeClass('d-none');
                }
            },
            error: function() {
                $loading.addClass('d-none');
                $empty.removeClass('d-none');
            }
        });
    }

    // Load returns history
    function loadHistory(search = '') {
        const $list = $('[data-history-list]');
        const $loading = $('[data-history-loading]');
        const $empty = $('[data-history-empty]');

        $list.addClass('d-none');
        $empty.addClass('d-none');
        $loading.removeClass('d-none');

        $.ajax({
            url: historyUrl,
            method: 'GET',
            data: { q: search },
            success: function(response) {
                $loading.addClass('d-none');

                if (response.orders && response.orders.length > 0) {
                    $list.removeClass('d-none');
                    renderHistory(response.orders);
                } else {
                    $empty.removeClass('d-none');
                }
            },
            error: function() {
                $loading.addClass('d-none');
                $empty.removeClass('d-none');
            }
        });
    }

    // Render returns list
    function renderReturns(orders) {
        const $list = $('[data-return-list]');
        $list.empty();

        orders.forEach(function(order) {
            const eligibleClass = order.is_eligible ? 'eligible' : 'ineligible';
            const eligibleBadge = order.is_eligible
                ? '<span class="badge bg-success">Eligible</span>'
                : '<span class="badge bg-danger">Expired</span>';
            const returnBtnDisabled = order.is_eligible ? '' : 'disabled';
            const returnBtnClass = order.is_eligible ? 'btn-danger' : 'btn-secondary';

            const html = `
                <div class="employee-order-card ${eligibleClass}" data-order-id="${order.id}">
                    <div class="employee-order-card-header">
                        <div class="employee-order-card-title">
                            <span class="order-number">${escape(order.order_number)}</span>
                            ${eligibleBadge}
                        </div>
                        <div class="employee-order-card-actions">
                            <button type="button" class="btn btn-sm ${returnBtnClass} return-order-btn" ${returnBtnDisabled}
                                data-order-id="${order.id}"
                                data-order-number="${escape(order.order_number)}"
                                data-customer-name="${escape(order.customer_name)}"
                                data-total-amount="${order.total_amount_label}"
                                data-refundable-amount="${order.refundable_amount_label}"
                                data-days-since-payment="${order.days_since_payment}"
                                data-items='${JSON.stringify(order.items || [])}'
                                data-service-fee-amount="${order.service_fee_amount || 0}"
                                data-discount-amount="${order.discount_amount || 0}"
                                data-total-items="${order.total_items || 0}">
                                Return Order
                            </button>
                        </div>
                    </div>
                    <div class="employee-order-card-body">
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Customer:</span>
                            <span class="employee-order-card-value">${escape(order.customer_name)}</span>
                        </div>
                        ${order.vehicle_label ? `
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Vehicle:</span>
                            <span class="employee-order-card-value">${escape(order.vehicle_label)}</span>
                        </div>
                        ` : ''}
                        ${order.plate_number ? `
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Plate:</span>
                            <span class="employee-order-card-value">${escape(order.plate_number)}</span>
                        </div>
                        ` : ''}
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Total:</span>
                            <span class="employee-order-card-value">${order.total_amount_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Refundable:</span>
                            <span class="employee-order-card-value text-success fw-bold">${order.refundable_amount_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Paid:</span>
                            <span class="employee-order-card-value">${order.paid_at_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Days Since:</span>
                            <span class="employee-order-card-value">${order.days_since_payment} / ${returnDays}</span>
                        </div>
                    </div>
                </div>
            `;

            $list.append(html);
        });
    }

    // Render returned orders history
    function renderHistory(orders) {
        const $list = $('[data-history-list]');
        $list.empty();

        orders.forEach(function(order) {
            const html = `
                <div class="employee-order-card returned" data-order-id="${order.id}">
                    <div class="employee-order-card-header">
                        <div class="employee-order-card-title">
                            <span class="order-number">${escape(order.order_number)}</span>
                            <span class="badge bg-danger">Returned</span>
                        </div>
                    </div>
                    <div class="employee-order-card-body">
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Customer:</span>
                            <span class="employee-order-card-value">${escape(order.customer_name)}</span>
                        </div>
                        ${order.vehicle_label ? `
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Vehicle:</span>
                            <span class="employee-order-card-value">${escape(order.vehicle_label)}</span>
                        </div>
                        ` : ''}
                        ${order.plate_number ? `
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Plate:</span>
                            <span class="employee-order-card-value">${escape(order.plate_number)}</span>
                        </div>
                        ` : ''}
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Total Amount:</span>
                            <span class="employee-order-card-value">${order.total_amount_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Refund Amount:</span>
                            <span class="employee-order-card-value text-danger fw-bold">${order.refund_amount_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Refund Method:</span>
                            <span class="employee-order-card-value">${order.refund_method_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Refunded At:</span>
                            <span class="employee-order-card-value">${order.refunded_at_label}</span>
                        </div>
                        <div class="employee-order-card-row">
                            <span class="employee-order-card-label">Items:</span>
                            <span class="employee-order-card-value">${order.items_count}</span>
                        </div>
                    </div>
                </div>
            `;

            $list.append(html);
        });
    }

    // Handle return button click
    $(document).on('click', '.return-order-btn', function() {
        const $btn = $(this);
        selectedOrder = {
            id: $btn.data('order-id'),
            order_number: $btn.data('order-number'),
            customer_name: $btn.data('customer-name'),
            total_amount: $btn.data('total-amount'),
            refundable_amount: $btn.data('refundable-amount'),
            days_since_payment: $btn.data('days-since-payment'),
            items: $btn.data('items') || [],
            service_fee_amount: parseFloat($btn.data('service-fee-amount') || 0),
            discount_amount: parseFloat($btn.data('discount-amount') || 0),
            total_items: parseInt($btn.data('total-items') || 0)
        };

        // Populate modal
        $('#returnOrderNumber').text(selectedOrder.order_number);
        $('#returnCustomerName').text(selectedOrder.customer_name);
        $('#returnTotalAmount').text(selectedOrder.total_amount);
        $('#returnDaysSincePayment').text(selectedOrder.days_since_payment + ' days');

        // Render items for selection
        renderReturnItems(selectedOrder.items);

        // Reset form
        $('#returnForm')[0].reset();

        // Show modal
        returnModal.show();
    });

    // Render return items with quantity selectors
    function renderReturnItems(items) {
        const $list = $('#returnItemsList');
        $list.empty();

        if (!items || items.length === 0) {
            $list.html('<p class="text-muted">No items in this order.</p>');
            return;
        }

        items.forEach(function(item) {
            const availableQty = item.available_quantity || item.quantity;
            const returnedQty = item.returned_quantity || 0;
            const html = `
                <div class="return-item-row d-flex align-items-center justify-content-between p-2 border-bottom" data-item-id="${item.id}" data-unit-price="${item.unit_price}">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${escape(item.product_name)}</div>
                        <div class="text-muted small">${escape(item.sku || 'N/A')} | ${item.unit_price_label} each | Total: ${item.quantity} | Returned: ${returnedQty} | Available: ${availableQty}</div>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="me-2 small">Return Qty:</label>
                        <input type="number" class="form-control form-control-sm return-item-qty" style="width: 80px;"
                            min="0" max="${availableQty}" value="0" data-max-qty="${availableQty}" data-item-id="${item.id}">
                    </div>
                </div>
            `;
            $list.append(html);
        });

        // Add event listeners for quantity changes
        $('.return-item-qty').on('input change', calculateRefundAmount);
    }

    // Calculate refund amount based on selected items
    function calculateRefundAmount() {
        let totalRefund = 0;
        let totalReturnedItems = 0;

        $('.return-item-qty').each(function() {
            const $input = $(this);
            const qty = parseInt($input.val()) || 0;
            const maxQty = parseInt($input.data('max-qty')) || 0;
            const unitPrice = parseFloat($input.closest('.return-item-row').data('unit-price')) || 0;

            // Validate quantity
            if (qty < 0) {
                $input.val(0);
            } else if (qty > maxQty) {
                $input.val(maxQty);
            }

            const validQty = Math.min(Math.max(qty, 0), maxQty);
            totalRefund += validQty * unitPrice;
            totalReturnedItems += validQty;
        });

        // Calculate proportional discount only (service fee is not refundable for partial returns)
        const totalOrderItems = selectedOrder.total_items || 0;
        let proportionalDiscount = 0;

        if (totalOrderItems > 0 && totalReturnedItems > 0) {
            proportionalDiscount = selectedOrder.discount_amount > 0
                ? Math.round(selectedOrder.discount_amount * (totalReturnedItems / totalOrderItems) * 100) / 100
                : 0;
        }

        // Subtract proportional discount only
        totalRefund = totalRefund - proportionalDiscount;

        // Round to 2 decimal places to avoid floating point precision issues
        totalRefund = Math.round(totalRefund * 100) / 100;

        $('#calculatedRefundAmount').text(formatMoney(totalRefund));
        return totalRefund;
    }

    // Format money
    function formatMoney(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }

    // Handle confirm return
    $('#confirmReturnBtn').on('click', function() {
        const $btn = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text');

        // Validate form
        const returnReason = $('#returnReason').val().trim();
        const refundMethod = $('#refundMethod').val();

        if (!returnReason) {
            alert('Please provide a return reason.');
            return;
        }

        if (!refundMethod) {
            alert('Please select a refund method.');
            return;
        }

        // Collect selected items and quantities
        const returnItems = [];
        let totalReturnQty = 0;

        $('.return-item-qty').each(function() {
            const $input = $(this);
            const qty = parseInt($input.val()) || 0;
            const itemId = $input.data('item-id');
            const maxQty = parseInt($input.data('max-qty')) || 0;

            if (qty > 0 && qty <= maxQty) {
                returnItems.push({
                    order_item_id: itemId,
                    quantity: qty
                });
                totalReturnQty += qty;
            }
        });

        if (returnItems.length === 0 || totalReturnQty === 0) {
            alert('Please select at least one item to return.');
            return;
        }

        const calculatedRefund = calculateRefundAmount();

        // Disable button and show spinner
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $btnText.text('Processing...');

        const returnUrl = returnUrlTemplate.replace('__ORDER_ID__', selectedOrder.id);

        $.ajax({
            url: returnUrl,
            method: 'POST',
            data: {
                return_reason: returnReason,
                refund_method: refundMethod,
                return_items: returnItems,
                refund_amount: calculatedRefund,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                returnModal.hide();
                alert(response.message || 'Order returned successfully.');
                loadReturns($('[data-return-search]').val());
                loadHistory($('[data-history-search]').val());
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to process return. Please try again.';
                alert(message);
            },
            complete: function() {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $btnText.text('Process Return');
            }
        });
    });

    // Handle search for eligible returns
    let searchTimeout;
    $(document).on('input', '[data-return-search]', function() {
        clearTimeout(searchTimeout);
        const search = $(this).val().trim();
        searchTimeout = setTimeout(function() {
            loadReturns(search);
        }, 300);
    });

    // Handle search for returned history
    let historySearchTimeout;
    $(document).on('input', '[data-history-search]', function() {
        clearTimeout(historySearchTimeout);
        const search = $(this).val().trim();
        historySearchTimeout = setTimeout(function() {
            loadHistory(search);
        }, 300);
    });

    // Handle refresh for eligible returns
    $(document).on('click', '[data-return-refresh]', function() {
        loadReturns($('[data-return-search]').val().trim());
    });

    // Handle refresh for returned history
    $(document).on('click', '[data-history-refresh]', function() {
        loadHistory($('[data-history-search]').val().trim());
    });

    // Initial load
    loadReturns();
    loadHistory();

    // Utility function
    function escape(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
