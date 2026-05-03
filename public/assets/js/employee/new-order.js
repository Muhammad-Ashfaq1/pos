/**
 * Employee — New Order page orchestrator.
 *
 * Renders unified catalog cards (categories / sub-cats / products / search results)
 * using the shared <template id="catalog-card-template"> so every level looks identical.
 * Talks to the backend exclusively through window.Catalog (catalog-api.js).
 *
 * Cart is FRONT-END ONLY (no backend persistence) — qty/price aggregation lives
 * in the DOM and the right-hand summary updates client-side.
 */

(function ($) {
    'use strict';

    if (typeof $ === 'undefined') return;

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    });

    // ─── Navigation state ──────────────────────────────────────────────
    // Stack of { level: 'categories'|'subCategories'|'products', meta: {...} }
    const navStack = [];
    let searchTimer = null;
    const cart = []; // [{ id, name, price, qty }]

    // ─── DOM refs ──────────────────────────────────────────────────────
    const $title = $('.catalog-title');
    const $backBtn = $('.catalog-back-btn');
    const $searchInput = $('.catalog-search');
    const $searchClear = $('.catalog-search-clear');
    const $catalogView = $('.catalog-view');
    const $grid = $('.catalog-grid');
    const $searchView = $('.catalog-search-view');
    const $productView = $('.product-details-view');

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
        const existing = cart.find(function (i) { return i.id === product.id; });
        if (existing) {
            existing.qty += qty;
        } else {
            cart.push({ id: product.id, name: product.name, price: product.price, qty: qty });
        }
        renderCart();
    }

    function removeFromCart(productId) {
        const idx = cart.findIndex(function (i) { return i.id === productId; });
        if (idx >= 0) cart.splice(idx, 1);
        renderCart();
    }

    function changeQty(productId, delta) {
        const item = cart.find(function (i) { return i.id === productId; });
        if (!item) return;
        item.qty = Math.max(1, item.qty + delta);
        renderCart();
    }

    function renderCart() {
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
        const totalQty = cart.reduce(function (s, i) { return s + i.qty; }, 0);
        const totalAmount = cart.reduce(function (s, i) { return s + i.qty * i.price; }, 0);

        $('.summary-qty').text(totalQty);
        $('.summary-subtotal').text('$' + totalAmount.toFixed(2));
        $('.summary-total').text('$' + totalAmount.toFixed(2));
        $('.btn-pay .text-warning:first').text('$' + totalAmount.toFixed(2));
        $('.btn-pay').prop('disabled', totalAmount <= 0);
    }

    function escape(value) {
        return $('<div>').text(value ?? '').html();
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
        cart.length = 0;
        renderCart();
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

    function initStaticSelect2() {
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

            $this.select2({
                dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
                placeholder: $this.data('placeholder'),
                allowClear: Boolean($this.data('allow-clear')),
                minimumResultsForSearch: parseMinResults($this.data('minimum-results-for-search')),
            });
        });
    }

    // ─── Boot ──────────────────────────────────────────────────────────

    $(function () {
        navStack.push({ level: 'categories', meta: {} });
        updateHeader();
        showCatalog();
        loadCategories('');
        renderCart();
        initStaticSelect2();
    });

})(window.jQuery);
