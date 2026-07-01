$(function () {
    const config = window.employeeReturnsConfig || {};
    const listingUrl = config.listingUrl || '';
    const historyUrl = config.historyUrl || '';
    const returnUrlTemplate = config.returnUrlTemplate || '';
    const returnDays = config.returnDays || 30;

    let selectedOrder = null;
    const returnModal = new bootstrap.Modal(document.getElementById('returnConfirmationModal'));

    // Pagination state
    const state = {
        eligible: {
            page: 1,
            per_page: 12,
            loading: false,
            has_more: true
        },
        history: {
            page: 1,
            per_page: 12,
            loading: false,
            has_more: true
        }
    };

    // ── Notifications (mirror orders.js conventions) ──────────────────────
    function notify(type, message) {
        if (typeof window.appNotify === 'function') {
            window.appNotify(type, message);
            return;
        }
        if (window.Notiflix && window.Notiflix.Notify) {
            const fn = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'failure');
            window.Notiflix.Notify[fn](message);
            return;
        }
        if (window.Swal) {
            window.Swal.fire(type === 'success' ? 'Done' : 'Oops!', message, type === 'success' ? 'success' : 'error');
        }
    }

    function formatMoney(amount) {
        const symbol = (window.appCurrency && window.appCurrency.symbol) || '$';
        return symbol + parseFloat(amount || 0).toFixed(2);
    }

    function escape(text) {
        if (text === null || text === undefined) return '';
        return $('<div>').text(text).html();
    }

    // ── Tabs ──────────────────────────────────────────────────────────────
    function activeTab() {
        return $('.employee-orders-tab.active').data('returns-tab') || 'eligible';
    }

    $(document).on('click', '[data-returns-tab]', function () {
        const tab = $(this).data('returns-tab');
        $('.employee-orders-tab').removeClass('active');
        $(this).addClass('active');
        $('[data-returns-view]').addClass('d-none');
        $('[data-returns-view="' + tab + '"]').removeClass('d-none');
        $('[data-returns-search]').val('');

        // Reset pagination when switching tabs
        if (tab === 'eligible') {
            state.eligible.page = 1;
            state.eligible.has_more = true;
            loadReturns();
        } else {
            state.history.page = 1;
            state.history.has_more = true;
            loadHistory();
        }
    });

    // ── Eligible orders ───────────────────────────────────────────────────
    function loadReturns(search = '', append = false) {
        const $list = $('[data-return-list]');
        const $loading = $('[data-return-loading]');
        const $empty = $('[data-return-empty]');

        if (state.eligible.loading) {
            return;
        }

        state.eligible.loading = true;
        $loading.removeClass('d-none');

        if (!append) {
            $list.addClass('d-none');
            $empty.addClass('d-none');
        }

        $.ajax({
            url: listingUrl,
            method: 'GET',
            data: {
                q: search,
                page: state.eligible.page,
                per_page: state.eligible.per_page
            },
            success: function (response) {
                $loading.addClass('d-none');
                const orders = response.orders || [];
                const pagination = response.pagination || {};

                if (append) {
                    $('[data-returns-count="eligible"]').text(pagination.total || 0);
                } else {
                    $('[data-returns-count="eligible"]').text(orders.length);
                }

                if (response.pagination) {
                    state.eligible.has_more = response.pagination.has_more;
                    state.eligible.page = response.pagination.current_page;
                }

                if (orders.length > 0) {
                    $list.removeClass('d-none');
                    renderReturns(orders, append);
                } else if (!append) {
                    $empty.removeClass('d-none');
                }
            },
            error: function () {
                $loading.addClass('d-none');
                if (!append) {
                    $empty.removeClass('d-none');
                }
                notify('error', 'Failed to load eligible orders.');
            },
            complete: function () {
                state.eligible.loading = false;
            }
        });
    }

    // ── Return history ────────────────────────────────────────────────────
    function loadHistory(search = '', append = false) {
        const $list = $('[data-history-list]');
        const $loading = $('[data-history-loading]');
        const $empty = $('[data-history-empty]');

        if (state.history.loading) {
            return;
        }

        state.history.loading = true;
        $loading.removeClass('d-none');

        if (!append) {
            $list.addClass('d-none');
            $empty.addClass('d-none');
        }

        $.ajax({
            url: historyUrl,
            method: 'GET',
            data: {
                q: search,
                page: state.history.page,
                per_page: state.history.per_page
            },
            success: function (response) {
                $loading.addClass('d-none');
                const orders = response.orders || [];
                const pagination = response.pagination || {};

                if (append) {
                    $('[data-returns-count="history"]').text(pagination.total || 0);
                } else {
                    $('[data-returns-count="history"]').text(orders.length);
                }

                if (response.pagination) {
                    state.history.has_more = response.pagination.has_more;
                    state.history.page = response.pagination.current_page;
                }

                if (orders.length > 0) {
                    $list.removeClass('d-none');
                    renderHistory(orders, append);
                } else if (!append) {
                    $empty.removeClass('d-none');
                }
            },
            error: function () {
                $loading.addClass('d-none');
                if (!append) {
                    $empty.removeClass('d-none');
                }
                notify('error', 'Failed to load returned orders.');
            },
            complete: function () {
                state.history.loading = false;
            }
        });
    }

    function metaRow(icon, label, value, valueClass) {
        if (!value && value !== 0) return '';
        return '' +
            '<div class="employee-return-meta-row">' +
            '  <span class="employee-return-meta-label"><i class="ti ' + icon + '"></i>' + escape(label) + '</span>' +
            '  <span class="employee-return-meta-value ' + (valueClass || '') + '">' + value + '</span>' +
            '</div>';
    }

    // ── Render eligible cards ─────────────────────────────────────────────
    function renderReturns(orders, append = false) {
        const $list = $('[data-return-list]');
        if (!append) {
            $list.empty();
        }

        orders.forEach(function (order) {
            const eligible = !!order.is_eligible;
            const badge = eligible
                ? '<span class="employee-order-status success">Eligible</span>'
                : '<span class="employee-order-status danger">Expired</span>';

            const vehicleLine = order.vehicle_label
                ? metaRow('tabler-car', 'Vehicle', escape(order.vehicle_label) + (order.plate_number ? ' · ' + escape(order.plate_number) : ''))
                : (order.plate_number ? metaRow('tabler-car', 'Plate', escape(order.plate_number)) : '');

            const card = '' +
                '<article class="employee-order-card employee-return-card ' + (eligible ? 'is-eligible' : 'is-ineligible') + '">' +
                '  <div class="employee-order-card-top">' +
                '    <div class="employee-order-number">Order # ' + escape(order.order_number) + '</div>' +
                '    ' + badge +
                '  </div>' +
                '  <div class="employee-order-card-middle">' +
                '    <div class="employee-order-customer"><i class="ti tabler-user"></i><span>' + escape(order.customer_name) + '</span></div>' +
                '  </div>' +
                '  <div class="employee-return-meta">' +
                vehicleLine +
                metaRow('tabler-cash', 'Total', order.total_amount_label) +
                metaRow('tabler-calendar', 'Paid', escape(order.paid_at_label)) +
                metaRow('tabler-clock', 'Time Since', escape(order.time_since_payment_label || order.days_since_payment + ' days')) +
                '  </div>' +
                '  <div class="employee-return-card-footer">' +
                '    <div class="employee-return-refundable">' +
                '      <span class="employee-return-refundable-label">Refundable</span>' +
                '      <span class="employee-return-refundable-value">' + order.refundable_amount_label + '</span>' +
                '    </div>' +
                '    <button type="button" class="btn btn-danger employee-return-action return-order-btn" ' + (eligible ? '' : 'disabled') +
                '      data-order-id="' + escape(order.id) + '"' +
                '      data-order-number="' + escape(order.order_number) + '"' +
                '      data-customer-name="' + escape(order.customer_name) + '"' +
                '      data-total-amount="' + escape(order.total_amount_label) + '"' +
                '      data-refundable-amount="' + escape(order.refundable_amount_label) + '"' +
                '      data-time-since-payment="' + escape(order.time_since_payment_label || order.days_since_payment + ' days') + '"' +
                "      data-items='" + JSON.stringify(order.items || []).replace(/'/g, '&#39;') + "'" +
                '      data-service-fee-amount="' + (order.service_fee_amount || 0) + '"' +
                '      data-discount-amount="' + (order.discount_amount || 0) + '"' +
                '      data-total-items="' + (order.total_items || 0) + '">' +
                '      <i class="ti tabler-rotate-2"></i><span>Return</span>' +
                '    </button>' +
                '  </div>' +
                '</article>';

            $list.append(card);
        });
    }

    // ── Render history cards ──────────────────────────────────────────────
    function renderHistory(orders, append = false) {
        const $list = $('[data-history-list]');
        if (!append) {
            $list.empty();
        }

        orders.forEach(function (order) {
            const vehicleLine = order.vehicle_label
                ? metaRow('tabler-car', 'Vehicle', escape(order.vehicle_label) + (order.plate_number ? ' · ' + escape(order.plate_number) : ''))
                : (order.plate_number ? metaRow('tabler-car', 'Plate', escape(order.plate_number)) : '');

            const card = '' +
                '<article class="employee-order-card employee-return-card is-returned">' +
                '  <div class="employee-order-card-top">' +
                '    <div class="employee-order-number">Order # ' + escape(order.order_number) + '</div>' +
                '    <span class="employee-order-status danger">Returned</span>' +
                '  </div>' +
                '  <div class="employee-order-card-middle">' +
                '    <div class="employee-order-customer"><i class="ti tabler-user"></i><span>' + escape(order.customer_name) + '</span></div>' +
                '  </div>' +
                '  <div class="employee-return-meta">' +
                vehicleLine +
                metaRow('tabler-cash', 'Total', order.total_amount_label) +
                metaRow('tabler-wallet', 'Method', escape(order.refund_method_label)) +
                metaRow('tabler-calendar', 'Refunded', escape(order.refunded_at_label)) +
                metaRow('tabler-box', 'Items', order.items_count) +
                '  </div>' +
                '  <div class="employee-return-card-footer">' +
                '    <div class="employee-return-refundable refunded">' +
                '      <span class="employee-return-refundable-label">Refunded</span>' +
                '      <span class="employee-return-refundable-value">' + order.refund_amount_label + '</span>' +
                '    </div>' +
                '  </div>' +
                '</article>';

            $list.append(card);
        });
    }

    // ── Load more eligible orders ───────────────────────────────────────────
    function loadMoreReturns() {
        if (!state.eligible.has_more || state.eligible.loading) {
            return;
        }
        state.eligible.page++;
        loadReturns($('[data-returns-search]').val().trim(), true);
    }

    // ── Load more history ─────────────────────────────────────────────────
    function loadMoreHistory() {
        if (!state.history.has_more || state.history.loading) {
            return;
        }
        state.history.page++;
        loadHistory($('[data-returns-search]').val().trim(), true);
    }

    // ── Open modal ────────────────────────────────────────────────────────
    $(document).on('click', '.return-order-btn', function () {
        const $btn = $(this);
        selectedOrder = {
            id: $btn.data('order-id'),
            order_number: $btn.data('order-number'),
            customer_name: $btn.data('customer-name'),
            total_amount: $btn.data('total-amount'),
            refundable_amount: $btn.data('refundable-amount'),
            time_since_payment: $btn.data('time-since-payment'),
            items: $btn.data('items') || [],
            service_fee_amount: parseFloat($btn.data('service-fee-amount') || 0),
            discount_amount: parseFloat($btn.data('discount-amount') || 0),
            total_items: parseInt($btn.data('total-items') || 0)
        };

        $('#returnOrderNumber').text(selectedOrder.order_number);
        $('#returnCustomerName').text(selectedOrder.customer_name);
        $('#returnTotalAmount').text(selectedOrder.total_amount);
        $('#returnDaysSincePayment').text(selectedOrder.time_since_payment);

        renderReturnItems(selectedOrder.items);
        $('#returnForm')[0].reset();
        calculateRefundAmount();

        returnModal.show();
    });

    // ── Render modal item rows with quantity steppers ─────────────────────
    function renderReturnItems(items) {
        const $list = $('#returnItemsList');
        $list.empty();

        if (!items || items.length === 0) {
            $list.html('<p class="employee-return-empty-items">No items in this order.</p>');
            return;
        }

        items.forEach(function (item) {
            const availableQty = (item.available_quantity !== undefined && item.available_quantity !== null)
                ? item.available_quantity
                : item.quantity;
            const returnedQty = item.returned_quantity || 0;
            const exhausted = availableQty <= 0;

            const row = '' +
                '<div class="employee-return-item ' + (exhausted ? 'is-exhausted' : '') + '" data-item-id="' + item.id + '" data-unit-price="' + item.unit_price + '">' +
                '  <div class="employee-return-item-info">' +
                '    <div class="employee-return-item-name">' + escape(item.product_name) + '</div>' +
                '    <div class="employee-return-item-meta">' +
                '      <span>' + escape(item.sku || 'N/A') + '</span>' +
                '      <span>' + item.unit_price_label + ' each</span>' +
                '      <span>Qty ' + item.quantity + (returnedQty ? ' · ' + returnedQty + ' returned' : '') + '</span>' +
                '      <span class="employee-return-item-avail">' + availableQty + ' available</span>' +
                '    </div>' +
                '  </div>' +
                '  <div class="employee-return-stepper" ' + (exhausted ? 'data-disabled="1"' : '') + '>' +
                '    <button type="button" class="employee-return-step" data-step="-1" ' + (exhausted ? 'disabled' : '') + '><i class="ti tabler-minus"></i></button>' +
                '    <input type="number" class="employee-return-qty" min="0" max="' + availableQty + '" value="0" data-max-qty="' + availableQty + '" data-item-id="' + item.id + '" ' + (exhausted ? 'disabled' : '') + '>' +
                '    <button type="button" class="employee-return-step" data-step="1" ' + (exhausted ? 'disabled' : '') + '><i class="ti tabler-plus"></i></button>' +
                '  </div>' +
                '</div>';
            $list.append(row);
        });

        $('.employee-return-qty').on('input change', calculateRefundAmount);
    }

    // Stepper buttons
    $(document).on('click', '.employee-return-step', function () {
        const $stepper = $(this).closest('.employee-return-stepper');
        const $input = $stepper.find('.employee-return-qty');
        const step = parseInt($(this).data('step'), 10) || 0;
        const max = parseInt($input.data('max-qty'), 10) || 0;
        let val = (parseInt($input.val(), 10) || 0) + step;
        val = Math.min(Math.max(val, 0), max);
        $input.val(val).trigger('change');
    });

    // ── Refund calculation ────────────────────────────────────────────────
    function calculateRefundAmount() {
        let totalRefund = 0;
        let totalReturnedItems = 0;

        $('.employee-return-qty').each(function () {
            const $input = $(this);
            let qty = parseInt($input.val(), 10) || 0;
            const maxQty = parseInt($input.data('max-qty'), 10) || 0;

            if (qty < 0) { qty = 0; $input.val(0); }
            else if (qty > maxQty) { qty = maxQty; $input.val(maxQty); }

            const unitPrice = parseFloat($input.closest('.employee-return-item').data('unit-price')) || 0;
            $input.closest('.employee-return-item').toggleClass('is-selected', qty > 0);

            totalRefund += qty * unitPrice;
            totalReturnedItems += qty;
        });

        const totalOrderItems = selectedOrder ? (selectedOrder.total_items || 0) : 0;
        let proportionalDiscount = 0;
        if (totalOrderItems > 0 && totalReturnedItems > 0 && selectedOrder.discount_amount > 0) {
            proportionalDiscount = Math.round(selectedOrder.discount_amount * (totalReturnedItems / totalOrderItems) * 100) / 100;
        }

        totalRefund = Math.round((totalRefund - proportionalDiscount) * 100) / 100;
        if (totalRefund < 0) totalRefund = 0;

        $('#calculatedRefundAmount').text(formatMoney(totalRefund));
        return totalRefund;
    }

    // ── Confirm return ────────────────────────────────────────────────────
    $('#confirmReturnBtn').on('click', function () {
        const $btn = $(this);
        const $btnText = $btn.find('.btn-text');

        const returnReason = $('#returnReason').val().trim();
        const refundMethod = $('#refundMethod').val();

        if (!returnReason) {
            notify('warning', 'Please provide a return reason.');
            $('#returnReason').trigger('focus');
            return;
        }
        if (!refundMethod) {
            notify('warning', 'Please select a refund method.');
            $('#refundMethod').trigger('focus');
            return;
        }

        const returnItems = [];
        let totalReturnQty = 0;
        $('.employee-return-qty').each(function () {
            const $input = $(this);
            const qty = parseInt($input.val(), 10) || 0;
            const maxQty = parseInt($input.data('max-qty'), 10) || 0;
            if (qty > 0 && qty <= maxQty) {
                returnItems.push({ order_item_id: $input.data('item-id'), quantity: qty });
                totalReturnQty += qty;
            }
        });

        if (returnItems.length === 0 || totalReturnQty === 0) {
            notify('warning', 'Please select at least one item to return.');
            return;
        }

        const calculatedRefund = calculateRefundAmount();

        $btn.prop('disabled', true);
        if (window.AppLoader && typeof window.AppLoader.button === 'function') {
            $btnText.html(window.AppLoader.button('Processing...'));
        } else {
            $btnText.text('Processing...');
        }

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
            success: function (response) {
                returnModal.hide();
                notify('success', response.message || 'Order returned successfully.');
                loadReturns();
                loadHistory();
            },
            error: function (xhr) {
                const message = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to process return. Please try again.';
                notify('error', message);
            },
            complete: function () {
                $btn.prop('disabled', false);
                $btnText.text('Process Return');
            }
        });
    });

    // ── Search (targets the active tab) ───────────────────────────────────
    let searchTimeout;
    $(document).on('input', '[data-returns-search]', function () {
        clearTimeout(searchTimeout);
        const search = $(this).val().trim();
        searchTimeout = setTimeout(function () {
            // Reset pagination when searching
            if (activeTab() === 'eligible') {
                state.eligible.page = 1;
                state.eligible.has_more = true;
                loadReturns(search);
            } else {
                state.history.page = 1;
                state.history.has_more = true;
                loadHistory(search);
            }
        }, 300);
    });

    $(document).on('click', '[data-return-refresh]', function () {
        state.eligible.page = 1;
        state.eligible.has_more = true;
        loadReturns($('[data-returns-search]').val().trim());
    });
    $(document).on('click', '[data-history-refresh]', function () {
        state.history.page = 1;
        state.history.has_more = true;
        loadHistory($('[data-returns-search]').val().trim());
    });



    // ── Initial load ──────────────────────────────────────────────────────
    loadReturns();
    loadHistory(); // pre-fill history count

    // ── Infinite scroll ────────────────────────────────────────────────────
    $(window).on('scroll', function () {
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();

        if (activeTab() === 'eligible') {
            const $list = $('[data-return-list]');
            if (!$list.length || $list.hasClass('d-none')) return;

            const listHeight = $list.height();
            const listOffset = $list.offset().top;

            // Load more when user scrolls near the bottom of the list
            if (scrollTop + windowHeight >= listOffset + listHeight - 200) {
                loadMoreReturns();
            }
        } else {
            const $list = $('[data-history-list]');
            if (!$list.length || $list.hasClass('d-none')) return;

            const listHeight = $list.height();
            const listOffset = $list.offset().top;

            // Load more when user scrolls near the bottom of the list
            if (scrollTop + windowHeight >= listOffset + listHeight - 200) {
                loadMoreHistory();
            }
        }
    });
});
