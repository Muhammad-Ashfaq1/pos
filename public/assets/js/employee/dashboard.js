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
  const $periodSelect = $card.find('[data-product-mix-period]');
  const $topBody = $card.find('[data-product-mix-top-body]');
  const $topEmpty = $card.find('[data-product-mix-top-products-empty]');
  const $categoryBody = $card.find('[data-product-mix-category-body]');
  const $categoryEmpty = $card.find('[data-product-mix-category-empty]');
  const borderColor = (window.config && window.config.colors && window.config.colors.borderColor) || '#ebe9f1';

  let lastUpdatedAt = Date.now();
  let timerId = null;
  let fetching = false;
  let selectedPeriod = String($periodSelect.val() || 'today');
  let topProductsChart = null;
  let categorySalesChart = null;

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
    if (timerId) {
      window.clearInterval(timerId);
    }

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
    if (!window.PosSalesMixCharts) {
      return;
    }

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
    if (!window.PosSalesMixCharts) {
      return;
    }

    PosSalesMixCharts.destroy(categorySalesChart);
    categorySalesChart = PosSalesMixCharts.renderCategorySales(
      'employeeCategorySalesChart',
      categories,
      chartOpts({ height: 260 })
    );
  }

  function updateTopSection(products) {
    if (!products.length) {
      $topBody.prop('hidden', true);
      $topEmpty.prop('hidden', false);
      if (window.PosSalesMixCharts) {
        PosSalesMixCharts.destroy(topProductsChart);
      }
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
      if (window.PosSalesMixCharts) {
        PosSalesMixCharts.destroy(categorySalesChart);
      }
      categorySalesChart = null;
      return;
    }

    $categoryBody.prop('hidden', false);
    $categoryEmpty.prop('hidden', true);
    renderCategoryChart(categories);
  }

  function updateMixCharts(data) {
    const products = Array.isArray(data.top_products) ? data.top_products : [];
    const categories = Array.isArray(data.sales_by_category) ? data.sales_by_category : [];

    updateTopSection(products);
    updateCategorySection(categories);
  }

  function updateCards(data) {
    if (Array.isArray(data.summary_cards) && data.summary_cards.length) {
      const $grid = $card.find('[data-product-mix-stats]');
      const html = data.summary_cards.map(function (card) {
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

      $grid
        .attr('class', 'preview-stats-grid pos-ed-kpis pos-ed-kpis--' + Math.max(1, Math.min(4, data.summary_cards.length)))
        .html(html);
    }

    if (data.period) {
      selectedPeriod = data.period;
      if ($periodSelect.val() !== data.period) {
        $periodSelect.val(data.period);
      }
    }

    updateMixCharts(data);
  }

  function readInitialMixData() {
    const node = document.getElementById('employeeProductMixData');

    if (!node) {
      return;
    }

    try {
      updateMixCharts(JSON.parse(node.textContent || '{}'));
    } catch (error) {
      updateMixCharts({});
    }
  }

  function refresh() {
    if (fetching) {
      return;
    }

    fetching = true;
    resetTimestamp();
    $refreshBtn.prop('disabled', true).addClass('is-refreshing');
    $periodSelect.prop('disabled', true);

    $.ajax({
      url: config.productMixUrl,
      method: 'GET',
      dataType: 'json',
      data: { period: selectedPeriod }
    })
      .done(function (data) {
        updateCards(data);
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
        $periodSelect.prop('disabled', false);
      });
  }

  $refreshBtn.on('click', refresh);

  $periodSelect.on('change', function () {
    selectedPeriod = String($(this).val() || 'today');
    refresh();
  });

  readInitialMixData();
  startTimer();
})(jQuery);
