/**
 * Tenant admin dashboard charts (ApexCharts).
 * Data is provided server-side via window.dashboardData.
 */
(function () {
  'use strict';

  if (typeof ApexCharts === 'undefined' || !window.dashboardData) {
    return;
  }

  var data = window.dashboardData;
  var sym = data.currencySymbol || '$';

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

  function money(n) {
    return sym + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function compact(n) {
    n = Number(n || 0);
    if (Math.abs(n) >= 1000) {
      return sym + (n / 1000).toFixed(1) + 'k';
    }
    return sym + n.toFixed(0);
  }
  function render(id, options) {
    var el = document.getElementById(id);
    if (!el) return;
    new ApexCharts(el, options).render();
  }

  // --- Sales-this-month sparkline ---
  render('salesMonthChart', {
    chart: { type: 'area', height: 90, sparkline: { enabled: true } },
    series: [{ name: 'Revenue', data: data.revenueTrend.revenue }],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', opacity: 0.25 },
    colors: [C.primary],
    tooltip: { y: { formatter: money } }
  });

  // --- Sales overview: revenue (area) + orders (line), dual axis ---
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

  // --- Donut helper ---
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

  // --- Orders by status ---
  donut(
    'ordersStatusChart',
    data.ordersByStatus.map(function (s) { return s.label; }),
    data.ordersByStatus.map(function (s) { return s.count; }),
    [C.success, C.warning, C.secondary],
    function (v) { return Math.round(v); },
    'Orders'
  );

  // --- Payment methods (by amount) ---
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

  // --- Customers by type ---
  donut(
    'customersTypeChart',
    data.customersByType.map(function (c) { return c.label; }),
    data.customersByType.map(function (c) { return c.count; }),
    [C.primary, C.info, C.warning],
    function (v) { return Math.round(v); },
    'Customers'
  );

  // --- Revenue breakdown ---
  var rb = data.revenueBreakdown;
  donut(
    'revenueBreakdownChart',
    ['Net Sales', 'Tax', 'Service Fees', 'Discounts'],
    [Math.max(0, rb.subtotal - rb.discount), rb.tax, rb.fees, rb.discount],
    [C.primary, C.info, C.success, C.danger],
    money,
    'Total'
  );

  // --- Top selling products (horizontal bar) ---
  if (data.topProducts.length) {
    render('topProductsChart', {
      chart: { type: 'bar', height: 340, toolbar: { show: false } },
      series: [{ name: 'Revenue', data: data.topProducts.map(function (p) { return p.revenue; }) }],
      plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '65%', distributed: true } },
      colors: palette,
      dataLabels: { enabled: true, formatter: compact, style: { fontSize: '11px' } },
      legend: { show: false },
      grid: { borderColor: cfg.borderColor, strokeDashArray: 6 },
      xaxis: { categories: data.topProducts.map(function (p) { return p.name; }), labels: { formatter: compact } },
      tooltip: { y: { formatter: money } }
    });
  }

  // --- Sales by category (column) ---
  if (data.salesByCategory.length) {
    render('categorySalesChart', {
      chart: { type: 'bar', height: 300, toolbar: { show: false } },
      series: [{ name: 'Revenue', data: data.salesByCategory.map(function (c) { return c.revenue; }) }],
      plotOptions: { bar: { horizontal: false, borderRadius: 6, columnWidth: '45%', distributed: true } },
      colors: palette,
      dataLabels: { enabled: false },
      legend: { show: false },
      grid: { borderColor: cfg.borderColor, strokeDashArray: 6 },
      xaxis: { categories: data.salesByCategory.map(function (c) { return c.name; }) },
      yaxis: { labels: { formatter: compact } },
      tooltip: { y: { formatter: money } }
    });
  }
})();
