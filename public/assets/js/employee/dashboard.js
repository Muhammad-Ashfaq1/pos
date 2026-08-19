(function ($) {
  'use strict';

  if (typeof $ === 'undefined') return;
  if (window.__employeeDashboardInitialized) return;
  window.__employeeDashboardInitialized = true;

  const config = window.employeeDashboardConfig || {};
  const $card = $('#employee-product-mix');

  if (!$card.length || !config.productMixUrl) {
    return;
  }

  const $rangeRoot = $card.find('[data-dashboard-range]');
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const currencySymbol = String(config.currencySymbol || '$');
  const palette = Array.isArray(config.chartPalette) && config.chartPalette.length
    ? config.chartPalette
    : ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE', '#826AF9', '#FFB400'];

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json'
    }
  });

  const $timeEl = $card.find('[data-product-mix-updated-time]');
  const $refreshBtn = $card.find('[data-product-mix-refresh]');
  const $statusDot = $card.find('[data-product-mix-status]');
  const $topBody = $card.find('[data-product-mix-top-body]');
  const $topEmpty = $card.find('[data-product-mix-top-products-empty]');
  const $categoryBody = $card.find('[data-product-mix-category-body]');
  const $categoryEmpty = $card.find('[data-product-mix-category-empty]');
  const $performanceRangeLabel = $('[data-performance-range-label]');
  const $statsGrid = $card.find('[data-product-mix-stats]');
  const borderColor = (window.config && window.config.colors && window.config.colors.borderColor) || '#ebe9f1';

  let lastUpdatedAt = Date.now();
  let timerId = null;
  let fetching = false;
  let selectedPeriod = 'today';
  let customStart = '';
  let customEnd = '';
  let topProductsChart = null;
  let categorySalesChart = null;
  let performanceChart = null;

  function closeRangeDropdown(el) {
    const dd = el && el.closest ? el.closest('.dropdown') : null;
    const toggle = dd && dd.querySelector('[data-bs-toggle="dropdown"]');
    if (toggle && window.bootstrap && bootstrap.Dropdown) {
      bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
    }
  }

  function markActivePeriod(period) {
    $rangeRoot.find('[data-dashboard-period]').each(function () {
      $(this).toggleClass('active', $(this).data('dashboard-period') === period);
    });
  }

  function updateRangeLabels(label, period) {
    const text = label || 'Today';
    const toggleText = period === 'custom' ? 'Custom range' : text;

    $card.find('[data-dashboard-range-label]').text(text);
    $rangeRoot.find('[data-dashboard-range-toggle-label]').text(toggleText);
    $performanceRangeLabel.text(text);
  }

  function requestParams() {
    const params = { period: selectedPeriod };
    if (selectedPeriod === 'custom') {
      params.start = customStart;
      params.end = customEnd;
    }
    return params;
  }

  function formatRelativeTime(totalSeconds) {
    const seconds = Math.max(1, totalSeconds);

    if (seconds < 60) {
      return seconds + (seconds === 1 ? ' second ago' : ' seconds ago');
    }

    if (seconds < 3600) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return mins + ' min ' + String(secs).padStart(2, '0') + ' sec ago';
    }

    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return hrs + ' hr ' + mins + ' min ' + String(secs).padStart(2, '0') + ' sec ago';
  }

  function tick() {
    const elapsed = Math.floor((Date.now() - lastUpdatedAt) / 1000);
    $timeEl.text(formatRelativeTime(elapsed));
  }

  function startTimer() {
    if (timerId) window.clearInterval(timerId);
    tick();
    timerId = window.setInterval(tick, 1000);
  }

  function resetTimestamp() {
    lastUpdatedAt = Date.now();
    tick();
  }

  function escapeHtml(value) {
    return $('<div>').text(value).html();
  }

  function chartOpts(extra) {
    return Object.assign({
      currencySymbol: currencySymbol,
      palette: palette,
      borderColor: borderColor,
      floorTooltip: true
    }, extra || {});
  }

  function renderTopProductsChart(products) {
    if (!window.PosSalesMixCharts) return;
    PosSalesMixCharts.destroy(topProductsChart);
    topProductsChart = PosSalesMixCharts.renderTopProducts(
      'employeeTopProductsChart',
      products,
      chartOpts({
        height: Math.max(220, Math.min(300, 48 + products.length * 44)),
        labelMaxWidth: 120
      })
    );
  }

  function renderCategoryChart(categories) {
    if (!window.PosSalesMixCharts) return;
    PosSalesMixCharts.destroy(categorySalesChart);
    categorySalesChart = PosSalesMixCharts.renderCategorySales(
      'employeeCategorySalesChart',
      categories,
      chartOpts({ height: 260 })
    );
  }

  function buildPerformanceOptions(trend) {
    return {
      chart: {
        type: 'area',
        height: 220,
        parentHeightOffset: 0,
        toolbar: { show: false }
      },
      series: [
        { name: 'Sales', data: trend.sales || [] },
        { name: 'Estimates', data: trend.estimates || [] }
      ],
      stroke: { curve: 'smooth', width: 2.5 },
      fill: { type: 'gradient', opacity: [0.15, 0.1] },
      colors: ['#28c76f', '#ff9f43'],
      xaxis: {
        categories: trend.labels || [],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: function (val) {
            return currencySymbol + Number(val).toLocaleString(undefined, { minimumFractionDigits: 0 });
          }
        }
      },
      grid: { borderColor: '#e2e8f0', strokeDashArray: 5 },
      dataLabels: { enabled: false },
      tooltip: {
        shared: true,
        y: {
          formatter: function (val) {
            return currencySymbol + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 });
          }
        }
      }
    };
  }

  function renderPerformanceChart(trend) {
    if (typeof ApexCharts === 'undefined' || !trend) return;

    const el = document.getElementById(config.trendChartId || 'employeePerformanceChart');
    if (!el) return;

    if (performanceChart) {
      performanceChart.destroy();
      performanceChart = null;
    }

    performanceChart = new ApexCharts(el, buildPerformanceOptions(trend));
    performanceChart.render();
  }

  function updateTopSection(products) {
    if (!products.length) {
      $topBody.prop('hidden', true);
      $topEmpty.prop('hidden', false);
      if (window.PosSalesMixCharts) PosSalesMixCharts.destroy(topProductsChart);
      topProductsChart = null;
      return;
    }

    $topBody.prop('hidden', false);
    $topEmpty.prop('hidden', true);
    renderTopProductsChart(products);
  }

  function updateCategorySection(categories) {
    if (!categories.length) {
      $categoryBody.prop('hidden', true);
      $categoryEmpty.prop('hidden', false);
      if (window.PosSalesMixCharts) PosSalesMixCharts.destroy(categorySalesChart);
      categorySalesChart = null;
      return;
    }

    $categoryBody.prop('hidden', false);
    $categoryEmpty.prop('hidden', true);
    renderCategoryChart(categories);
  }

  function updateSummaryCards(cards) {
    if (!Array.isArray(cards) || !cards.length) {
      return;
    }

    const html = cards.map(function (card) {
      return (
        '<div class="pos-glass-card pos-tone-' + escapeHtml(card.tone || 'primary') + ' h-100" data-product-mix-card="' + escapeHtml(card.key) + '">' +
          '<div class="pos-stat-body">' +
            '<div class="pos-stat-head">' +
              '<span class="pos-stat-icon"><i class="icon-base ti ' + escapeHtml(card.icon || 'tabler-chart-bar') + '" aria-hidden="true"></i></span>' +
              '<h6 class="pos-stat-label">' + escapeHtml(card.label || '') + '</h6>' +
            '</div>' +
            '<p class="pos-stat-value" data-product-mix-value="' + escapeHtml(card.key) + '" data-product-mix-format="' + escapeHtml(card.format || 'number') + '">' + escapeHtml(card.value || '0') + '</p>' +
            '<p class="pos-stat-desc mb-0" data-product-mix-meta="' + escapeHtml(card.key) + '">' + escapeHtml(card.meta || '') + '</p>' +
          '</div>' +
        '</div>'
      );
    }).join('');

    $statsGrid
      .attr('class', 'preview-stats-grid pos-ed-kpis pos-ed-kpis--' + Math.max(1, Math.min(4, cards.length)))
      .html(html);
  }

  function applyDashboardData(data) {
    if (!data || typeof data !== 'object') {
      return;
    }

    if (data.period) {
      selectedPeriod = data.period;
      markActivePeriod(selectedPeriod);
    }

    if (data.dashboard_range) {
      customStart = data.dashboard_range.start || customStart;
      customEnd = data.dashboard_range.end || customEnd;
    }

    if (data.period_label) {
      updateRangeLabels(data.period_label, data.period || selectedPeriod);
    }

    updateSummaryCards(data.summary_cards);
    updateTopSection(Array.isArray(data.top_products) ? data.top_products : []);
    updateCategorySection(Array.isArray(data.sales_by_category) ? data.sales_by_category : []);

    if (data.trend) {
      renderPerformanceChart(data.trend);
    }
  }

  function refresh() {
    if (fetching) return;

    fetching = true;
    resetTimestamp();
    $refreshBtn.prop('disabled', true).addClass('is-refreshing');
    $rangeRoot.find('[data-dashboard-range-toggle]').prop('disabled', true);
    $card.addClass('is-dashboard-refreshing');
    $statsGrid.addClass('is-loading');

    $.ajax({
      url: config.productMixUrl,
      method: 'GET',
      dataType: 'json',
      cache: false,
      global: false,
      data: requestParams()
    })
      .done(function (data) {
        applyDashboardData(data);
        $statusDot.addClass('is-live');
      })
      .fail(function () {
        if (window.Notiflix && Notiflix.Notify) {
          Notiflix.Notify.failure('Could not refresh sales and product mix.');
        }
      })
      .always(function () {
        fetching = false;
        $refreshBtn.prop('disabled', false).removeClass('is-refreshing');
        $rangeRoot.find('[data-dashboard-range-toggle]').prop('disabled', false);
        $card.removeClass('is-dashboard-refreshing');
        $statsGrid.removeClass('is-loading');
      });
  }

  $refreshBtn.on('click', refresh);

  $rangeRoot.on('click', '[data-dashboard-period]', function (event) {
    event.preventDefault();
    selectedPeriod = String($(this).data('dashboard-period') || 'today');
    closeRangeDropdown(this);
    refresh();
  });

  $rangeRoot.on('submit', '[data-dashboard-custom]', function (event) {
    event.preventDefault();
    const form = this;
    selectedPeriod = 'custom';
    customStart = form.querySelector('[name="start"]') ? form.querySelector('[name="start"]').value : '';
    customEnd = form.querySelector('[name="end"]') ? form.querySelector('[name="end"]').value : '';
    closeRangeDropdown(form);
    refresh();
  });

  $rangeRoot.on('shown.bs.dropdown', '.dropdown', function () {
    $card.addClass('is-range-dropdown-open');
    if (window.AppDatepicker && typeof window.AppDatepicker.init === 'function') {
      window.AppDatepicker.init(this);
    }
  });

  $rangeRoot.on('hidden.bs.dropdown', '.dropdown', function () {
    $card.removeClass('is-range-dropdown-open');
  });

  $rangeRoot.on('hide.bs.dropdown', '.dropdown', function (event) {
    const clickTarget = event.clickEvent && event.clickEvent.target;
    if (clickTarget && clickTarget.closest && clickTarget.closest('.flatpickr-calendar')) {
      event.preventDefault();
    }
  });

  applyDashboardData(config.initialData || {});
  startTimer();
})(jQuery);
