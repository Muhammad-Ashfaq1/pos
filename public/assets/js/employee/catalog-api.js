/**
 * Central Catalog API — single source of truth for fetching categories,
 * sub-categories, products, and unified search results.
 *
 * Usage:
 *   Catalog.getAllCategories('search term')
 *   Catalog.getSubCategories({ categoryId: 5, q: 'oil' })
 *   Catalog.getProducts({ subCategoryId: 12, q: '' })
 *   Catalog.getProducts({ categoryId: 3 })       // products of an entire category
 *   Catalog.search('synthetic')                  // returns { categories, sub_categories, products }
 *
 * All methods return a jQuery Deferred (jqXHR), so you can chain .done()/.fail().
 */

window.Catalog = (function ($) {
    'use strict';

    function routes() {
        return window.catalogRoutes || {};
    }

    function getAllCategories(q) {
        return $.get(routes().categories, { q: q || '' });
    }

    function getSubCategories(opts) {
        opts = opts || {};
        const params = { q: opts.q || '' };
        if (opts.categoryId) params.category_id = opts.categoryId;
        return $.get(routes().subCategories, params);
    }

    function getProducts(opts) {
        opts = opts || {};
        const params = { q: opts.q || '' };
        if (opts.subCategoryId) params.sub_category_id = opts.subCategoryId;
        if (opts.categoryId) params.category_id = opts.categoryId;
        return $.get(routes().products, params);
    }

    function search(q) {
        return $.get(routes().search, { q: q || '' });
    }

    return {
        getAllCategories: getAllCategories,
        getSubCategories: getSubCategories,
        getProducts: getProducts,
        search: search,
    };
})(window.jQuery);
