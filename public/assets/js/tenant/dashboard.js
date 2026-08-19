/**
 * Tenant admin dashboard (ApexCharts) with an AJAX date-range filter.
 *
 * The chart dataset is embedded server-side as JSON in #dashboardData. Selecting
 * a filter fetches the dashboard for that range, swaps #dashboard-content in
 * place (no browser navigation / URL change) and re-renders the charts.
 */
(function () {
  'use strict';

  var charts = [];

  var cfg = (window.config && window.config.colors) || {};
  var C = {
    primary: cfg.primary || '#7367F0',
    success: cfg.success || '#28C76F',
    warning: cfg.warning || '#FF9F43',
    danger: cfg.danger || '#EA5455',
    info: cfg.info || '#00CFE8',
    secondary: cfg.secondary || '#A8AAAE'
  };
  var palette = [C.primary, C.success, C.warning, C.info, C.danger, C.secondary, '#826AF9', '#FFB400'];

  function readData() {
    var el = document.getElementById('dashboardData');
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (e) { return null; }
  }

  function destroyCharts() {
    charts.forEach(function (c) { try { c.destroy(); } catch (e) {} });
    charts = [];
  }

  function renderCharts(data) {
    if (typeof ApexCharts === 'undefined' || !data) return;

    var sym = data.currencySymbol || (window.appCurrencySymbol && window.appCurrencySymbol()) ||
        (window.appCurrency && window.appCurrency.symbol) ||
        '$';
    function money(n) {
      return sym + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function compact(n) {
      n = Number(n || 0);
      return Math.abs(n) >= 1000 ? sym + (n / 1000).toFixed(1) + 'k' : sym + n.toFixed(0);
    }
    function render(id, options) {
      var el = document.getElementById(id);
      if (!el) return;
      var chart = new ApexCharts(el, options);
      chart.render();
      charts.push(chart);
    }
    function donut(id, labels, series, colors, valueFormatter, totalLabel) {
      var nonZero = series.some(function (v) { return Number(v) > 0; });
      render(id, {
        chart: { type: 'donut', height: 300 },
        series: nonZero ? series : [],
        labels: labels,
        colors: colors,
        stroke: { width: 0 },
        dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
        legend: { position: 'bottom', markers: { radius: 12 } },
        noData: { text: 'No data yet' },
        plotOptions: {
          pie: {
            donut: {
              size: '72%',
              labels: {
                show: true,
                value: { formatter: valueFormatter },
                total: {
                  show: true,
                  label: totalLabel || 'Total',
                  formatter: function (w) {
                    var sum = w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                    return valueFormatter ? valueFormatter(sum) : sum;
                  }
                }
              }
            }
          }
        }
      });
    }

    render('salesMonthChart', {
      chart: { type: 'area', height: 90, sparkline: { enabled: true } },
      series: [{ name: 'Revenue', data: data.revenueTrend.revenue }],
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', opacity: 0.25 },
      colors: [C.primary],
      tooltip: { y: { formatter: money } }
    });

    render('salesOverviewChart', {
      chart: { type: 'line', height: 320, toolbar: { show: false }, parentHeightOffset: 0 },
      series: [
        { name: 'Revenue', type: 'area', data: data.revenueTrend.revenue },
        { name: 'Orders', type: 'line', data: data.revenueTrend.orders }
      ],
      stroke: { curve: 'smooth', width: [2, 3] },
      fill: { type: ['gradient', 'solid'], opacity: [0.25, 1] },
      colors: [C.primary, C.warning],
      dataLabels: { enabled: false },
      legend: { position: 'top', horizontalAlign: 'left' },
      grid: { borderColor: cfg.borderColor, strokeDashArray: 6 },
      xaxis: { categories: data.revenueTrend.labels, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: [
        { labels: { formatter: compact } },
        { opposite: true, labels: { formatter: function (v) { return Math.round(v); } } }
      ],
      tooltip: { shared: true, y: { formatter: function (v, o) { return o && o.seriesIndex === 0 ? money(v) : Math.round(v) + ' orders'; } } }
    });

    donut(
      'ordersStatusChart',
      data.ordersByStatus.map(function (s) { return s.label; }),
      data.ordersByStatus.map(function (s) { return s.count; }),
      [C.success, C.warning, C.secondary, C.info],
      function (v) { return Math.round(v); },
      'Orders'
    );

    if (data.paymentMethods.length) {
      donut(
        'paymentMethodsChart',
        data.paymentMethods.map(function (p) { return p.label; }),
        data.paymentMethods.map(function (p) { return p.amount; }),
        palette,
        money,
        'Collected'
      );
    }

    donut(
      'customersTypeChart',
      data.customersByType.map(function (c) { return c.label; }),
      data.customersByType.map(function (c) { return c.count; }),
      [C.primary, C.info, C.warning],
      function (v) { return Math.round(v); },
      'Customers'
    );

    var rb = data.revenueBreakdown;
    donut(
      'revenueBreakdownChart',
      ['Net Sales', 'Tax', 'Service Fees', 'Discounts'],
      [Math.max(0, rb.subtotal - rb.discount), rb.tax, rb.fees, rb.discount],
      [C.primary, C.info, C.success, C.danger],
      money,
      'Total'
    );

    if (window.PosSalesMixCharts) {
      if (data.topProducts.length) {
        var topChart = PosSalesMixCharts.renderTopProducts('topProductsChart', data.topProducts, {
          currencySymbol: sym,
          palette: palette,
          borderColor: cfg.borderColor,
          height: 340
        });
        if (topChart) charts.push(topChart);
      }

      if (data.salesByCategory.length) {
        var categoryChart = PosSalesMixCharts.renderCategorySales('categorySalesChart', data.salesByCategory, {
          currencySymbol: sym,
          palette: palette,
          borderColor: cfg.borderColor,
          height: 300
        });
        if (categoryChart) charts.push(categoryChart);
      }
    }
  }

  // --- AJAX range switching -------------------------------------------------

  function baseUrl() {
    var content = document.getElementById('dashboard-content');
    return (content && content.dataset.url) || window.location.pathname;
  }

  function loadRange(params) {
    var content = document.getElementById('dashboard-content');
    if (!content) return;

    var query = Object.keys(params)
      .filter(function (k) { return params[k] !== undefined && params[k] !== null && params[k] !== ''; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
      .join('&');

    fetch(baseUrl() + (query ? '?' + query : ''), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = doc.getElementById('dashboard-content');
        if (!fresh) { throw new Error('Malformed response'); }

        destroyCharts();
        content.innerHTML = fresh.innerHTML;
        renderCharts(readData());
      })
      .catch(function () {
        if (window.Notiflix && Notiflix.Notify) Notiflix.Notify.failure('Could not update the dashboard.');
      });
  }

  function closeDropdown(el) {
    var dd = el.closest('.dropdown');
    var toggle = dd && dd.querySelector('[data-bs-toggle="dropdown"]');
    if (toggle && window.bootstrap && bootstrap.Dropdown) {
      bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
    }
  }

  // Delegated on document so the handlers survive the content swap.
  // Presets apply immediately and close the menu — no "Apply" needed.
  document.addEventListener('click', function (e) {
    var item = e.target.closest('[data-period]');
    if (!item) return;
    e.preventDefault();
    closeDropdown(item);
    loadRange({ period: item.getAttribute('data-period') });
  });

  // The custom form's date inputs are required only for the custom range.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('[data-dashboard-custom]');
    if (!form) return;
    e.preventDefault();
    closeDropdown(form);
    loadRange({
      period: 'custom',
      start: form.querySelector('[name="start"]') ? form.querySelector('[name="start"]').value : '',
      end: form.querySelector('[name="end"]') ? form.querySelector('[name="end"]').value : ''
    });
  });

  function init() {
    renderCharts(readData());
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
