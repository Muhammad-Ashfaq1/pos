/**
 * Generic application loader.
 *
 * Public API (use anywhere):
 *   AppLoader.show('Saving...')   // show full-screen overlay (ref-counted)
 *   AppLoader.hide()              // hide when all callers have released
 *   AppLoader.wrap(promise, msg)  // show while a promise/thenable is pending
 *
 * Auto-wiring: every jQuery $.ajax and axios request shows the loader while in
 * flight (a short delay prevents flicker on fast calls). Opt a jQuery call out
 * with { global: false }; opt an axios call out with { headers: { 'X-No-Loader': '1' } }.
 */
(function (window, document) {
  'use strict';

  var SHOW_DELAY = 180; // ms — skip the overlay for very fast requests.
  var pending = 0;
  var showTimer = null;
  var overlay = null;
  var messageEl = null;

  function build() {
    if (overlay) return;
    overlay = document.createElement('div');
    overlay.id = 'app-loader-overlay';
    overlay.setAttribute('role', 'status');
    overlay.setAttribute('aria-live', 'polite');
    var spokes = '';
    for (var i = 0; i < 12; i++) {
      spokes += '<span class="spoke" style="--angle:' + (i * 30) + 'deg;--delay:' + (i / 12).toFixed(3) + 's"></span>';
    }
    overlay.innerHTML =
      '<div class="app-loader-spinner">' + spokes + '</div>' +
      '<div class="app-loader-message"></div>';
    document.body.appendChild(overlay);
    messageEl = overlay.querySelector('.app-loader-message');
  }

  function paint(message) {
    build();
    messageEl.textContent = message || 'Please wait…';
    overlay.classList.add('is-visible');
  }

  function show(message) {
    pending++;
    if (overlay && overlay.classList.contains('is-visible')) {
      if (message) messageEl.textContent = message;
      return;
    }
    if (showTimer) return;
    showTimer = window.setTimeout(function () {
      showTimer = null;
      if (pending > 0) paint(message);
    }, SHOW_DELAY);
  }

  function hide(force) {
    pending = force ? 0 : Math.max(0, pending - 1);
    if (pending > 0) return;
    if (showTimer) { window.clearTimeout(showTimer); showTimer = null; }
    if (overlay) overlay.classList.remove('is-visible');
  }

  function wrap(thenable, message) {
    show(message);
    if (thenable && typeof thenable.finally === 'function') {
      return thenable.finally(function () { hide(); });
    }
    if (thenable && typeof thenable.then === 'function') {
      return thenable.then(
        function (v) { hide(); return v; },
        function (e) { hide(); throw e; }
      );
    }
    hide();
    return thenable;
  }

  var AppLoader = { show: show, hide: hide, wrap: wrap };
  window.AppLoader = AppLoader;

  // Back the legacy helper with the generic loader so existing callers benefit.
  window.appLoading = window.appLoading || {};
  window.appLoading.show = function (msg) { show(msg); };
  window.appLoading.hide = function () { hide(true); };

  // --- Auto-wiring -----------------------------------------------------------

  function wireJquery($) {
    if (!$ || !$.fn) return;
    // ajaxStart/ajaxStop fire around the whole queue of "global" requests.
    $(document).on('ajaxStart', function () { show(); });
    $(document).on('ajaxStop', function () { hide(true); });
  }

  function wireAxios(axios) {
    if (!axios || !axios.interceptors) return;
    axios.interceptors.request.use(function (config) {
      if (!(config.headers && (config.headers['X-No-Loader'] || config.headers['x-no-loader']))) {
        config.__appLoader = true;
        show();
      }
      return config;
    }, function (error) { return Promise.reject(error); });

    var done = function (configOrResponse) {
      var config = configOrResponse && configOrResponse.config ? configOrResponse.config : configOrResponse;
      if (config && config.__appLoader) hide();
    };
    axios.interceptors.response.use(
      function (response) { done(response); return response; },
      function (error) { done(error && error.config ? error : (error && error.response)); return Promise.reject(error); }
    );
  }

  function init() {
    wireJquery(window.jQuery);
    wireAxios(window.axios);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);
