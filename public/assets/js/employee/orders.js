(function ($) {
  'use strict';

  if (typeof $ === 'undefined') return;
  if (window.__employeeOrdersInitialized) return;
  window.__employeeOrdersInitialized = true;

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const config = window.employeeOrdersConfig || {};
  const $page = $('.employee-orders-page');
  const sortStorageKey = 'employee.orders.sortPreference';
  const supportedSorts = [
    'customer_name',
    'date_opened',
    'order_id',
    'order_total',
    'latest',
    'oldest',
    'amount_desc',
    'amount_asc'
  ];

  if (!$page.length) {
    return;
  }

  const readStoredSort = function () {
    try {
      const storedSort = window.localStorage.getItem(sortStorageKey);

      return supportedSorts.indexOf(storedSort) >= 0 ? storedSort : 'order_id';
    } catch (error) {
      return 'order_id';
    }
  };

  const urlParams = new URLSearchParams(window.location.search);
  const initialTab = urlParams.get('tab');
  const supportedTabs = ['today', 'all', 'pending', 'estimates'];

  const state = {
    tab: (supportedTabs.indexOf(initialTab) >= 0) ? initialTab : 'all',
    q: '',
    sort: readStoredSort(),
    date_from: '',
    date_to: '',
    date_preset: 'all',
    search_fields: [],
    page: 1,
    per_page: 12,
    loading: false,
    has_more: true
  };

  const escapeHtml = function (value) {
    return $('<div>').text(value ?? '').html();
  };

  const debounce = function (callback, delay) {
    let timeout = null;

    return function () {
      const args = arguments;
      const context = this;
      window.clearTimeout(timeout);
      timeout = window.setTimeout(function () {
        callback.apply(context, args);
      }, delay);
    };
  };

  const parseMinResults = function (raw) {
    if (raw == null || raw === '') return 0;
    if (typeof raw === 'number') return raw;

    const value = String(raw).trim();
    if (value.toLowerCase() === 'infinity') return Infinity;

    const number = Number(value);
    return Number.isNaN(number) ? 0 : number;
  };

  const initSelect2 = function () {
    const $selects = $page.find('.select2');

    if (typeof $.fn.select2 !== 'function' || !$selects.length) {
      return;
    }

    $selects.each(function () {
      const $select = $(this);
      if ($select.data('select2')) return;

      const dropdownParentSelector = $select.data('dropdown-parent');
      if (!dropdownParentSelector && !$select.parent().hasClass('position-relative')) {
        $select.wrap('<div class="position-relative"></div>');
      }

      $select.select2({
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $select.parent(),
        placeholder: $select.data('placeholder'),
        allowClear: Boolean($select.data('allow-clear')),
        minimumResultsForSearch: parseMinResults($select.data('minimum-results-for-search'))
      });
    });
  };

  const toDateInputValue = function (date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return year + '-' + month + '-' + day;
  };

  const dateRangeFromPreset = function (preset) {
    const today = new Date();

    if (preset === 'today') {
      const value = toDateInputValue(today);

      return { date_from: value, date_to: value };
    }

    if (preset === 'yesterday') {
      const yesterday = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 1);
      const value = toDateInputValue(yesterday);

      return { date_from: value, date_to: value };
    }

    if (preset === 'last_7') {
      const start = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 6);

      return {
        date_from: toDateInputValue(start),
        date_to: toDateInputValue(today)
      };
    }

    if (preset === 'this_month') {
      const start = new Date(today.getFullYear(), today.getMonth(), 1);
      const end = new Date(today.getFullYear(), today.getMonth() + 1, 0);

      return {
        date_from: toDateInputValue(start),
        date_to: toDateInputValue(end)
      };
    }

    return { date_from: '', date_to: '' };
  };

  const syncSearchInputs = function (value, sourceInput) {
    $('[data-order-search]').each(function () {
      if (this !== sourceInput) {
        $(this).val(value);
      }
    });
  };

  const collectSearchFields = function () {
    return $('[data-order-search-field]:checked')
      .map(function () {
        return $(this).val();
      })
      .get();
  };

  const syncDatePreset = function (preset) {
    const $datePreset = $('[data-order-date-preset]');

    $datePreset.val(preset);
    if ($datePreset.data('select2')) {
      $datePreset.trigger('change.select2');
    }

    $('[data-order-custom-range]').toggleClass('d-none', preset !== 'custom');
  };

  const syncSortPreference = function () {
    $('[data-order-sort-option]').each(function () {
      const $option = $(this);
      const isActive = $option.data('order-sort-option') === state.sort;

      $option.toggleClass('active', isActive);
      $option.attr('aria-selected', isActive ? 'true' : 'false');
    });
  };

  const storeSortPreference = function () {
    try {
      window.localStorage.setItem(sortStorageKey, state.sort);
    } catch (error) {
      //
    }
  };

  const updateDateRange = function (preset) {
    state.date_preset = preset || 'all';
    syncDatePreset(state.date_preset);

    if (state.date_preset === 'custom') {
      state.date_from = $('[data-order-date-from]').val() || '';
      state.date_to = $('[data-order-date-to]').val() || '';
      return;
    }

    const range = dateRangeFromPreset(state.date_preset);
    state.date_from = range.date_from;
    state.date_to = range.date_to;
    $('[data-order-date-from], [data-order-date-to]').val('');
  };

  const notifyError = function (message) {
    if (typeof window.appNotify === 'function') {
      window.appNotify('error', message);
      return;
    }

    if (window.Notiflix && window.Notiflix.Notify) {
      window.Notiflix.Notify.failure(message);
      return;
    }

    if (window.Swal) {
      window.Swal.fire('Oops!', message, 'error');
    }
  };

  const setLoading = function (isLoading) {
    $('[data-order-list]').toggleClass('d-none', isLoading);
    $('[data-order-refresh]').prop('disabled', isLoading);
    $('[data-advanced-order-list]').toggleClass('d-none', isLoading);

    if (isLoading) {
      $('[data-order-loading], [data-advanced-order-loading]').removeClass('d-none');
      $('[data-order-empty]').addClass('d-none');
      $('[data-advanced-order-empty]').addClass('d-none');
    } else {
      $('[data-order-loading], [data-advanced-order-loading]').addClass('d-none');
    }
  };

  const updateCounts = function (counts) {
    counts = counts || {};

    ['today', 'all', 'pending', 'estimates'].forEach(function (key) {
      $('[data-order-count="' + key + '"]').text(counts[key] || 0);
    });
  };

  const makeStatusClass = function (statusClass) {
    const supported = ['success', 'warning', 'secondary', 'info'];

    return supported.indexOf(statusClass) >= 0 ? statusClass : 'secondary';
  };

  const detailUrlForOrder = function (orderId) {
    if (!config.detailUrlTemplate || !orderId) {
      return '';
    }

    return config.detailUrlTemplate.replace('__ORDER_ID__', encodeURIComponent(orderId));
  };

  const makeOrderCard = function (order, options) {
    options = options || {};

    const vehicle = order.vehicle_label
      ? '<div class="employee-order-vehicle">' + escapeHtml(order.vehicle_label) + '</div>'
      : '';
    const compactClass = options.compact ? ' employee-order-card-compact' : '';
    const detailUrl = detailUrlForOrder(order.id);

    return [
      '<article class="employee-order-card' + compactClass + '" data-order-id="' + escapeHtml(order.id) + '" data-order-detail-url="' + escapeHtml(detailUrl) + '" role="link" tabindex="0">',
      '  <div class="employee-order-card-top">',
      '    <div class="employee-order-number">Order # ' + escapeHtml(order.order_number) + '</div>',
      '    <span class="employee-order-status ' + makeStatusClass(order.status_class) + '">' + escapeHtml(order.status_label) + '</span>',
      '  </div>',
      '  <div class="employee-order-card-middle">',
      '    <div class="employee-order-customer">',
      '      <i class="ti tabler-user"></i>',
      '      <span>' + escapeHtml(order.customer_name) + '</span>',
      '    </div>',
      '    <div class="employee-order-amount">' + escapeHtml(order.total_amount_label) + '</div>',
      '  </div>',
      vehicle,
      '  <div class="employee-order-meta">' + escapeHtml(order.created_at_label) + '</div>',
      '</article>'
    ].join('');
  };

  const renderOrders = function (orders, append = false) {
    const $list = $('[data-order-list]');
    const $empty = $('[data-order-empty]');

    orders = orders || [];

    if (!append) {
      $list.empty();
    }

    if (!orders.length && !append) {
      $empty.removeClass('d-none');
      return;
    }

    $empty.addClass('d-none');
    const html = orders.map(function (order) {
      return makeOrderCard(order);
    }).join('');

    if (append) {
      $list.append(html);
    } else {
      $list.html(html);
    }
  };

  const renderAdvancedOrders = function (orders, append = false) {
    const $list = $('[data-advanced-order-list]');
    const $empty = $('[data-advanced-order-empty]');

    if (!$list.length) {
      return;
    }

    orders = orders || [];

    if (!append) {
      $list.empty();
    }

    if (!orders.length && !append) {
      $empty.removeClass('d-none');
      return;
    }

    $empty.addClass('d-none');
    const html = orders.map(function (order) {
      return makeOrderCard(order, { compact: true });
    }).join('');

    if (append) {
      $list.append(html);
    } else {
      $list.html(html);
    }
  };

  const requestParams = function () {
    return {
      tab: state.tab,
      q: state.q,
      sort: state.sort,
      date_from: state.date_from,
      date_to: state.date_to,
      search_fields: state.search_fields,
      page: state.page,
      per_page: state.per_page
    };
  };

  const loadOrders = function (append = false) {
    if (!config.listingUrl) {
      notifyError('Orders listing route is missing.');
      return;
    }

    if (state.loading) {
      return;
    }

    state.loading = true;
    setLoading(true);

    $.ajax({
      url: config.listingUrl,
      method: 'GET',
      data: requestParams(),
      dataType: 'json'
    })
      .done(function (response) {
        updateCounts(response.counts);
        renderOrders(response.orders, append);
        renderAdvancedOrders(response.orders, append);

        if (response.pagination) {
          state.has_more = response.pagination.has_more;
          state.page = response.pagination.current_page;
        }
      })
      .fail(function (xhr) {
        const message = xhr.responseJSON && xhr.responseJSON.message
          ? xhr.responseJSON.message
          : 'Unable to load orders right now.';

        if (!append) {
          renderOrders([]);
          renderAdvancedOrders([]);
        }
        notifyError(message);
      })
      .always(function () {
        state.loading = false;
        setLoading(false);
      });
  };

  const resetPagination = function () {
    state.page = 1;
    state.has_more = true;
  };

  const loadMoreOrders = function () {
    if (!state.has_more || state.loading) {
      return;
    }

    state.page++;
    loadOrders(true);
  };

  const bindEvents = function () {
    $('[data-order-tab]').on('click', function () {
      const $tab = $(this);

      state.tab = $tab.data('order-tab') || 'all';
      $('[data-order-tab]').removeClass('active');
      $tab.addClass('active');
      resetPagination();
      loadOrders();
    });

    $('[data-order-search]').on('input', debounce(function () {
      const value = $(this).val();

      state.q = value.trim();
      syncSearchInputs(value, this);
      resetPagination();
      loadOrders();
    }, 300));

    $('[data-order-sort-option]').on('click', function () {
      state.sort = $(this).data('order-sort-option') || 'order_id';
      syncSortPreference();
      storeSortPreference();
      resetPagination();
      loadOrders();
    });

    $('[data-order-date-preset]').on('change', function () {
      updateDateRange($(this).val() || 'all');
      resetPagination();
      loadOrders();
    });

    $('[data-order-date-from], [data-order-date-to]').on('change', function () {
      updateDateRange('custom');
      resetPagination();
      loadOrders();
    });

    $('[data-order-search-field]').on('change', function () {
      state.search_fields = collectSearchFields();
      resetPagination();
      loadOrders();
    });

    $('[data-order-refresh]').on('click', function () {
      resetPagination();
      loadOrders();
    });

    // Infinite scroll
    $(window).on('scroll', debounce(function () {
      const $list = $('[data-order-list]');
      if (!$list.length) return;

      const windowHeight = $(window).height();
      const scrollTop = $(window).scrollTop();
      const listHeight = $list.height();
      const listOffset = $list.offset().top;

      // Load more when user scrolls near the bottom of the list
      if (scrollTop + windowHeight >= listOffset + listHeight - 200) {
        loadMoreOrders();
      }
    }, 100));

    $(document).on('click', '[data-order-detail-url]', function () {
      const detailUrl = $(this).data('order-detail-url');

      if (detailUrl) {
        window.location.href = detailUrl;
      }
    });

    $(document).on('keydown', '[data-order-detail-url]', function (event) {
      if (event.key !== 'Enter' && event.key !== ' ') {
        return;
      }

      event.preventDefault();
      $(this).trigger('click');
    });
  };

  $(function () {
    // Sync active tab class in UI based on initial state
    $('[data-order-tab]').removeClass('active');
    $('[data-order-tab="' + state.tab + '"]').addClass('active');

    initSelect2();
    bindEvents();
    syncDatePreset(state.date_preset);
    syncSortPreference();

    if (window.Helpers && window.Helpers.initToolTip) {
      window.Helpers.initToolTip(document);
    }

    loadOrders();
  });
})(window.jQuery);
