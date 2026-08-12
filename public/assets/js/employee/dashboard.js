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
  const palette = ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE'];

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
  const $topList = $card.find('[data-product-mix-top-list]');
  const $topEmpty = $card.find('[data-product-mix-top-products-empty]');
  const $categoryBody = $card.find('[data-product-mix-category-body]');
  const $categoryList = $card.find('[data-product-mix-category-list]');
  const $categoryEmpty = $card.find('[data-product-mix-category-empty]');

  let lastUpdatedAt = Date.now();
  let timerId = null;
  let fetching = false;
  let selectedPeriod = String($periodSelect.val() || 'today');
  let categoryMixChart = null;

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

  function money(value) {
    return currencySymbol + Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatValue(field, value, format) {
    if (format === 'money' || field === 'total_sales') {
      return money(value);
    }

    return Number(value || 0).toLocaleString();
  }

  function destroyChart(chart) {
    if (chart && typeof chart.destroy === 'function') {
      chart.destroy();
    }
  }

  function escapeHtml(value) {
    return $('<div>').text(value).html();
  }

  function renderCategoryChart(categories) {
    const el = document.getElementById('employeeCategoryMixChart');

    if (!el || typeof ApexCharts === 'undefined') {
      return;
    }

    destroyChart(categoryMixChart);
    categoryMixChart = null;

    if (!categories.length) {
      return;
    }

    categoryMixChart = new ApexCharts(el, {
      chart: {
        type: 'donut',
        height: 168,
        parentHeightOffset: 0,
        toolbar: { show: false }
      },
      series: categories.map(function (category) {
        return category.revenue;
      }),
      labels: categories.map(function (category) {
        return category.name;
      }),
      colors: palette,
      legend: { show: false },
      dataLabels: { enabled: false },
      stroke: { width: 2, colors: ['rgba(255,255,255,0.65)'] },
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              name: { show: false },
              value: { show: false },
              total: {
                show: true,
                label: 'Total',
                fontSize: '11px',
                fontWeight: 600,
                color: '#6f6b7d',
                formatter: function (w) {
                  const total = w.globals.seriesTotals.reduce(function (sum, val) {
                    return sum + val;
                  }, 0);

                  return money(total);
                }
              }
            }
          }
        }
      },
      tooltip: {
        y: { formatter: money }
      }
    });

    categoryMixChart.render();
  }

  function buildTopProductsHtml(products) {
    const maxRevenue = products.reduce(function (max, product) {
      return Math.max(max, Number(product.revenue || 0));
    }, 0) || 1;

    return products.map(function (product, index) {
      const pct = Math.round((Number(product.revenue || 0) / maxRevenue) * 100);

      return (
        '<li class="product-mix-rank-item">' +
          '<span class="product-mix-rank-no">' + (index + 1) + '</span>' +
          '<div class="product-mix-rank-body">' +
            '<div class="product-mix-rank-row">' +
              '<span class="product-mix-rank-name">' + escapeHtml(product.name) + '</span>' +
              '<span class="product-mix-rank-meta">' +
                Number(product.qty || 0).toLocaleString() + ' · ' + money(product.revenue) +
              '</span>' +
            '</div>' +
            '<span class="product-mix-rank-bar" style="--mix-pct:' + pct + '"></span>' +
          '</div>' +
        '</li>'
      );
    }).join('');
  }

  function buildCategoryListHtml(categories) {
    const total = categories.reduce(function (sum, category) {
      return sum + Number(category.revenue || 0);
    }, 0) || 1;

    return categories.map(function (category) {
      const sharePct = Math.round((Number(category.revenue || 0) / total) * 100);

      return (
        '<li class="product-mix-rank-item product-mix-rank-item--compact">' +
          '<div class="product-mix-rank-body">' +
            '<div class="product-mix-rank-row">' +
              '<span class="product-mix-rank-name">' + escapeHtml(category.name) + '</span>' +
              '<span class="product-mix-rank-meta">' + money(category.revenue) + ' · ' + sharePct + '%</span>' +
            '</div>' +
            '<span class="product-mix-rank-bar product-mix-rank-bar--info" style="--mix-pct:' + sharePct + '"></span>' +
          '</div>' +
        '</li>'
      );
    }).join('');
  }

  function updateTopList(products) {
    if (!products.length) {
      $topList.empty().prop('hidden', true);
      $topEmpty.prop('hidden', false);
      return;
    }

    $topList.html(buildTopProductsHtml(products)).prop('hidden', false);
    $topEmpty.prop('hidden', true);
  }

  function updateCategorySection(categories) {
    if (!categories.length) {
      $categoryBody.prop('hidden', true);
      $categoryEmpty.prop('hidden', false);
      destroyChart(categoryMixChart);
      categoryMixChart = null;
      return;
    }

    $categoryList.html(buildCategoryListHtml(categories));
    $categoryBody.prop('hidden', false);
    $categoryEmpty.prop('hidden', true);
    renderCategoryChart(categories);
  }

  function updateMixCharts(data) {
    const products = Array.isArray(data.top_products) ? data.top_products : [];
    const categories = Array.isArray(data.sales_by_category) ? data.sales_by_category : [];

    updateTopList(products);
    updateCategorySection(categories);
  }

  function updateCards(data) {
    const fields = [
      'total_sales',
      'orders_completed',
      'orders_incomplete',
      'items_sold'
    ];

    fields.forEach(function (field) {
      const $valueEl = $card.find('[data-product-mix-value="' + field + '"]');

      if (data[field] !== undefined && $valueEl.length) {
        const format = $valueEl.attr('data-product-mix-format') || 'number';

        $valueEl.text(formatValue(field, data[field], format));
      }

      if (data.meta && data.meta[field]) {
        $card.find('[data-product-mix-meta="' + field + '"]').text(data.meta[field]);
      }
    });

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
