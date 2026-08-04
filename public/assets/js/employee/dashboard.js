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

  let lastUpdatedAt = Date.now();
  let timerId = null;
  let fetching = false;
  let selectedPeriod = String($periodSelect.val() || 'today');

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

  function updateCards(data) {
    const fields = [
      'orders_completed_today',
      'orders_incomplete_today',
      'products_available'
    ];

    fields.forEach(function (field) {
      if (data[field] !== undefined) {
        $card.find('[data-product-mix-value="' + field + '"]').text(
          Number(data[field]).toLocaleString()
        );
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
          Notiflix.Notify.failure('Could not refresh product mix.');
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

  startTimer();
})(jQuery);
