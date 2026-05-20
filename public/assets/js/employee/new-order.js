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

    function pickSelectionValue(primary, fallback, key) {
        if (primary && primary[key] !== undefined) return primary[key];
        if (fallback && fallback[key] !== undefined) return fallback[key];

        return undefined;
    }

    function readSelectSelections($select) {
        const value = $select.val();
        const values = Array.isArray(value)
            ? value.filter(function (item) { return item !== null && item !== undefined && item !== ''; })
            : (value ? [value] : []);

        if (!values.length) return [];

        const optionByValue = {};
        $select.find('option:selected').each(function () {
            optionByValue[String(this.value)] = {
                text: $(this).text(),
                data: $(this).data('selection') || {}
            };
        });

        const selectedItems = $select.data('select2') ? ($select.select2('data') || []) : [];

        return values.map(function (itemValue) {
            const id = String(itemValue);
            const option = optionByValue[id] || {};
            const selectedData = selectedItems.find(function (item) {
                return String(item.id) === id;
            }) || {};
            const optionData = option.data || {};
            const text = option.text || selectedData.text || id;
            const selection = { id: itemValue, text: text || itemValue };

            [
                'customer_type',
                'phone',
                'email',
                'discount_id',
                'discount',
                'discount_group_id',
                'discount_group',
                'name',
                'code',
                'category_name',
                'standard_price',
                'tax_percentage',
                'is_active'
            ].forEach(function (key) {
                const selectedValue = pickSelectionValue(optionData, selectedData, key);
                if (selectedValue !== undefined) {
                    selection[key] = selectedValue;
                }
            });

            return selection;
        });
    }

    function readSelectSelection($select) {
        return readSelectSelections($select)[0] || null;
    }

    function setSelectSelections($select, selections) {
        if (!$select.length) return;

        const isMultiple = $select.prop('multiple');
        const normalized = Array.isArray(selections)
            ? selections.filter(function (selection) { return selection && selection.id; })
            : [];

        if (!normalized.length) {
            $select.val(isMultiple ? [] : null);
            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
            return;
        }

        const ids = [];
        normalized.forEach(function (selection) {
            const id = String(selection.id);
            ids.push(id);

            const $existing = $select.find('option').filter(function () {
                return String(this.value) === id;
            }).first();

            if ($existing.length) {
                $existing.text(selection.text || id);
                $existing.data('selection', selection);
            } else {
                const option = new Option(selection.text || id, selection.id, false, false);
                $(option).data('selection', selection);
                $select.append(option);
            }
        });

        $select.val(isMultiple ? ids : ids[0]);
        if ($select.data('select2')) {
            $select.trigger('change.select2');
        }
    }

    function setSelectSelection($select, selection) {
        if (!$select.length) return;

        if (!selection || !selection.id) {
            setSelectSelections($select, []);
            return;
        }

        setSelectSelections($select, [selection]);
    }

    function readServiceFeeSelections() {
        return readSelectSelections($('#order_service_filter')).filter(function (service) {
            return service && service.id;
        });
    }

    function saveActiveOrderMeta() {
        const order = getActiveOrder();
        if (!order) return;

        order.customer = readSelectSelection($('#customer_type_filter'));
        order.vehicle = readSelectSelection($('#add_vehicle_filter'));
    }

    function customerSelectionFromData(customer) {
        if (!customer || !customer.id) return null;

        return {
            id: customer.id,
            text: customer.text || customer.name || customer.id,
            customer_type: customer.customer_type,
            phone: customer.phone,
            email: customer.email,
            discount_id: customer.discount_id || null,
            discount: customer.discount || null,
            discount_group_id: customer.discount_group_id || null,
            discount_group: customer.discount_group || null
        };
    }

    function restoreActiveOrderMeta() {
        const order = getActiveOrder();
        isRestoringOrderMeta = true;
        setSelectSelection($('#customer_type_filter'), order ? order.customer : null);
        setSelectSelection($('#add_vehicle_filter'), order ? order.vehicle : null);
        isRestoringOrderMeta = false;
    }

    function formatOrderText(order) {
        const totals = totalsForOrder(order);
        const qty = totals.quantity;
        const serviceCount = normalizedServiceFees(orderServiceFees(order)).length;
        const labels = [];

        if (qty === 0 && serviceCount === 0) {
            return order.label;
        }

        if (qty > 0) {
            labels.push(qty + ' item' + (qty === 1 ? '' : 's'));
        }

        if (serviceCount > 0) {
            labels.push(serviceCount + ' service' + (serviceCount === 1 ? '' : 's'));
        }

        return order.label + ' - ' + labels.join(', ') + ' - $' + totals.total.toFixed(2);
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
        fillServiceFeeForm();
        renderCart();
    }

    function createOrder(carryCurrentSelection) {
        const order = {
            id: 'draft-' + nextOrderNumber + '-' + Date.now(),
            label: 'Order ' + nextOrderNumber,
            items: [],
            customer: carryCurrentSelection ? readSelectSelection($('#customer_type_filter')) : null,
            vehicle: carryCurrentSelection ? readSelectSelection($('#add_vehicle_filter')) : null,
            serviceFees: []
        };

        nextOrderNumber += 1;
        orders.push(order);
        selectOrder(order.id);
        return order;
    }

    // ─── Card rendering (uses shared <template> partial) ───────────────

    function encodePayload(value) {
        if (!value) return '';

        try {
            return encodeURIComponent(JSON.stringify(value));
        } catch (error) {
            return '';
        }
    }

    function decodePayload(value) {
        if (!value) return null;

        try {
            return JSON.parse(decodeURIComponent(value));
        } catch (error) {
            return null;
        }
    }

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
            $card.attr('data-stock', item.current_stock || 0);
            $card.attr('data-image', item.image_url || '');
            $card.attr('data-discount', encodePayload(item.discount || null));
            $card.attr('data-tax-percentage', item.tax_percentage || 0);
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

    function showProductDetail() {
        $catalogView.addClass('d-none');
        $searchView.addClass('d-none');
        $('.catalog-header').addClass('d-none');
        $productView.removeClass('d-none');
    }

    function showCatalog() {
        $catalogView.removeClass('d-none');
        $searchView.addClass('d-none');
        $('.catalog-header').removeClass('d-none');
        $productView.addClass('d-none');
    }

    function showSearchResults() {
        $catalogView.addClass('d-none');
        $searchView.removeClass('d-none');
        $('.catalog-header').removeClass('d-none');
        $productView.addClass('d-none');
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
                current_stock: parseInt($card.data('stock')) || 0,
                image_url: $card.attr('data-image') || '',
                discount: decodePayload($card.attr('data-discount') || ''),
                tax_percentage: parseFloat($card.attr('data-tax-percentage')) || 0,
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
        $('.product-detail-title').text('Product Details');
        $('.product-name').text(product.name);
        $('.product-sku').text(product.sku || '—');
        $('.product-barcode').text(product.barcode || '—');
        $('.product-price').text('$' + product.price.toFixed(2));
        $('.product-discount-banner').addClass('d-none');
        $('.product-discount-label').text('');
        $('.product-qty-input').val(1);

        // Handle Image
        const $img = $('.product-image');
        const $icon = $('.product-default-icon');
        if (product.image_url) {
            $img.attr('src', product.image_url).removeClass('d-none');
            $icon.addClass('d-none');
        } else {
            $img.addClass('d-none').attr('src', '');
            $icon.removeClass('d-none');
        }

        // Update Stock & Cart status
        const cart = currentCart();
        const inCartItem = cart.find(function (i) { return i.id === product.id; });
        $('.product-in-cart-qty').text(inCartItem ? inCartItem.qty : 0);
        $('.product-available-stock').text(product.current_stock || 0);

        // Disable add to cart if out of stock
        const $btnAdd = $('.btn-add-to-cart');
        if ((product.current_stock || 0) <= 0) {
            $btnAdd.prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary').html('<i class="ti tabler-circle-x me-2"></i> Out of Stock');
        } else {
            $btnAdd.prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary').html('<i class="ti tabler-shopping-cart me-2"></i> Add to Cart');
        }

        showProductDetail();
    }

    $(document).on('click', '.btn-back-from-product', function () {
        activeProduct = null;
        showCatalog();
    });

    $(document).on('click', '.btn-clear-qty', function () {
        $('.product-qty-input').val(1);
    });

    $(document).on('click', '.product-qty-plus-btn', function () {
        const $input = $('.product-qty-input');
        const val = parseInt($input.val(), 10) || 1;
        const max = activeProduct ? activeProduct.current_stock : 999999;

        if (val < max) {
            $input.val(val + 1);
        } else {
            notifyOrder('warning', 'Cannot exceed available stock (' + max + ').');
        }
    });

    $(document).on('click', '.product-qty-minus-btn', function () {
        const $input = $('.product-qty-input');
        const val = parseInt($input.val(), 10) || 1;
        if (val > 1) {
            $input.val(val - 1);
        }
    });

    $(document).on('change', '.product-qty-input', function () {
        const $input = $(this);
        let val = parseInt($input.val(), 10);
        const max = activeProduct ? activeProduct.current_stock : 999999;

        if (isNaN(val) || val < 1) {
            $input.val(1);
        } else if (val > max) {
            $input.val(max);
            notifyOrder('warning', 'Quantity capped at available stock (' + max + ').');
        }
    });

    $(document).on('click', '.btn-add-to-cart', function () {
        if (!activeProduct) return;
        const qty = Math.max(1, parseInt($('.product-qty-input').val(), 10) || 1);
        const max = activeProduct.current_stock;

        if (qty > max) {
            notifyOrder('error', 'Insufficient stock. Available: ' + max);
            return;
        }

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
            existing.discount = product.discount || existing.discount || null;
            existing.tax_percentage = product.tax_percentage || existing.tax_percentage || 0;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                qty: qty,
                discount: product.discount || null,
                tax_percentage: product.tax_percentage || 0
            });
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

    function roundMoney(amount) {
        return Math.round((Number(amount) || 0) * 100) / 100;
    }

    function isTruthyFlag(value) {
        return !(value === false || value === 0 || value === '0');
    }

    function discountType(discount) {
        return discount ? (discount.discount_type || discount.type || '') : '';
    }

    function discountMaxAmount(discount) {
        if (!discount) return null;

        if (discount.max_discount_amount !== undefined && discount.max_discount_amount !== null) {
            return discount.max_discount_amount;
        }

        return discount.max_amount !== undefined ? discount.max_amount : null;
    }

    function discountIsUsable(discount, appliesTo) {
        if (!discount || !isTruthyFlag(discount.is_active)) {
            return false;
        }

        if (appliesTo && discount.applies_to && discount.applies_to !== appliesTo) {
            return false;
        }

        const now = new Date();
        const startsAt = discount.starts_at ? new Date(discount.starts_at) : null;
        const endsAt = discount.ends_at ? new Date(discount.ends_at) : null;

        if (startsAt && !Number.isNaN(startsAt.getTime()) && startsAt > now) {
            return false;
        }

        if (endsAt && !Number.isNaN(endsAt.getTime()) && endsAt < now) {
            return false;
        }

        return true;
    }

    function calculateDiscountAmount(type, value, baseAmount, quantity, fixedPerUnit, maxAmount) {
        const base = Number(baseAmount) || 0;
        const discountValue = Number(value) || 0;

        if (base <= 0 || discountValue <= 0) {
            return 0;
        }

        let amount = 0;

        if (type === 'percentage') {
            amount = base * Math.min(discountValue, 100) / 100;
        } else if (type === 'fixed') {
            amount = discountValue * (fixedPerUnit ? Math.max(Number(quantity) || 1, 1) : 1);
        }

        if (maxAmount !== null && maxAmount !== undefined && maxAmount !== '') {
            amount = Math.min(amount, Math.max(Number(maxAmount) || 0, 0));
        }

        return roundMoney(Math.min(Math.max(amount, 0), base));
    }

    function discountLabel(discount) {
        if (!discount) return '';

        const value = Number(discount.value) || 0;
        const valueLabel = discountType(discount) === 'percentage'
            ? value.toFixed(2).replace(/\.00$/, '') + '%'
            : formatMoney(value);

        return (discount.name || 'Item Discount') + ' (' + valueLabel + ')';
    }

    function formatPercentage(value) {
        return (Number(value) || 0).toFixed(2).replace(/\.?0+$/, '');
    }

    function serviceName(service) {
        if (!service) return '';

        return String(service.name || service.text || '').trim();
    }

    function serviceStandardPrice(service) {
        return roundMoney(service ? service.standard_price : 0);
    }

    function taxPercentage(source) {
        const value = Number(source ? source.tax_percentage : 0) || 0;

        return Math.max(value, 0);
    }

    function serviceFeeServiceKey(serviceFee) {
        const service = serviceFee && serviceFee.service ? serviceFee.service : null;
        const id = service && service.id !== undefined
            ? service.id
            : (serviceFee && serviceFee.service_id !== undefined ? serviceFee.service_id : null);

        return id === null || id === undefined ? '' : String(id);
    }

    function normalizedServiceFeeLine(serviceFee) {
        const service = serviceFee && serviceFee.service ? serviceFee.service : null;
        const rawTitle = serviceFee && serviceFee.title ? String(serviceFee.title).trim() : '';
        const amount = roundMoney(serviceFee ? serviceFee.amount : 0);
        const percentage = roundMoney(serviceFee ? serviceFee.percentage : 0);
        const type = serviceFee && serviceFee.type ? serviceFee.type : (percentage > 0 && amount <= 0 ? 'percentage' : 'fixed');

        return {
            service: service,
            title: rawTitle || serviceName(service) || 'Service Fee',
            amount: amount > 0 ? amount : 0,
            percentage: percentage > 0 ? percentage : 0,
            type: type === 'percentage' ? 'percentage' : 'fixed',
            tax_percentage: serviceFee && serviceFee.tax_percentage !== undefined
                ? taxPercentage(serviceFee)
                : taxPercentage(service)
        };
    }

    function normalizedServiceFees(serviceFees) {
        const fees = Array.isArray(serviceFees)
            ? serviceFees
            : (serviceFees && serviceFees.amount ? [serviceFees] : []);

        return fees
            .map(normalizedServiceFeeLine)
            .filter(function (fee) {
                return fee.amount > 0 || fee.percentage > 0;
            });
    }

    function orderServiceFees(order) {
        if (!order) return [];

        if (Array.isArray(order.serviceFees)) {
            return order.serviceFees;
        }

        return order.serviceFee && order.serviceFee.amount ? [order.serviceFee] : [];
    }

    function serviceFeeLabel(serviceFee) {
        const normalized = normalizedServiceFeeLine(serviceFee);

        if (normalized.type === 'percentage' && normalized.percentage > 0) {
            return normalized.title + ' (' + formatPercentage(normalized.percentage) + '%)';
        }

        return normalized.title;
    }

    function serviceFeesLabel(serviceFees) {
        const fees = normalizedServiceFees(serviceFees);

        if (fees.length === 1) {
            return serviceFeeLabel(fees[0]);
        }

        return fees.length > 1 ? 'Service Fees (' + fees.length + ')' : 'Service Fee';
    }

    function serviceFeeSummaryTitle(serviceFees) {
        return normalizedServiceFees(serviceFees).length > 1 ? 'Service Fees' : 'Service Fee';
    }

    function servicePriceSummaryTitle(serviceFees) {
        const pricedServices = normalizedServiceFees(serviceFees).filter(function (fee) {
            return serviceStandardPrice(fee.service) > 0;
        });

        return pricedServices.length > 1 ? 'Service Prices' : 'Service Price';
    }

    function discountGroupLabel(group) {
        if (!group) return '';
        if (group.label) return group.label;

        const value = Number(group.value) || 0;
        const valueLabel = group.type === 'percentage'
            ? formatPercentage(value) + '%'
            : formatMoney(value);

        return (group.name || 'Customer Discount') + ' (' + valueLabel + ')';
    }

    function customerDiscountMessage(order, totals) {
        const customer = order ? order.customer : null;

        if (!customer) return '';

        const messages = [];
        const customerDiscount = customer.discount || null;

        if (discountIsUsable(customerDiscount, 'customer_profile')) {
            const appliedAmount = totals ? totals.customerDiscount : 0;
            messages.push(
                discountLabel(customerDiscount)
                + (appliedAmount > 0 ? ' applied -' + formatMoney(appliedAmount) : ' selected')
            );
        }

        const group = customer.discount_group || null;
        if (group && isTruthyFlag(group.is_active)) {
            const appliedAmount = totals ? totals.customerGroupDiscount : 0;
            const minLimit = roundMoney(group.min_limit || 0);
            const eligibleBase = totals
                ? roundMoney(Math.max(totals.orderSubtotal - totals.itemDiscount - totals.customerDiscount, 0))
                : 0;
            let label = discountGroupLabel(group);

            if (appliedAmount > 0) {
                label += ' applied -' + formatMoney(appliedAmount);
            } else if (minLimit > 0 && eligibleBase > 0 && eligibleBase < minLimit) {
                label += ' available at ' + formatMoney(minLimit);
            } else if (minLimit > 0) {
                label += ' (min ' + formatMoney(minLimit) + ')';
            }

            messages.push(label);
        }

        return messages.join(' | ');
    }

    function renderCustomerDiscountBanner() {
        const $banner = $('.customer-discount-banner');

        if (!$banner.length) return;

        $banner.addClass('d-none').empty();
    }

    function orderDiscountEmptyHtml() {
        return ''
            + '<div class="text-center py-5 border rounded-3 bg-light bg-opacity-50">'
            + '<span class="text-muted small">No customer or product discounts on this order</span>'
            + '</div>';
    }

    function renderDiscountDrawer() {
        const $lines = $('#order_discount_lines');

        if (!$lines.length) return;

        const order = getActiveOrder();
        const totals = totalsForOrder(order);
        const rows = [];

        (order ? order.items : []).forEach(function (item) {
            const amount = itemDiscountAmount(item);

            if (amount <= 0) return;

            rows.push({
                type: 'Product',
                name: item.name,
                label: discountLabel(item.discount),
                amount: amount,
                muted: false
            });
        });

        const customer = order ? order.customer : null;
        const customerDiscountMessageText = customerDiscountMessage(order, totals);

        if (customerDiscountMessageText !== '') {
            rows.push({
                type: 'Customer',
                name: customer ? customer.text : '',
                label: customerDiscountMessageText,
                amount: totals.customerDiscount + totals.customerGroupDiscount,
                muted: (totals.customerDiscount + totals.customerGroupDiscount) <= 0
            });
        }

        if (rows.length === 0) {
            $lines.html(orderDiscountEmptyHtml());
            return;
        }

        $lines.html(rows.map(function (row) {
            return ''
                + '<div class="order-discount-item' + (row.muted ? ' is-muted' : '') + '">'
                + '<div class="min-w-0">'
                + '<span class="order-discount-type">' + escape(row.type) + '</span>'
                + '<span class="order-discount-name">' + escape(row.name || row.type) + '</span>'
                + '<span class="order-discount-label">' + escape(row.label) + '</span>'
                + '</div>'
                + '<strong>' + (row.amount > 0 ? '-' + formatMoney(row.amount) : 'Ready') + '</strong>'
                + '</div>';
        }).join(''));
    }

    function serviceFeeServiceId(serviceFees, totals) {
        const fees = normalizedServiceFees(serviceFees);
        const amount = roundMoney((totals ? totals.servicePrice : 0) + (totals ? totals.serviceFee : 0));

        if (amount <= 0 || fees.length === 0 || !fees[0].service || !fees[0].service.id) {
            return null;
        }

        return fees[0].service.id;
    }

    function serviceFeeDetailsPayload(serviceFees, totals) {
        const fees = normalizedServiceFees(serviceFees);
        const amount = roundMoney((totals ? totals.servicePrice : 0) + (totals ? totals.serviceFee : 0));

        if (amount <= 0 || fees.length === 0) {
            return null;
        }

        const lines = [];
        fees.forEach(function (fee) {
            const service = fee.service || {};
            const servicePrice = serviceStandardPrice(service);
            const feeAmount = serviceFeeAmountForTotals(fee, totals.productNet);

            if (service.id && servicePrice > 0) {
                lines.push({
                    type: 'service',
                    service_id: service.id,
                    name: serviceName(service) || 'Service Price',
                    amount: servicePrice
                });
            }

            if (feeAmount > 0) {
                lines.push({
                    type: 'manual',
                    service_id: service.id || null,
                    name: fee.title || serviceName(service) || 'Manual Service Fee',
                    amount: roundMoney(feeAmount)
                });
            }
        });

        return {
            title: 'Service Charges',
            type: 'service_charges',
            base_amount: roundMoney(totals.productNet),
            amount: amount,
            fees: lines
        };
    }

    function itemDiscountAmount(item) {
        const qty = Number(item.qty) || 0;
        const lineTotal = roundMoney(qty * (Number(item.price) || 0));

        if (!discountIsUsable(item.discount, 'item')) {
            return 0;
        }

        return calculateDiscountAmount(
            discountType(item.discount),
            item.discount.value,
            lineTotal,
            qty,
            true,
            discountMaxAmount(item.discount)
        );
    }

    function serviceFeeAmountForTotals(fee, baseAmount) {
        if (!fee) return 0;

        if (fee.type === 'percentage' && fee.percentage > 0) {
            return roundMoney((Number(baseAmount) || 0) * (fee.percentage / 100));
        }

        return roundMoney(fee.amount);
    }

    function taxSummaryForLines(lines, customerDiscountAmount) {
        const activeLines = (lines || []).filter(function (line) {
            return roundMoney(line.base) > 0;
        });
        const taxableBaseBeforeCustomerDiscount = roundMoney(activeLines.reduce(function (sum, line) {
            return roundMoney(sum + roundMoney(line.base));
        }, 0));
        const discountToAllocate = roundMoney(Math.min(
            Math.max(Number(customerDiscountAmount) || 0, 0),
            taxableBaseBeforeCustomerDiscount
        ));
        let remainingDiscount = discountToAllocate;

        const taxLines = activeLines.map(function (line, index) {
            const base = roundMoney(line.base);
            const isLast = index === activeLines.length - 1;
            const allocatedDiscount = isLast
                ? remainingDiscount
                : roundMoney(discountToAllocate * (base / taxableBaseBeforeCustomerDiscount));
            const safeAllocatedDiscount = roundMoney(Math.min(Math.max(allocatedDiscount, 0), base, remainingDiscount));
            const taxBase = roundMoney(Math.max(base - safeAllocatedDiscount, 0));
            const rate = taxPercentage(line);
            const taxAmount = roundMoney(taxBase * (rate / 100));

            remainingDiscount = roundMoney(Math.max(remainingDiscount - safeAllocatedDiscount, 0));

            return {
                type: line.type,
                name: line.name,
                quantity: line.quantity || 1,
                rate: rate,
                base: base,
                discount: safeAllocatedDiscount,
                taxBase: taxBase,
                tax: taxAmount
            };
        });

        return {
            base: roundMoney(taxLines.reduce(function (sum, line) {
                return roundMoney(sum + line.taxBase);
            }, 0)),
            tax: roundMoney(taxLines.reduce(function (sum, line) {
                return roundMoney(sum + line.tax);
            }, 0)),
            lines: taxLines
        };
    }

    function totalsForCart(cart, customer, serviceFees) {
        const totals = {
            quantity: 0,
            productSubtotal: 0,
            productNet: 0,
            subtotal: 0,
            orderSubtotal: 0,
            itemDiscount: 0,
            customerDiscount: 0,
            customerGroupDiscount: 0,
            discount: 0,
            servicePrice: 0,
            serviceFee: 0,
            taxBase: 0,
            tax: 0,
            taxLines: [],
            total: 0
        };
        const taxLines = [];

        (cart || []).forEach(function (item) {
            const qty = Number(item.qty) || 0;
            const lineTotal = roundMoney(qty * (Number(item.price) || 0));
            const lineDiscount = itemDiscountAmount(item);
            const netLineTotal = roundMoney(Math.max(lineTotal - lineDiscount, 0));

            totals.quantity += qty;
            totals.productSubtotal = roundMoney(totals.productSubtotal + lineTotal);
            totals.subtotal = totals.productSubtotal;

            totals.itemDiscount = roundMoney(totals.itemDiscount + lineDiscount);
            taxLines.push({
                type: 'Product',
                name: item.name,
                quantity: qty,
                base: netLineTotal,
                tax_percentage: taxPercentage(item)
            });
        });

        totals.productNet = roundMoney(Math.max(totals.productSubtotal - totals.itemDiscount, 0));

        const normalizedFees = normalizedServiceFees(serviceFees);
        normalizedFees.forEach(function (fee) {
            const servicePrice = serviceStandardPrice(fee.service);
            const feeAmount = serviceFeeAmountForTotals(fee, totals.productNet);

            if (servicePrice > 0) {
                taxLines.push({
                    type: 'Service',
                    name: serviceName(fee.service) || serviceFeeLabel(fee),
                    quantity: 1,
                    base: servicePrice,
                    tax_percentage: taxPercentage(fee)
                });
            }

            if (feeAmount > 0) {
                taxLines.push({
                    type: 'Service Fee',
                    name: serviceFeeLabel(fee),
                    quantity: 1,
                    base: feeAmount,
                    tax_percentage: taxPercentage(fee)
                });
            }

            totals.servicePrice = roundMoney(totals.servicePrice + servicePrice);
            totals.serviceFee = roundMoney(totals.serviceFee + feeAmount);
        });

        totals.orderSubtotal = roundMoney(totals.productSubtotal + totals.servicePrice + totals.serviceFee);

        const amountAfterItemDiscounts = roundMoney(Math.max(totals.productNet + totals.servicePrice + totals.serviceFee, 0));
        const customerDiscount = customer ? customer.discount : null;

        if (discountIsUsable(customerDiscount, 'customer_profile')) {
            totals.customerDiscount = calculateDiscountAmount(
                discountType(customerDiscount),
                customerDiscount.value,
                amountAfterItemDiscounts,
                1,
                false,
                discountMaxAmount(customerDiscount)
            );
        }

        const amountAfterCustomerDiscount = roundMoney(Math.max(
            amountAfterItemDiscounts - totals.customerDiscount,
            0
        ));
        const group = customer ? customer.discount_group : null;

        if (group && isTruthyFlag(group.is_active)) {
            const minLimit = roundMoney(group.min_limit || 0);
            if (amountAfterCustomerDiscount > 0 && amountAfterCustomerDiscount >= minLimit) {
                totals.customerGroupDiscount = calculateDiscountAmount(
                    group.type,
                    group.value,
                    amountAfterCustomerDiscount,
                    1,
                    false,
                    null
                );
            }
        }

        totals.discount = roundMoney(Math.min(
            totals.orderSubtotal,
            totals.itemDiscount + totals.customerDiscount + totals.customerGroupDiscount
        ));

        const customerDiscountTotal = roundMoney(totals.customerDiscount + totals.customerGroupDiscount);
        const taxSummary = taxSummaryForLines(taxLines, customerDiscountTotal);

        totals.taxBase = taxSummary.base;
        totals.tax = taxSummary.tax;
        totals.taxLines = taxSummary.lines;
        totals.total = roundMoney(Math.max(totals.orderSubtotal - totals.discount, 0) + totals.tax);

        return totals;
    }

    function totalsForOrder(order) {
        return totalsForCart(
            order ? order.items : [],
            order ? order.customer : null,
            order ? orderServiceFees(order) : []
        );
    }

    function renderCart() {
        const order = getActiveOrder();
        const cart = order ? order.items : [];
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
                const lineTotal = roundMoney(item.price * item.qty);
                const discountAmount = itemDiscountAmount(item);
                const netLineTotal = roundMoney(Math.max(lineTotal - discountAmount, 0));
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
                    + '<div class="col-3 text-end"><span class="fw-bold text-primary small item-price">' + formatMoney(netLineTotal) + '</span></div>'
                    + '</div></td></tr>'
                );
            });
        }

        updateSummary();
    }

    function renderBreakdownHtml(label, amount, prefix, isDiscount) {
        const sign = isDiscount ? '-' : (prefix || '+');
        return '<div class="summary-breakdown-row">'
            + '<span class="breakdown-label">' + escape(label) + '</span>'
            + '<span class="breakdown-value ' + (isDiscount ? 'text-success' : '') + '">' + sign + formatMoney(amount) + '</span>'
            + '</div>';
    }

    function getTaxTypeLabel(type) {
        if (type === 'Product') return 'Product Tax';
        if (type === 'Service' || type === 'Service Fee') return 'Service Tax';
        return type + ' Tax';
    }

    function updateSummary() {
        const order = getActiveOrder();
        const totals = cartTotals();
        const serviceFees = order ? orderServiceFees(order) : [];
        const normalizedFees = normalizedServiceFees(serviceFees);

        $('.summary-qty').text(totals.quantity);
        $('.summary-subtotal').text(formatMoney(totals.productSubtotal));
        $('.summary-discount-lines').toggleClass('d-none', totals.discount <= 0);
        $('.summary-discount').text('-' + formatMoney(totals.discount));

        // 1. Service Price & Breakdowns
        $('.summary-service-price-row').toggleClass('d-none', totals.servicePrice <= 0);
        const $servicePriceBreakdowns = $('.summary-service-price-breakdowns');
        $servicePriceBreakdowns.empty();
        if (totals.servicePrice > 0) {
            $('.summary-service-price-title').text(servicePriceSummaryTitle(serviceFees));
            $('.summary-service-price').text('+' + formatMoney(totals.servicePrice));

            const servicePriceItems = [];
            normalizedFees.forEach(function (fee) {
                const standardPrice = serviceStandardPrice(fee.service);
                if (standardPrice > 0) {
                    servicePriceItems.push({
                        label: serviceName(fee.service) || 'Service Price',
                        amount: standardPrice
                    });
                }
            });

            if (servicePriceItems.length > 0) {
                $servicePriceBreakdowns.html(servicePriceItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $servicePriceBreakdowns.addClass('d-none');
            }
        } else {
            $servicePriceBreakdowns.addClass('d-none');
        }

        // 2. Service Fee & Breakdowns
        $('.summary-service-fee-row').toggleClass('d-none', totals.serviceFee <= 0);
        const $serviceFeeBreakdowns = $('.summary-service-fee-breakdowns');
        $serviceFeeBreakdowns.empty();
        if (totals.serviceFee > 0) {
            $('.summary-service-fee-title').text(serviceFeeSummaryTitle(serviceFees));
            $('.summary-service-fee').text('+' + formatMoney(totals.serviceFee));

            const serviceFeeItems = [];
            normalizedFees.forEach(function (fee) {
                const feeAmount = serviceFeeAmountForTotals(fee, totals.productNet);
                if (feeAmount > 0) {
                    serviceFeeItems.push({
                        label: serviceFeeLabel(fee),
                        amount: feeAmount
                    });
                }
            });

            if (serviceFeeItems.length > 0) {
                $serviceFeeBreakdowns.html(serviceFeeItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $serviceFeeBreakdowns.addClass('d-none');
            }
        } else {
            $serviceFeeBreakdowns.addClass('d-none');
        }

        // 3. Discount & Breakdowns
        const $discountBreakdowns = $('.summary-discount-breakdowns');
        $discountBreakdowns.empty();
        if (totals.discount > 0) {
            const discountItems = [];
            (order ? order.items : []).forEach(function (item) {
                const amount = itemDiscountAmount(item);
                if (amount > 0) {
                    discountItems.push({
                        label: item.name + ' Discount',
                        amount: amount
                    });
                }
            });

            const customer = order ? order.customer : null;
            if (totals.customerDiscount > 0 && customer && customer.discount) {
                discountItems.push({
                    label: discountLabel(customer.discount) || 'Customer Discount',
                    amount: totals.customerDiscount
                });
            }

            if (totals.customerGroupDiscount > 0 && customer && customer.discount_group) {
                discountItems.push({
                    label: discountGroupLabel(customer.discount_group) || 'Discount Group',
                    amount: totals.customerGroupDiscount
                });
            }

            if (discountItems.length > 0) {
                $discountBreakdowns.html(discountItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount, '-', true);
                }).join('')).removeClass('d-none');
            } else {
                $discountBreakdowns.addClass('d-none');
            }
        } else {
            $discountBreakdowns.addClass('d-none');
        }

        // 4. Tax & Breakdowns
        $('.summary-tax-row').toggleClass('d-none', totals.tax <= 0);
        $('.summary-tax').text('+' + formatMoney(totals.tax));
        const $taxBreakdowns = $('.summary-tax-breakdowns');
        $taxBreakdowns.empty();
        if (totals.tax > 0) {
            const groupedTaxes = {};
            (totals.taxLines || []).forEach(function (line) {
                if (line.tax <= 0) return;
                const typeLabel = getTaxTypeLabel(line.type);
                const key = typeLabel + ' (' + formatPercentage(line.rate) + '%)';
                if (!groupedTaxes[key]) {
                    groupedTaxes[key] = 0;
                }
                groupedTaxes[key] = roundMoney(groupedTaxes[key] + line.tax);
            });

            const taxItems = Object.keys(groupedTaxes).map(function (key) {
                return {
                    label: key,
                    amount: groupedTaxes[key]
                };
            });

            if (taxItems.length > 0) {
                $taxBreakdowns.html(taxItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $taxBreakdowns.addClass('d-none');
            }
        } else {
            $taxBreakdowns.addClass('d-none');
        }

        $('.summary-total').text(formatMoney(totals.total));
        $('.btn-pay .text-warning:first').text(formatMoney(totals.total));
        $('.btn-pay').prop('disabled', !order || order.items.length === 0 || totals.orderSubtotal <= 0 || isSavingOrder);
        $('.btn-pay .small').text(isSavingOrder ? 'Saving...' : 'Pay');
        renderCustomerDiscountBanner(totals);
        renderDiscountDrawer();
        refreshOrderDropdown();
    }

    function cartTotals() {
        return totalsForOrder(getActiveOrder());
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
        const order = getActiveOrder();
        const cart = order ? order.items : [];
        const $list = $('.payment-items-list');

        if (!$list.length) return;

        if (cart.length === 0) {
            $list.html('<div class="text-muted text-center py-5">No Items Added</div>');
            return;
        }

        const rows = cart.map(function (item) {
            const lineTotal = roundMoney(item.qty * item.price);
            const discountAmount = itemDiscountAmount(item);
            const netLineTotal = roundMoney(Math.max(lineTotal - discountAmount, 0));

            return ''
                + '<div class="payment-item-row">'
                + '<div>'
                + '<div class="payment-item-name">' + escape(item.name) + '</div>'
                + '<div class="payment-item-meta">' + item.qty + ' x ' + formatMoney(item.price) + '</div>'
                + '</div>'
                + '<div class="text-end">'
                + '<div class="fw-bold text-primary">' + formatMoney(netLineTotal) + '</div>'
                + '</div>'
                + '</div>';
        });

        $list.html(rows.join(''));
    }

    function renderPaymentScreen() {
        const order = getActiveOrder();
        const totals = cartTotals();
        const serviceFees = order ? orderServiceFees(order) : [];
        const normalizedFees = normalizedServiceFees(serviceFees);
        const amount = paymentAmountValue();
        const remaining = Math.max(totals.total - amount, 0);
        const changeDue = Math.max(amount - totals.total, 0);
        const canCheckout = order && order.items.length > 0 && totals.orderSubtotal > 0 && !isSavingOrder;

        $('.payment-order-number').text(paymentOrderNumber());
        $('.payment-total').text(formatMoney(totals.total));
        $('.payment-subtotal').text(formatMoney(totals.productSubtotal));

        // 1. Service Price & Breakdowns
        const $servicePriceSection = $('.payment-service-price-section');
        const $servicePriceBreakdowns = $('.payment-service-price-breakdowns');
        $servicePriceSection.toggleClass('d-none', totals.servicePrice <= 0);
        $servicePriceBreakdowns.empty();
        if (totals.servicePrice > 0) {
            $('.payment-service-price-title').text(servicePriceSummaryTitle(serviceFees) + ':');
            $('.payment-service-price').text('+' + formatMoney(totals.servicePrice));

            const servicePriceItems = [];
            normalizedFees.forEach(function (fee) {
                const standardPrice = serviceStandardPrice(fee.service);
                if (standardPrice > 0) {
                    servicePriceItems.push({
                        label: serviceName(fee.service) || 'Service Price',
                        amount: standardPrice
                    });
                }
            });

            if (servicePriceItems.length > 0) {
                $servicePriceBreakdowns.html(servicePriceItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $servicePriceBreakdowns.addClass('d-none');
            }
        } else {
            $servicePriceBreakdowns.addClass('d-none');
        }

        // 2. Service Fee & Breakdowns
        const $serviceFeeSection = $('.payment-service-fee-section');
        const $serviceFeeBreakdowns = $('.payment-service-fee-breakdowns');
        $serviceFeeSection.toggleClass('d-none', totals.serviceFee <= 0);
        $serviceFeeBreakdowns.empty();
        if (totals.serviceFee > 0) {
            $('.payment-service-fee-title').text(serviceFeeSummaryTitle(serviceFees) + ':');
            $('.payment-service-fee').text('+' + formatMoney(totals.serviceFee));

            const serviceFeeItems = [];
            normalizedFees.forEach(function (fee) {
                const feeAmount = serviceFeeAmountForTotals(fee, totals.productNet);
                if (feeAmount > 0) {
                    serviceFeeItems.push({
                        label: serviceFeeLabel(fee),
                        amount: feeAmount
                    });
                }
            });

            if (serviceFeeItems.length > 0) {
                $serviceFeeBreakdowns.html(serviceFeeItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $serviceFeeBreakdowns.addClass('d-none');
            }
        } else {
            $serviceFeeBreakdowns.addClass('d-none');
        }

        // 3. Discount & Breakdowns
        const $discountSection = $('.payment-discount-section');
        const $discountBreakdowns = $('.payment-discount-breakdowns');
        $discountSection.toggleClass('d-none', totals.discount <= 0);
        $discountBreakdowns.empty();
        if (totals.discount > 0) {
            $('.payment-discount').text('-' + formatMoney(totals.discount));

            const discountItems = [];
            (order ? order.items : []).forEach(function (item) {
                const amount = itemDiscountAmount(item);
                if (amount > 0) {
                    discountItems.push({
                        label: item.name + ' Discount',
                        amount: amount
                    });
                }
            });

            const customer = order ? order.customer : null;
            if (totals.customerDiscount > 0 && customer && customer.discount) {
                discountItems.push({
                    label: discountLabel(customer.discount) || 'Customer Discount',
                    amount: totals.customerDiscount
                });
            }

            if (totals.customerGroupDiscount > 0 && customer && customer.discount_group) {
                discountItems.push({
                    label: discountGroupLabel(customer.discount_group) || 'Discount Group',
                    amount: totals.customerGroupDiscount
                });
            }

            if (discountItems.length > 0) {
                $discountBreakdowns.html(discountItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount, '-', true);
                }).join('')).removeClass('d-none');
            } else {
                $discountBreakdowns.addClass('d-none');
            }
        } else {
            $discountBreakdowns.addClass('d-none');
        }

        // 4. Tax & Breakdowns
        const $taxSection = $('.payment-tax-section');
        const $taxBreakdowns = $('.payment-tax-breakdowns');
        $taxSection.toggleClass('d-none', totals.tax <= 0);
        $taxBreakdowns.empty();
        if (totals.tax > 0) {
            $('.payment-tax').text('+' + formatMoney(totals.tax));

            const groupedTaxes = {};
            (totals.taxLines || []).forEach(function (line) {
                if (line.tax <= 0) return;
                const typeLabel = getTaxTypeLabel(line.type);
                const key = typeLabel + ' (' + formatPercentage(line.rate) + '%)';
                if (!groupedTaxes[key]) {
                    groupedTaxes[key] = 0;
                }
                groupedTaxes[key] = roundMoney(groupedTaxes[key] + line.tax);
            });

            const taxItems = Object.keys(groupedTaxes).map(function (key) {
                return {
                    label: key,
                    amount: groupedTaxes[key]
                };
            });

            if (taxItems.length > 0) {
                $taxBreakdowns.html(taxItems.map(function (item) {
                    return renderBreakdownHtml(item.label, item.amount);
                }).join('')).removeClass('d-none');
            } else {
                $taxBreakdowns.addClass('d-none');
            }
        } else {
            $taxBreakdowns.addClass('d-none');
        }

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
        const totals = cartTotals();
        const amount = paymentAmountValue();

        if (!paymentMethod) {
            $('.payment-methods').addClass('is-invalid');
            notifyOrder('error', 'Oops! Please select a payment method.');
            return false;
        }

        if (totals.total > 0 && amount <= 0) {
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
        markSelectInvalid($('#order_service_filter'), false);
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

        const totals = cartTotals();
        const invalidFee = normalizedServiceFees(orderServiceFees(order)).find(function (fee) {
            return !fee.service || !fee.service.id;
        });
        if ((totals.servicePrice > 0 || totals.serviceFee > 0) && invalidFee) {
            notifyOrder('error', 'Please select a service before applying the service fee.');
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
        if (!order) return null;

        const totals = cartTotals();
        if (order.items.length === 0) return null;

        const serviceFees = orderServiceFees(order);
        const serviceFeeDetails = serviceFeeDetailsPayload(orderServiceFees(order), totals);

        return {
            customer_id: order.customer ? order.customer.id : null,
            vehicle_id: order.vehicle ? order.vehicle.id : null,
            payment: {
                method: paymentMethod,
                amount: paymentAmountValue(),
            },
            service_id: serviceFeeServiceId(serviceFees, totals),
            service_fees: serviceFeeDetails ? serviceFeeDetails.fees : [],
            service_fee_amount: roundMoney((totals.servicePrice || 0) + (totals.serviceFee || 0)),
            service_fee_details: serviceFeeDetails,
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
        const order = getActiveOrder();

        if (!order) return;

        order.items.length = 0;
        order.serviceFees = [];
        clearServiceFeeForm();
        renderServiceFeeLines();
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
            setPaymentAmount(cartTotals().total.toFixed(2));
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

            if ($this.attr('id') === 'order_service_filter' && $.fn.select2.amd) {
                const Utils = $.fn.select2.amd.require('select2/utils');
                const Dropdown = $.fn.select2.amd.require('select2/dropdown');
                const DropdownSearch = $.fn.select2.amd.require('select2/dropdown/search');
                const AttachBody = $.fn.select2.amd.require('select2/dropdown/attachBody');

                options.dropdownAdapter = Utils.Decorate(
                    Utils.Decorate(Dropdown, DropdownSearch),
                    AttachBody
                );
                options.closeOnSelect = false;
            }

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

                        if ($this.attr('id') === 'order_service_filter') {
                            q.active_only = 1;
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
            updateSummary();
        });

        $('#add_vehicle_filter').on('change', function () {
            markSelectInvalid($(this), false);

            if (isRestoringOrderMeta) return;

            const order = getActiveOrder();
            if (order) {
                order.vehicle = readSelectSelection($(this));
            }
        });

        $('#order_service_filter').on('change', function () {
            const $serviceSelect = $(this);
            markSelectInvalid($serviceSelect, false);

            renderSelectedServiceFeeRows();
        });
    }

    // ─── Custom Service Fee Handlers ─────────────────────────────────────

    function selectedServiceFeeRowState() {
        const state = {};

        $('#selected_service_fee_rows .selected-service-fee-row').each(function () {
            const $row = $(this);
            const serviceId = String($row.data('service-id') || '');

            if (!serviceId) return;

            state[serviceId] = {
                title: ($row.find('.selected-service-fee-title').val() || '').trim(),
                amount: ($row.find('.selected-service-fee-amount').val() || '').trim()
            };
        });

        return state;
    }

    function serviceFeeForService(order, service) {
        if (!order || !service || !service.id) return null;

        const serviceKey = String(service.id);

        return orderServiceFees(order).find(function (fee) {
            return serviceFeeServiceKey(fee) === serviceKey;
        }) || null;
    }

    function serviceFeeRowMeta(service) {
        const meta = [];
        const standardPrice = serviceStandardPrice(service);

        if (service.code) {
            meta.push(service.code);
        }

        if (service.category_name) {
            meta.push(service.category_name);
        }

        if (standardPrice > 0) {
            meta.push('Standard ' + formatMoney(standardPrice));
        }

        if (taxPercentage(service) > 0) {
            meta.push('Tax ' + formatPercentage(taxPercentage(service)) + '%');
        }

        return meta.join(' - ');
    }

    function renderSelectedServiceFeeRows() {
        const $rows = $('#selected_service_fee_rows');
        if (!$rows.length) return;

        const order = getActiveOrder();
        const services = readServiceFeeSelections();
        const stagedState = selectedServiceFeeRowState();

        if (services.length === 0) {
            $rows.html('<div class="selected-service-fee-empty"><span class="text-muted small">No service selected</span></div>');
            return;
        }

        $rows.html(services.map(function (service) {
            const serviceId = String(service.id);
            const existingFee = serviceFeeForService(order, service);
            const staged = stagedState[serviceId] || {};
            const title = staged.title !== undefined
                ? staged.title
                : (existingFee ? serviceFeeLabel(existingFee) : serviceName(service));
            const amount = staged.amount !== undefined
                ? staged.amount
                : (existingFee ? roundMoney(existingFee.amount).toFixed(2) : '');
            const meta = serviceFeeRowMeta(service);

            return ''
                + '<div class="selected-service-fee-row" data-service-id="' + escape(serviceId) + '">'
                + '<div class="selected-service-fee-row-header">'
                + '<div class="min-w-0">'
                + '<span class="selected-service-fee-name">' + escape(serviceName(service) || service.text || serviceId) + '</span>'
                + (meta ? '<span class="selected-service-fee-meta">' + escape(meta) + '</span>' : '')
                + '</div>'
                + '<button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill remove-selected-service-row" data-service-id="' + escape(serviceId) + '" title="Remove service">'
                + '<i class="ti tabler-x"></i>'
                + '</button>'
                + '</div>'
                + '<div class="selected-service-fee-fields">'
                + '<div>'
                + '<label class="form-label fw-bold small">Fee Label</label>'
                + '<input type="text" class="form-control py-2 selected-service-fee-title" value="' + escape(title) + '" placeholder="' + escape(serviceName(service) || 'Service fee') + '">'
                + '</div>'
                + '<div>'
                + '<label class="form-label fw-bold small">Manual Amount</label>'
                + '<div class="input-group input-group-merge">'
                + '<span class="input-group-text">$</span>'
                + '<input type="number" step="0.01" min="0" class="form-control py-2 selected-service-fee-amount" value="' + escape(amount) + '" placeholder="0.00">'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        }).join(''));
    }

    function readSelectedServiceFeeRows() {
        const servicesById = {};

        readServiceFeeSelections().forEach(function (service) {
            servicesById[String(service.id)] = service;
        });

        const rows = [];

        $('#selected_service_fee_rows .selected-service-fee-row').each(function () {
            const $row = $(this);
            const serviceId = String($row.data('service-id') || '');
            const service = servicesById[serviceId];

            if (!service) return;

            const amountText = ($row.find('.selected-service-fee-amount').val() || '').trim();

            rows.push({
                service: service,
                title: ($row.find('.selected-service-fee-title').val() || '').trim(),
                amountText: amountText,
                amount: parseFloat(amountText),
                $row: $row
            });
        });

        return rows;
    }

    function clearServiceFeeForm() {
        setSelectSelections($('#order_service_filter'), []);
        renderSelectedServiceFeeRows();
        markSelectInvalid($('#order_service_filter'), false);
    }

    function renderServiceFeeLines() {
        const order = getActiveOrder();
        const fees = normalizedServiceFees(order ? orderServiceFees(order) : []);
        const $lines = $('#service_fee_lines');

        if (!$lines.length) return;

        if (fees.length === 0) {
            $lines.html('<div class="service-fee-empty"><span class="text-muted small">No service fees added</span></div>');
            return;
        }

        $lines.html(fees.map(function (fee, index) {
            const serviceText = serviceName(fee.service) || 'Service';

            return ''
                + '<div class="service-fee-item">'
                + '<div class="min-w-0">'
                + '<span class="service-fee-item-name">' + escape(serviceFeeLabel(fee)) + '</span>'
                + '<span class="service-fee-item-type">' + escape(serviceText) + '</span>'
                + '</div>'
                + '<div class="d-flex align-items-center gap-2">'
                + '<span class="service-fee-item-amount">' + formatMoney(fee.amount) + '</span>'
                + '<button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill remove-service-fee-line" data-service-fee-index="' + index + '" title="Remove fee">'
                + '<i class="ti tabler-trash text-danger"></i>'
                + '</button>'
                + '</div>'
                + '</div>';
        }).join(''));
    }

    function fillServiceFeeForm() {
        clearServiceFeeForm();
        renderServiceFeeLines();
    }

    function syncServiceFeeFromForm(options) {
        options = options || {};

        const order = ensureActiveOrder();

        const services = readServiceFeeSelections();
        const rows = readSelectedServiceFeeRows();
        const hasAnyFeeInput = services.length > 0;

        if (!hasAnyFeeInput) {
            return true;
        }

        if (services.length === 0) {
            if (options.notify) {
                markSelectInvalid($('#order_service_filter'), true);
                notifyOrder('error', 'Please select at least one service before applying the service fee.');
            }

            return false;
        }

        let invalidRow = null;

        rows.forEach(function (row) {
            row.$row.removeClass('is-invalid');
            row.$row.find('.selected-service-fee-amount').removeClass('is-invalid');
        });

        rows.some(function (row) {
            if (isNaN(row.amount) || row.amount <= 0) {
                invalidRow = row;
                return true;
            }

            return false;
        });

        if (invalidRow) {
            invalidRow.$row.addClass('is-invalid');
            invalidRow.$row.find('.selected-service-fee-amount').addClass('is-invalid');

            if (options.notify) {
                notifyOrder('error', 'Please enter a valid fee amount for each selected service.');
                invalidRow.$row.find('.selected-service-fee-amount').trigger('focus');
            }

            return false;
        }

        order.serviceFees = orderServiceFees(order);
        rows.forEach(function (row) {
            const nextFee = {
                service: row.service,
                title: row.title || serviceName(row.service) || 'Service Fee',
                amount: roundMoney(row.amount),
                tax_percentage: taxPercentage(row.service)
            };
            const serviceKey = serviceFeeServiceKey(nextFee);
            const existingIndex = order.serviceFees.findIndex(function (fee) {
                return serviceFeeServiceKey(fee) === serviceKey;
            });

            if (existingIndex >= 0) {
                order.serviceFees.splice(existingIndex, 1, nextFee);
            } else {
                order.serviceFees.push(nextFee);
            }
        });

        clearServiceFeeForm();
        renderServiceFeeLines();

        return true;
    }

    $(document).on('click', '#apply_custom_service_fee', function () {
        ensureActiveOrder();

        if (!syncServiceFeeFromForm({ notify: true, requireAmount: true })) return;

        renderCart();
    });

    $(document).on('click', '#remove_custom_service_fee', function () {
        clearServiceFeeForm();
        renderServiceFeeLines();
    });

    $(document).on('input', '.selected-service-fee-title, .selected-service-fee-amount', function () {
        const $row = $(this).closest('.selected-service-fee-row');

        $row.removeClass('is-invalid');
        $row.find('.selected-service-fee-amount').removeClass('is-invalid');
    });

    $(document).on('click', '.remove-selected-service-row', function () {
        const serviceId = String($(this).data('service-id') || '');
        const services = readServiceFeeSelections().filter(function (service) {
            return String(service.id) !== serviceId;
        });

        setSelectSelections($('#order_service_filter'), services);
        renderSelectedServiceFeeRows();
    });

    $(document).on('click', '.remove-service-fee-line', function () {
        const order = getActiveOrder();
        if (!order) return;

        const index = Number($(this).data('service-fee-index'));
        order.serviceFees = orderServiceFees(order);

        if (!Number.isNaN(index) && index >= 0) {
            order.serviceFees.splice(index, 1);
        }

        renderServiceFeeLines();
        renderCart();
    });

    // ─── Boot ──────────────────────────────────────────────────────────

    $(function () {
        navStack.push({ level: 'categories', meta: {} });
        updateHeader();
        showCatalog();
        loadCategories('');
        initSelect2();

        $('#offcanvasServiceFee').on('show.bs.offcanvas', function () {
            fillServiceFeeForm();
        });
        $('#offcanvasDiscount').on('show.bs.offcanvas', function () {
            renderDiscountDrawer();
        });
        refreshOrderDropdown();
        renderCart();

        // Initialize centralized CustomerManager for "Add Customer" modal
        if (typeof window.CustomerManager === 'function') {
            new window.CustomerManager({
                onSaveSuccess: function (response) {
                    const $customerSelect = $('#customer_type_filter');
                    const customer = response.data || {};

                    if (customer.id && $customerSelect.length) {
                        const selection = customerSelectionFromData(customer);
                        const newOption = new Option(selection.text, selection.id, true, true);
                        $(newOption).data('selection', selection);
                        $customerSelect.append(newOption).trigger('change');
                        $customerSelect.trigger({
                            type: 'select2:select',
                            params: { data: selection }
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
                        const currentCustomer = readSelectSelection($customerSelect);
                        const selection = currentCustomer && String(currentCustomer.id) === String(vehicle.customer_id) ? currentCustomer : {
                            id: vehicle.customer_id,
                            text: customerText
                        };
                        const customerOption = new Option(selection.text, selection.id, true, true);
                        $(customerOption).data('selection', selection);
                        $customerSelect.append(customerOption).trigger('change');
                        $customerSelect.trigger({
                            type: 'select2:select',
                            params: { data: selection }
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
