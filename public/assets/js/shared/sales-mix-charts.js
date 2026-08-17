/**
 * Shared Top Selling Products + Sales by Category ApexCharts.
 * Used by admin tenant dashboard and employee Product Mix.
 */
(function (global) {
  'use strict';

  var DEFAULT_PALETTE = ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE', '#826AF9', '#FFB400'];

  function resolveEl(target) {
    if (!target) return null;
    if (typeof target === 'string') return document.getElementById(target);
    return target.nodeType ? target : null;
  }

  function moneyFormatter(sym) {
    return function (n) {
      return sym + Number(n || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };
  }

  function compactFormatter(sym) {
    return function (n) {
      n = Number(n || 0);
      return Math.abs(n) >= 1000 ? sym + (n / 1000).toFixed(1) + 'k' : sym + n.toFixed(0);
    };
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function optionsFrom(opts) {
    opts = opts || {};
    var cfg = (global.config && global.config.colors) || {};
    var sym = opts.currencySymbol
      || (global.appCurrencySymbol && global.appCurrencySymbol())
      || (global.appCurrency && global.appCurrency.symbol)
      || '$';
    var palette = Array.isArray(opts.palette) && opts.palette.length ? opts.palette : DEFAULT_PALETTE;

    return {
      sym: sym,
      money: moneyFormatter(sym),
      compact: compactFormatter(sym),
      palette: palette,
      borderColor: opts.borderColor || cfg.borderColor || '#ebe9f1'
    };
  }

  function destroy(chart) {
    if (chart && typeof chart.destroy === 'function') {
      try { chart.destroy(); } catch (e) { /* ignore */ }
    }
  }

  /**
   * Floor-style tooltip without the colored marker dot.
   * rows: [{ label, value }, ...]
   */
  function tooltipHtml(title, rows) {
    var lines = (rows || []).map(function (row) {
      return (
        '<div class="apexcharts-tooltip-y-group">' +
          '<span class="apexcharts-tooltip-text-y-label">' + escapeHtml(row.label) + ': </span>' +
          '<span class="apexcharts-tooltip-text-y-value">' + escapeHtml(row.value) + '</span>' +
        '</div>'
      );
    }).join('');

    return (
      '<div class="apexcharts-tooltip-title" style="font-family: inherit; font-size: 12px;">' +
        escapeHtml(title) +
      '</div>' +
      '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order:1; display:flex;">' +
        '<div class="apexcharts-tooltip-text" style="font-family: inherit; font-size: 12px;">' +
          lines +
        '</div>' +
      '</div>'
    );
  }

  function renderTopProducts(target, products, opts) {
    if (typeof ApexCharts === 'undefined') return null;

    var el = resolveEl(target);
    if (!el) return null;

    products = Array.isArray(products) ? products : [];
    if (!products.length) {
      el.innerHTML = '';
      return null;
    }

    opts = opts || {};
    var o = optionsFrom(opts);
    var height = opts.height != null
      ? opts.height
      : Math.max(220, Math.min(340, 48 + products.length * 44));

    var chartOptions = {
      chart: { type: 'bar', height: height, parentHeightOffset: 0, toolbar: { show: false } },
      series: [{ name: opts.seriesName || 'Revenue', data: products.map(function (p) { return Number(p.revenue || 0); }) }],
      plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '65%', distributed: true } },
      colors: o.palette,
      dataLabels: { enabled: true, formatter: o.compact, style: { fontSize: '11px' } },
      legend: { show: false },
      grid: { borderColor: o.borderColor, strokeDashArray: 6 },
      xaxis: {
        categories: products.map(function (p) { return p.name; }),
        labels: { formatter: o.compact }
      },
      yaxis: {
        labels: {
          maxWidth: opts.labelMaxWidth != null ? opts.labelMaxWidth : 160,
          style: { fontSize: '12px' }
        }
      },
      tooltip: opts.tooltip || { y: { formatter: o.money } }
    };

    if (opts.floorTooltip) {
      chartOptions.series[0].name = 'Sales';
      chartOptions.tooltip = {
        custom: function (ctx) {
          var index = ctx.dataPointIndex;
          var product = products[index] || {};
          return tooltipHtml(product.name || '', [
            { label: 'Sales', value: o.money(product.revenue) },
            { label: 'Items Sold', value: Number(product.qty || 0).toLocaleString() }
          ]);
        }
      };
    }

    var chart = new ApexCharts(el, chartOptions);
    chart.render();
    return chart;
  }

  function renderCategorySales(target, categories, opts) {
    if (typeof ApexCharts === 'undefined') return null;

    var el = resolveEl(target);
    if (!el) return null;

    categories = Array.isArray(categories) ? categories : [];
    if (!categories.length) {
      el.innerHTML = '';
      return null;
    }

    opts = opts || {};
    var o = optionsFrom(opts);
    var height = opts.height != null ? opts.height : 300;
    var totalRevenue = categories.reduce(function (sum, category) {
      return sum + Number(category.revenue || 0);
    }, 0) || 1;

    var chartOptions = {
      chart: { type: 'bar', height: height, parentHeightOffset: 0, toolbar: { show: false } },
      series: [{ name: opts.seriesName || 'Revenue', data: categories.map(function (c) { return Number(c.revenue || 0); }) }],
      plotOptions: { bar: { horizontal: false, borderRadius: 6, columnWidth: '45%', distributed: true } },
      colors: o.palette,
      dataLabels: { enabled: false },
      legend: { show: false },
      grid: { borderColor: o.borderColor, strokeDashArray: 6 },
      xaxis: {
        categories: categories.map(function (c) { return c.name; }),
        labels: {
          rotate: categories.length > 4 ? -25 : 0,
          hideOverlappingLabels: true,
          trim: true,
          style: { fontSize: '11px' }
        }
      },
      yaxis: { labels: { formatter: o.compact } },
      tooltip: opts.tooltip || { y: { formatter: o.money } }
    };

    if (opts.floorTooltip) {
      chartOptions.series[0].name = 'Sales';
      chartOptions.tooltip = {
        custom: function (ctx) {
          var index = ctx.dataPointIndex;
          var category = categories[index] || {};
          var revenue = Number(category.revenue || 0);
          var sharePct = Math.round((revenue / totalRevenue) * 100);
          return tooltipHtml(category.name || '', [
            { label: 'Sales', value: o.money(revenue) },
            { label: 'Share', value: sharePct + '%' }
          ]);
        }
      };
    }

    var chart = new ApexCharts(el, chartOptions);
    chart.render();
    return chart;
  }

  global.PosSalesMixCharts = {
    defaultPalette: DEFAULT_PALETTE,
    destroy: destroy,
    tooltipHtml: tooltipHtml,
    renderTopProducts: renderTopProducts,
    renderCategorySales: renderCategorySales
  };
})(window);
