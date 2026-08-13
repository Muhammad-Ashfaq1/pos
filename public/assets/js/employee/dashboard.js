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
    : ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE'];

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
  const $topChart = $card.find('[data-product-mix-top-chart]');
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

  function compactMoney(value) {
    const amount = Number(value || 0);

    if (Math.abs(amount) >= 1000) {
      return currencySymbol + (amount / 1000).toFixed(1) + 'k';
    }

    return currencySymbol + Math.round(amount);
  }

  function destroyChart(chart) {
    if (chart && typeof chart.destroy === 'function') {
      chart.destroy();
    }
  }

  function escapeHtml(value) {
    return $('<div>').text(value).html();
  }

  function renderTopProductsChart(products) {
    if (!$topChart.length) {
      return;
    }

    if (!products.length) {
      $topChart.empty();
      return;
    }

    const maxRevenue = products.reduce(function (max, product) {
      return Math.max(max, Number(product.revenue || 0));
    }, 0) || 1;

    $topChart.html(products.map(function (product, index) {
      const pct = Math.max(8, Math.round((Number(product.revenue || 0) / maxRevenue) * 100));
      const color = palette[index % palette.length];
      const name = escapeHtml(product.name);

      return (
        '<div class="product-mix-hbar-row" data-name="' + name + '" data-sales="' + escapeHtml(money(product.revenue)) + '" data-qty="' + Number(product.qty || 0).toLocaleString() + '" data-color="' + color + '">' +
          '<span class="product-mix-hbar-track">' +
            '<span class="product-mix-hbar-fill" style="width:' + pct + '%;background:' + color + '">' +
              '<span class="product-mix-hbar-value">' + escapeHtml(compactMoney(product.revenue)) + '</span>' +
            '</span>' +
          '</span>' +
        '</div>'
      );
    }).join(''));
  }

  function mixTooltipEl() {
    let el = document.getElementById('employeeMixTooltip');

    if (el) {
      return el;
    }

    el = document.createElement('div');
    el.id = 'employeeMixTooltip';
    el.className = 'apexcharts-tooltip apexcharts-theme-light product-mix-hbar-tooltip';
    document.body.appendChild(el);

    return el;
  }

  function showMixTooltip(event, title, color, rows, options) {
    const tip = mixTooltipEl();
    const seriesColor = color || '#5e5873';
    const showMarker = !(options && options.marker === false);
    const lines = (rows || []).map(function (row) {
      return (
        '<div class="apexcharts-tooltip-y-group">' +
          '<span class="apexcharts-tooltip-text-y-label" style="color:' + seriesColor + '">' + escapeHtml(row.label) + ': </span>' +
          '<span class="apexcharts-tooltip-text-y-value" style="color:' + seriesColor + '">' + escapeHtml(row.value) + '</span>' +
        '</div>'
      );
    }).join('');

    tip.innerHTML =
      '<div class="apexcharts-tooltip-title" style="color:' + seriesColor + '">' + escapeHtml(title) + '</div>' +
      '<div class="apexcharts-tooltip-series-group apexcharts-active" style="display:flex;">' +
        (showMarker ? '<span class="apexcharts-tooltip-marker" style="background:' + seriesColor + '"></span>' : '') +
        '<div class="apexcharts-tooltip-text">' + lines + '</div>' +
      '</div>';

    tip.classList.add('apexcharts-active');
    tip.style.left = (event.clientX + 14) + 'px';
    tip.style.top = (event.clientY + 14) + 'px';

    const rect = tip.getBoundingClientRect();
    const pad = 12;
    let left = event.clientX + 14;
    let top = event.clientY + 14;

    if (left + rect.width > window.innerWidth - pad) {
      left = event.clientX - rect.width - 12;
    }

    if (top + rect.height > window.innerHeight - pad) {
      top = event.clientY - rect.height - 8;
    }

    tip.style.left = Math.max(pad, left) + 'px';
    tip.style.top = Math.max(pad, top) + 'px';
  }

  function hideMixTooltip() {
    const tip = document.getElementById('employeeMixTooltip');

    if (tip) {
      tip.classList.remove('apexcharts-active');
    }
  }

  function bindBarTooltip() {
    $topChart.off('mousemove.pmtooltip mouseleave.pmtooltip');

    $topChart.on('mousemove.pmtooltip', '.product-mix-hbar-row', function (event) {
      const row = event.currentTarget;

      showMixTooltip(event, row.getAttribute('data-name'), row.getAttribute('data-color'), [
        { label: 'Sales', value: row.getAttribute('data-sales') },
        { label: 'Items Sold', value: row.getAttribute('data-qty') }
      ], { marker: false });
    });

    $topChart.on('mouseleave.pmtooltip', hideMixTooltip);
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

    const labels = categories.map(function (category) {
      return category.name;
    });
    const series = categories.map(function (category) {
      return Number(category.revenue || 0);
    });

    categoryMixChart = new ApexCharts(el, {
      chart: {
        type: 'donut',
        height: 230,
        parentHeightOffset: 0,
        toolbar: { show: false },
        events: {
          dataPointMouseEnter: function (_event, _chartContext, config) {
            const index = config.seriesIndex >= 0 ? config.seriesIndex : config.dataPointIndex;
            if (index >= 0) {
              el.style.setProperty('--mix-slice-color', palette[index % palette.length]);
            }
          },
          mouseMove: function (_event, _chartContext, config) {
            hideMixTooltip();
            const index = config.seriesIndex >= 0 ? config.seriesIndex : config.dataPointIndex;
            if (index >= 0) {
              el.style.setProperty('--mix-slice-color', palette[index % palette.length]);
            }
          }
        }
      },
      series: series,
      labels: labels,
      colors: palette,
      legend: { show: false },
      stroke: { width: 0 },
      dataLabels: {
        enabled: true,
        formatter: function (val) {
          return Math.round(val) + '%';
        },
        style: {
          fontSize: '12px',
          fontWeight: 600
        }
      },
      plotOptions: {
        pie: {
          dataLabels: {
            offset: -6,
            minAngleToShowLabel: 8
          },
          donut: {
            size: '58%',
            labels: {
              show: true,
              value: { formatter: money },
              total: {
                show: true,
                label: 'Total',
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
        enabled: true,
        fillSeriesColor: false,
        theme: 'light',
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
        '<li class="product-mix-rank-item product-mix-rank-item--compact">' +
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

  function updateTopSection(products) {
    if (!products.length) {
      $topBody.prop('hidden', true);
      $topEmpty.prop('hidden', false);
      $topChart.empty();
      return;
    }

    $topList.html(buildTopProductsHtml(products));
    $topBody.prop('hidden', false);
    $topEmpty.prop('hidden', true);
    renderTopProductsChart(products);
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
  bindBarTooltip();
  startTimer();
})(jQuery);
