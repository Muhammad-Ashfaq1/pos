/**
 * Employee — New Order page orchestrator.
 *
 * Renders unified catalog cards (categories / sub-cats / products / search results)
 * using the shared <template id="catalog-card-template"> so every level looks identical.
 * Talks to the backend exclusively through window.Catalog (catalog-api.js).
 *
 * Draft carts live client-side until checkout; checkout posts the active draft
 * order to the backend and then resets the saved draft.
 */

(function ($) {
    'use strict';

    if (typeof $ === 'undefined') return;
    if (window.__employeeNewOrderInitialized) return;
    window.__employeeNewOrderInitialized = true;

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    });

    // ─── Navigation state ──────────────────────────────────────────────
    // Stack of { level: 'categories'|'subCategories'|'products', meta: {...} }
    const navStack = [];
    let searchTimer = null;
    const orders = []; // [{ id, label, items, customer, vehicle }]
    let activeOrderId = null;
    let nextOrderNumber = 1;
    let isRestoringOrderMeta = false;
    let isSavingOrder = false;
    let paymentAmountInput = '';
    let paymentMethod = '';

    // ─── DOM refs ──────────────────────────────────────────────────────
    const $title = $('.catalog-title');
    const $backBtn = $('.catalog-back-btn');
    const $searchInput = $('.catalog-search');
    const $searchClear = $('.catalog-search-clear');
    const $catalogView = $('.catalog-view');
    const $grid = $('.catalog-grid');
    const $searchView = $('.catalog-search-view');
    const $productView = $('.product-details-view');

    // Dynamic draft orders

    function getActiveOrder() {
        return orders.find(function (order) { return order.id === activeOrderId; }) || null;
    }

    function currentCart() {
        const order = getActiveOrder();
        return order ? order.items : [];
    }

    function ensureActiveOrder() {
        return getActiveOrder() || createOrder(true);
    }

    function readSelectSelection($select) {
        const value = $select.val();
        if (!value) return null;

        let text = $select.find('option:selected').text();
        if (!text && $select.data('select2')) {
            const selected = $select.select2('data') || [];
            text = selected[0] ? selected[0].text : '';
        }

        return { id: value, text: text || value };
    }

    function setSelectSelection($select, selection) {
        if (!$select.length) return;

        if (!selection || !selection.id) {
            $select.val(null);
            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
            return;
        }

        const id = String(selection.id);
        const $existing = $select.find('option').filter(function () {
            return String(this.value) === id;
        }).first();

        if ($existing.length) {
            $existing.text(selection.text || id);
        } else {
            $select.append(new Option(selection.text || id, selection.id, false, false));
        }

        $select.val(selection.id);
        if ($select.data('select2')) {
            $select.trigger('change.select2');
        }
    }

    function saveActiveOrderMeta() {
        const order = getActiveOrder();
        if (!order) return;

        order.customer = readSelectSelection($('#customer_type_filter'));
        order.vehicle = readSelectSelection($('#add_vehicle_filter'));
    }

    function restoreActiveOrderMeta() {
        const order = getActiveOrder();
        isRestoringOrderMeta = true;
        setSelectSelection($('#customer_type_filter'), order ? order.customer : null);
        setSelectSelection($('#add_vehicle_filter'), order ? order.vehicle : null);
        isRestoringOrderMeta = false;
    }

    function formatOrderText(order) {
        const qty = order.items.reduce(function (sum, item) { return sum + item.qty; }, 0);
        const total = order.items.reduce(function (sum, item) { return sum + (item.qty * item.price); }, 0);

        if (qty === 0) {
            return order.label;
        }

        return order.label + ' - ' + qty + ' item' + (qty === 1 ? '' : 's') + ' - $' + total.toFixed(2);
    }

    function refreshOrderDropdown() {
        const $select = $('#order_type_filter');
        if (!$select.length) return;

        $select.empty().append(new Option('', '', false, false));
        orders.forEach(function (order) {
            $select.append(new Option(formatOrderText(order), order.id, false, order.id === activeOrderId));
        });
        $select.val(activeOrderId || '');

        if ($select.data('select2')) {
            $select.trigger('change.select2');
        }
    }

    function selectOrder(orderId) {
        const exists = orders.some(function (order) { return order.id === orderId; });
        if (!exists) return;

        if (activeOrderId !== orderId) {
            saveActiveOrderMeta();
        }

        activeOrderId = orderId;
        refreshOrderDropdown();
        restoreActiveOrderMeta();
        renderCart();
    }

    function createOrder(carryCurrentSelection) {
        const order = {
            id: 'draft-' + nextOrderNumber + '-' + Date.now(),
            label: 'Order ' + nextOrderNumber,
            items: [],
            customer: carryCurrentSelection ? readSelectSelection($('#customer_type_filter')) : null,
            vehicle: carryCurrentSelection ? readSelectSelection($('#add_vehicle_filter')) : null
        };

        nextOrderNumber += 1;
        orders.push(order);
        selectOrder(order.id);
        return order;
    }

    // ─── Card rendering (uses shared <template> partial) ───────────────

    function buildCard(item) {
        const tpl = document.getElementById('catalog-card-template');
        if (!tpl) return '';

        const node = tpl.content.cloneNode(true);
        const $col = $(node).find('.col-md-4');
        const $card = $col.find('.catalog-card');
        const $title = $col.find('.catalog-card-title');

        $card.attr('data-type', item.type);
        $card.attr('data-id', item.id);
        $card.attr('data-name', item.name);
        $title.text(item.name);

        // Product cards keep price/sku/barcode as data-* (read on click), but the
        // VISUAL stays identical to category cards — same size, same content.
        if (item.type === 'product') {
            $card.attr('data-price', Number(item.sale_price || 0).toFixed(2));
            $card.attr('data-sku', item.sku || '');
            $card.attr('data-barcode', item.barcode || '');
        }

        return $col[0].outerHTML;
    }

    function renderCards($container, items, emptyMessage) {
        if (!items || items.length === 0) {
            $container.html(
                '<div class="col-12 text-center py-5">'
                + '<i class="icon-base ti tabler-package-off" style="font-size:3rem;color:#ccc;"></i>'
                + '<p class="text-muted mt-2 mb-0">' + emptyMessage + '</p>'
                + '</div>'
            );
            return;
        }
        $container.html(items.map(buildCard).join(''));
    }

    function showLoading($container) {
        $container.html(
            '<div class="col-12 text-center py-5">'
            + '<div class="spinner-border text-primary" role="status"></div>'
            + '<p class="text-muted mt-2 mb-0">Loading...</p>'
            + '</div>'
        );
    }

    // ─── View switching ────────────────────────────────────────────────

    function showCatalog() {
        $catalogView.removeClass('d-none');
        $searchView.addClass('d-none');
        $productView.addClass('d-none');
    }

    function showSearchResults() {
        $catalogView.addClass('d-none');
        $searchView.removeClass('d-none');
        $productView.addClass('d-none');
    }

    function showProductDetail() {
        $catalogView.addClass('d-none');
        $searchView.addClass('d-none');
        $productView.removeClass('d-none');
    }

    function updateHeader() {
        const top = navStack[navStack.length - 1];
        if (!top || top.level === 'categories') {
            $title.text('Categories');
            $backBtn.addClass('d-none');
        } else {
            $title.text(top.meta.name || (top.level === 'subCategories' ? 'Sub Categories' : 'Products'));
            $backBtn.removeClass('d-none');
        }
    }

    // ─── Loaders for each level ────────────────────────────────────────

    function loadCategories(q) {
        showLoading($grid);
        Catalog.getAllCategories(q).done(function (res) {
            renderCards($grid, res.data || [], 'No categories found.');
        }).fail(function () {
            renderCards($grid, [], 'Failed to load categories.');
        });
    }

    function loadSubCategories(categoryId, q) {
        showLoading($grid);
        Catalog.getSubCategories({ categoryId: categoryId, q: q }).done(function (res) {
            renderCards($grid, res.data || [], 'No sub categories found.');
        }).fail(function () {
            renderCards($grid, [], 'Failed to load sub categories.');
        });
    }

    function loadProducts(subCategoryId, q) {
        showLoading($grid);
        Catalog.getProducts({ subCategoryId: subCategoryId, q: q }).done(function (res) {
            renderCards($grid, res.data || [], 'No products found.');
        }).fail(function () {
            renderCards($grid, [], 'Failed to load products.');
        });
    }

    function loadCurrentLevel(q) {
        const top = navStack[navStack.length - 1];
        if (!top || top.level === 'categories') {
            loadCategories(q);
        } else if (top.level === 'subCategories') {
            loadSubCategories(top.meta.id, q);
        } else if (top.level === 'products') {
            loadProducts(top.meta.id, q);
        }
    }

    // ─── Card click → drill down ───────────────────────────────────────

    $(document).on('click', '.catalog-card', function () {
        const $card = $(this);
        const type = $card.data('type');
        const id = $card.data('id');
        const name = $card.data('name');

        if (type === 'category') {
            navStack.push({ level: 'subCategories', meta: { id: id, name: name } });
            updateHeader();
            showCatalog();
            loadSubCategories(id, '');
        } else if (type === 'sub_category') {
            navStack.push({ level: 'products', meta: { id: id, name: name } });
            updateHeader();
            showCatalog();
            loadProducts(id, '');
        } else if (type === 'product') {
            openProductDetail({
                id: id,
                name: name,
                price: parseFloat($card.data('price')) || 0,
                sku: $card.data('sku') || '',
                barcode: $card.data('barcode') || '',
            });
        }
    });

    // ─── Back button (pops nav stack) ──────────────────────────────────

    $(document).on('click', '.catalog-back-btn', function () {
        if (navStack.length === 0) return;
        navStack.pop();
        updateHeader();
        showCatalog();
        loadCurrentLevel($searchInput.val() || '');
    });

    // ─── Product detail flow ───────────────────────────────────────────

    let activeProduct = null;

    function openProductDetail(product) {
        activeProduct = product;
        $('.product-detail-title').text(product.name);
        $('.product-name').text(product.name);
        $('.product-sku').text(product.sku || '—');
        $('.product-barcode').text(product.barcode || '—');
        $('.product-price').text('$' + product.price.toFixed(2));
        $('.product-qty-input').val(1);
        showProductDetail();
    }

    $(document).on('click', '.btn-back-from-product', function () {
        activeProduct = null;
        showCatalog();
    });

    $(document).on('click', '.btn-clear-qty', function () {
        $('.product-qty-input').val(1);
    });

    $(document).on('click', '.btn-add-to-cart', function () {
        if (!activeProduct) return;
        const qty = Math.max(1, parseInt($('.product-qty-input').val(), 10) || 1);
        addToCart(activeProduct, qty);
        activeProduct = null;
        showCatalog();
    });

    // ─── Cart (front-end only) ─────────────────────────────────────────

    function addToCart(product, qty) {
        const cart = ensureActiveOrder().items;
        const existing = cart.find(function (i) { return i.id === product.id; });
        if (existing) {
            existing.qty += qty;
        } else {
            cart.push({ id: product.id, name: product.name, price: product.price, qty: qty });
        }
        renderCart();
    }

    function removeFromCart(productId) {
        const cart = currentCart();
        const idx = cart.findIndex(function (i) { return i.id === productId; });
        if (idx >= 0) cart.splice(idx, 1);
        renderCart();
    }

    function changeQty(productId, delta) {
        const cart = currentCart();
        const item = cart.find(function (i) { return i.id === productId; });
        if (!item) return;
        item.qty = Math.max(1, item.qty + delta);
        renderCart();
    }

    function renderCart() {
        const cart = currentCart();
        const $tbody = $('#cart-items-tbody');
        $tbody.empty();

        if (cart.length === 0) {
            $tbody.html(
                '<tr class="empty-cart-message">'
                + '<td colspan="2" class="text-center py-5">'
                + '<p class="text-muted fw-bold mb-0">No Items Added</p>'
                + '</td></tr>'
            );
        } else {
            cart.forEach(function (item) {
                const lineTotal = (item.price * item.qty).toFixed(2);
                $tbody.append(
                    '<tr data-product-id="' + item.id + '">'
                    + '<td class="p-0 text-center" style="width:40px;">'
                    + '<button type="button" class="btn btn-link text-danger p-0 border-0 btn-remove-cart-item">'
                    + '<i class="icon-base ti tabler-trash"></i></button></td>'
                    + '<td class="p-0">'
                    + '<div class="bg-label-primary bg-opacity-10 rounded-pill py-2 px-3 shadow-sm row g-0 align-items-center mb-2">'
                    + '<div class="col-5"><span class="fw-bold text-secondary small">' + escape(item.name) + '</span></div>'
                    + '<div class="col-4 text-center d-flex align-items-center justify-content-center">'
                    + '<button type="button" class="btn btn-sm p-0 border-0 text-secondary fw-light cart-qty-minus">—</button>'
                    + '<span class="mx-2 fw-bold text-dark small qty-value">' + item.qty + '</span>'
                    + '<button type="button" class="btn btn-sm p-0 border-0 text-secondary fw-light cart-qty-plus">+</button>'
                    + '</div>'
                    + '<div class="col-3 text-end"><span class="fw-bold text-primary small item-price">$' + lineTotal + '</span></div>'
                    + '</div></td></tr>'
                );
            });
        }

        updateSummary();
    }

    function updateSummary() {
        const cart = currentCart();
        const totalQty = cart.reduce(function (s, i) { return s + i.qty; }, 0);
        const totalAmount = cart.reduce(function (s, i) { return s + i.qty * i.price; }, 0);

        $('.summary-qty').text(totalQty);
        $('.summary-subtotal').text('$' + totalAmount.toFixed(2));
        $('.summary-total').text('$' + totalAmount.toFixed(2));
        $('.btn-pay .text-warning:first').text('$' + totalAmount.toFixed(2));
        $('.btn-pay').prop('disabled', totalAmount <= 0 || isSavingOrder);
        $('.btn-pay .small').text(isSavingOrder ? 'Saving...' : 'Pay');
        refreshOrderDropdown();
    }

    function cartTotals() {
        const cart = currentCart();
        return {
            quantity: cart.reduce(function (sum, item) { return sum + item.qty; }, 0),
            subtotal: cart.reduce(function (sum, item) { return sum + (item.qty * item.price); }, 0),
        };
    }

    function formatMoney(amount) {
        return '$' + (Number(amount) || 0).toFixed(2);
    }

    function paymentAmountValue() {
        if (paymentAmountInput === '') return 0;

        return Math.round((parseFloat(paymentAmountInput) || 0) * 100) / 100;
    }

    function paymentMethodText() {
        const labels = {
            cash: 'Cash',
            card: 'Credit/Debit Card',
            check: 'Check'
        };

        return labels[paymentMethod] || 'Select Method';
    }

    function paymentOrderNumber() {
        const order = getActiveOrder();
        if (!order) return 'Draft';

        return order.label.replace(/\s+/g, '-').toUpperCase();
    }

    function renderPaymentItems() {
        const cart = currentCart();
        const $list = $('.payment-items-list');

        if (!$list.length) return;

        if (cart.length === 0) {
            $list.html('<div class="text-muted text-center py-5">No Items Added</div>');
            return;
        }

        $list.html(cart.map(function (item) {
            const lineTotal = item.qty * item.price;

            return ''
                + '<div class="payment-item-row">'
                + '<div>'
                + '<div class="payment-item-name">' + escape(item.name) + '</div>'
                + '<div class="payment-item-meta">' + item.qty + ' x ' + formatMoney(item.price) + '</div>'
                + '</div>'
                + '<div class="fw-bold text-primary">' + formatMoney(lineTotal) + '</div>'
                + '</div>';
        }).join(''));
    }

    function renderPaymentScreen() {
        const totals = cartTotals();
        const amount = paymentAmountValue();
        const remaining = Math.max(totals.subtotal - amount, 0);
        const changeDue = Math.max(amount - totals.subtotal, 0);
        const canCheckout = totals.subtotal > 0 && !isSavingOrder;

        $('.payment-order-number').text(paymentOrderNumber());
        $('.payment-total').text(formatMoney(totals.subtotal));
        $('.payment-subtotal').text(formatMoney(totals.subtotal));
        $('.payment-balance-due').text(formatMoney(remaining));
        $('.payment-remaining').text(formatMoney(remaining));
        $('.payment-change-due').text(formatMoney(changeDue));
        $('.payment-method-label').text(paymentMethodText());
        $('.payment-amount-display').text(paymentAmountInput === '' ? '$' : '$' + paymentAmountInput);
        $('.payment-method-btn').removeClass('active')
            .filter('[data-payment-method="' + paymentMethod + '"]')
            .addClass('active');
        $('.payment-methods').toggleClass('is-invalid', false);
        $('.btn-checkout-order')
            .prop('disabled', !canCheckout)
            .text(isSavingOrder ? 'Processing...' : 'Checkout');

        renderPaymentItems();
    }

    function openPaymentScreen() {
        paymentAmountInput = '';
        paymentMethod = '';
        renderPaymentScreen();
        $('.order-entry-screen').addClass('d-none');
        $('.order-payment-screen').removeClass('d-none');
    }

    function closePaymentScreen() {
        $('.order-payment-screen').addClass('d-none');
        $('.order-entry-screen').removeClass('d-none');
    }

    function setPaymentAmount(value) {
        const normalized = String(value || '').replace(/[^0-9.]/g, '');
        const parts = normalized.split('.');

        if (parts.length > 2) return;

        const dollars = parts[0].slice(0, 7);
        const cents = parts.length === 2 ? parts[1].slice(0, 2) : null;
        paymentAmountInput = cents === null ? dollars : dollars + '.' + cents;

        if (paymentAmountInput.length > 1 && paymentAmountInput[0] === '0' && paymentAmountInput[1] !== '.') {
            paymentAmountInput = paymentAmountInput.replace(/^0+/, '') || '0';
        }

        renderPaymentScreen();
    }

    function appendPaymentKey(key) {
        if (key === 'clear') {
            paymentAmountInput = '';
            renderPaymentScreen();
            return;
        }

        if (key === 'backspace') {
            paymentAmountInput = paymentAmountInput.slice(0, -1);
            renderPaymentScreen();
            return;
        }

        if (key === '.' && paymentAmountInput.includes('.')) return;
        if (key === '.' && paymentAmountInput === '') {
            setPaymentAmount('0.');
            return;
        }

        setPaymentAmount(paymentAmountInput + key);
    }

    function validatePaymentForCheckout() {
        const amount = paymentAmountValue();

        if (!paymentMethod) {
            $('.payment-methods').addClass('is-invalid');
            notifyOrder('error', 'Oops! Please select a payment method.');
            return false;
        }

        if (amount <= 0) {
            notifyOrder('error', 'Please enter the payment amount.');
            return false;
        }

        return true;
    }

    function escape(value) {
        return $('<div>').text(value ?? '').html();
    }

    function notifyOrder(type, message) {
        if (!message) return;

        if (typeof window.appNotify === 'function') {
            window.appNotify(type, message);
            return;
        }

        alert(message);
    }

    function markSelectInvalid($select, isInvalid) {
        $select.toggleClass('is-invalid', isInvalid);

        if ($select.data('select2')) {
            $select.next('.select2-container')
                .find('.select2-selection')
                .toggleClass('is-invalid', isInvalid);
        }
    }

    function clearOrderValidation() {
        markSelectInvalid($('#customer_type_filter'), false);
        markSelectInvalid($('#add_vehicle_filter'), false);
    }

    function validateOrderForSave() {
        const order = getActiveOrder();
        const $customerSelect = $('#customer_type_filter');
        const $vehicleSelect = $('#add_vehicle_filter');

        clearOrderValidation();

        if (!order || order.items.length === 0) {
            notifyOrder('error', 'Please add at least one item before saving the order.');
            return false;
        }

        if (!$customerSelect.val()) {
            markSelectInvalid($customerSelect, true);
            notifyOrder('error', 'Please select a customer before saving the order.');
            return false;
        }

        if (!$vehicleSelect.val()) {
            markSelectInvalid($vehicleSelect, true);
            notifyOrder('error', 'Please select a vehicle before saving the order.');
            return false;
        }

        return true;
    }

    function orderErrorMessage(xhr) {
        const response = xhr.responseJSON || {};
        const errors = response.errors || {};
        const firstField = Object.keys(errors)[0];

        if (firstField && errors[firstField] && errors[firstField][0]) {
            return errors[firstField][0];
        }

        return response.message || 'Unable to save order.';
    }

    function currentOrderPayload() {
        saveActiveOrderMeta();

        const order = getActiveOrder();
        if (!order || order.items.length === 0) return null;

        return {
            customer_id: order.customer ? order.customer.id : null,
            vehicle_id: order.vehicle ? order.vehicle.id : null,
            payment: {
                method: paymentMethod,
                amount: paymentAmountValue(),
            },
            items: order.items.map(function (item) {
                return {
                    product_id: item.id,
                    quantity: item.qty
                };
            })
        };
    }

    function resetSavedOrder() {
        const savedOrderId = activeOrderId;
        const savedOrderIndex = orders.findIndex(function (order) { return order.id === savedOrderId; });

        if (savedOrderIndex >= 0) {
            orders.splice(savedOrderIndex, 1);
        }

        activeOrderId = null;
        if (orders.length > 0) {
            selectOrder(orders[0].id);
        } else {
            nextOrderNumber = 1;
            refreshOrderDropdown();
            restoreActiveOrderMeta();
            renderCart();
        }
    }

    $(document).on('click', '.btn-remove-cart-item', function () {
        const productId = $(this).closest('tr').data('product-id');
        removeFromCart(productId);
    });

    $(document).on('click', '.cart-qty-plus', function () {
        const productId = $(this).closest('tr').data('product-id');
        changeQty(productId, 1);
    });

    $(document).on('click', '.cart-qty-minus', function () {
        const productId = $(this).closest('tr').data('product-id');
        changeQty(productId, -1);
    });

    $(document).on('click', '.btn-cancel-order', function () {
        const cart = currentCart();
        cart.length = 0;
        renderCart();
    });

    $(document).on('click', '.add-order-btn', function (event) {
        event.preventDefault();

        const activeOrder = getActiveOrder();
        if (activeOrder && activeOrder.items.length === 0 && !activeOrder.customer && !activeOrder.vehicle) {
            selectOrder(activeOrder.id);
            return;
        }

        createOrder();
    });

    $(document).on('change', '#order_type_filter', function () {
        const orderId = $(this).val();
        if (orderId && orderId !== activeOrderId) {
            selectOrder(orderId);
        }
    });

    $(document).on('click', '.btn-pay', function () {
        if (isSavingOrder || !validateOrderForSave()) return;

        openPaymentScreen();
    });

    $(document).on('click', '.payment-back-btn', function () {
        closePaymentScreen();
    });

    $(document).on('click', '.payment-key', function () {
        const quickAmount = $(this).data('payment-quick');
        const key = $(this).data('payment-key');

        if (quickAmount !== undefined) {
            setPaymentAmount(String(quickAmount));
            return;
        }

        appendPaymentKey(String(key));
    });

    $(document).on('click', '.payment-method-btn', function () {
        paymentMethod = $(this).data('payment-method') || '';
        $('.payment-methods').removeClass('is-invalid');

        if (paymentMethod && paymentMethod !== 'cash' && paymentAmountInput === '') {
            setPaymentAmount(cartTotals().subtotal.toFixed(2));
            return;
        }

        renderPaymentScreen();
    });

    $(document).on('click', '.btn-checkout-order', function () {
        const saveUrl = (window.catalogRoutes || {}).save;

        if (isSavingOrder || !validateOrderForSave() || !validatePaymentForCheckout()) return;

        if (!saveUrl) {
            notifyOrder('error', 'Order save route is missing.');
            return;
        }

        const payload = currentOrderPayload();
        if (!payload) return;

        isSavingOrder = true;
        updateSummary();
        renderPaymentScreen();

        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
        }).done(function (response) {
            notifyOrder('success', response.message || 'Order saved successfully.');
            resetSavedOrder();
            closePaymentScreen();
        }).fail(function (xhr) {
            notifyOrder('error', orderErrorMessage(xhr));
        }).always(function () {
            isSavingOrder = false;
            updateSummary();
            renderPaymentScreen();
        });
    });

    // ─── Search (unified across categories + sub-cats + products) ──────

    function runSearch(q) {
        showLoading($('.search-categories-grid'));
        $('.search-sub-categories-grid, .search-products-grid').empty();

        Catalog.search(q).done(function (res) {
            const cats = res.categories || [];
            const subs = res.sub_categories || [];
            const prods = res.products || [];

            $('.search-categories-count').text(cats.length);
            $('.search-sub-categories-count').text(subs.length);
            $('.search-products-count').text(prods.length);

            renderCards($('.search-categories-grid'), cats, 'No categories match.');
            renderCards($('.search-sub-categories-grid'), subs, 'No sub categories match.');
            renderCards($('.search-products-grid'), prods, 'No products match.');

            showSearchResults();
        }).fail(function () {
            renderCards($('.search-categories-grid'), [], 'Search failed.');
        });
    }

    $searchInput.on('input', function () {
        const term = $(this).val().trim();
        clearTimeout(searchTimer);

        if (term === '') {
            $searchClear.addClass('d-none');
            showCatalog();
            loadCurrentLevel('');
            return;
        }

        $searchClear.removeClass('d-none');
        searchTimer = setTimeout(function () { runSearch(term); }, 300);
    });

    $searchClear.on('click', function () {
        $searchInput.val('');
        $searchClear.addClass('d-none');
        showCatalog();
        loadCurrentLevel('');
    });

    // ─── Select2 init (matches tenant admin pattern) ───────────────────

    function parseMinResults(raw) {
        if (raw == null || raw === '') return 0;
        if (typeof raw === 'number') return raw;
        const s = String(raw).trim();
        if (s.toLowerCase() === 'infinity') return Infinity;
        const n = Number(s);
        return Number.isNaN(n) ? 0 : n;
    }

    function initSelect2() {
        const $selects = $('.select2');

        if (typeof $.fn.select2 !== 'function' || !$selects.length) {
            return;
        }

        $selects.each(function () {
            const $this = $(this);
            if ($this.data('select2')) return;

            const dropdownParentSelector = $this.data('dropdown-parent');
            if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
                $this.wrap('<div class="position-relative"></div>');
            }

            const ajaxUrl = $this.data('ajax-url');
            const options = {
                dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
                placeholder: $this.data('placeholder'),
                allowClear: Boolean($this.data('allow-clear')),
                minimumResultsForSearch: parseMinResults($this.data('minimum-results-for-search')),
            };

            if (ajaxUrl) {
                options.ajax = {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        const q = {
                            q: params.term,
                            page: params.page || 1
                        };

                        if ($this.attr('id') === 'add_vehicle_filter') {
                            q.customer_id = $('#customer_type_filter').val();
                        }

                        return q;
                    },
                    processResults: function (data) {
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    cache: true
                };
            }

            $this.select2(options);
        });

        // Dependency: customer and vehicle selections belong to the active draft order.
        $('#customer_type_filter').on('change', function () {
            markSelectInvalid($(this), false);

            if (isRestoringOrderMeta) return;

            const order = getActiveOrder();
            if (order) {
                order.customer = readSelectSelection($(this));
                order.vehicle = null;
            }

            const $vehicleSelect = $('#add_vehicle_filter');
            $vehicleSelect.val(null).trigger('change');
        });

        $('#add_vehicle_filter').on('change', function () {
            markSelectInvalid($(this), false);

            if (isRestoringOrderMeta) return;

            const order = getActiveOrder();
            if (order) {
                order.vehicle = readSelectSelection($(this));
            }
        });
    }

    // ─── Boot ──────────────────────────────────────────────────────────

    $(function () {
        navStack.push({ level: 'categories', meta: {} });
        updateHeader();
        showCatalog();
        loadCategories('');
        initSelect2();
        refreshOrderDropdown();
        renderCart();

        // Initialize centralized CustomerManager for "Add Customer" modal
        if (typeof window.CustomerManager === 'function') {
            new window.CustomerManager({
                onSaveSuccess: function (response) {
                    const $customerSelect = $('#customer_type_filter');
                    const customer = response.data || {};

                    if (customer.id && $customerSelect.length) {
                        const newOption = new Option(customer.name, customer.id, true, true);
                        $customerSelect.append(newOption).trigger('change');
                        $customerSelect.trigger({
                            type: 'select2:select',
                            params: { data: { id: customer.id, text: customer.name } }
                        });
                    }
                }
            });
        }

        // Initialize centralized VehicleManager for "Add Vehicle" modal
        if (typeof window.VehicleManager === 'function') {
            const vehicleManager = new window.VehicleManager({
                onSaveSuccess: function (response) {
                    const $customerSelect = $('#customer_type_filter');
                    const $vehicleSelect = $('#add_vehicle_filter');
                    const vehicle = response.data || {};

                    if (vehicle.customer_id && $customerSelect.length) {
                        const customerText = vehicle.customer_name || 'Walk-in Customer';
                        const customerOption = new Option(customerText, vehicle.customer_id, true, true);
                        $customerSelect.append(customerOption).trigger('change');
                        $customerSelect.trigger({
                            type: 'select2:select',
                            params: { data: { id: vehicle.customer_id, text: customerText } }
                        });
                    }

                    if (vehicle.id && $vehicleSelect.length) {
                        const text = [vehicle.make, vehicle.model, vehicle.year].filter(Boolean).join(' ') || vehicle.plate_number;
                        const newOption = new Option(text, vehicle.id, true, true);
                        $vehicleSelect.append(newOption).trigger('change');
                        $vehicleSelect.trigger({
                            type: 'select2:select',
                            params: { data: { id: vehicle.id, text: text } }
                        });
                    }
                }
            });

            // Pre-select customer in vehicle modal if already selected in main dropdown
            $(document).on('click', '.add-vehicle-btn', function () {
                const customerId = $('#customer_type_filter').val();
                const customerName = $('#customer_type_filter').find('option:selected').text();

                if (customerId && vehicleManager.$form.length) {
                    const $modalCustomerSelect = vehicleManager.$form.find('#vehicle_customer_id');
                    if ($modalCustomerSelect.length) {
                        const newOption = new Option(customerName, customerId, true, true);
                        $modalCustomerSelect.append(newOption).trigger('change');
                    }
                }
            });
        }
    });

})(window.jQuery);
