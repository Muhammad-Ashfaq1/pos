/**
 * Application loading helpers.
 *
 * Full-screen overlay loading has been removed app-wide. Use inline() or
 * button() for localized spinners (e.g. inside a button or table row).
 */
(function (window) {
  'use strict';

  function buildSpinnerMarkup(sizeClass) {
    var spokes = '';
    for (var i = 0; i < 12; i++) {
      spokes += '<span class="spoke" style="--angle:' + (i * 30) + 'deg;--delay:' + (i / 12).toFixed(3) + 's"></span>';
    }

    return '<span class="app-loader-spinner ' + (sizeClass || '') + '">' + spokes + '</span>';
  }

  function buildInlineMarkup(message, sizeClass, extraClass) {
    return '<span class="' + (extraClass || 'app-loader-inline-block') + '">' +
      buildSpinnerMarkup(sizeClass || 'app-loader-spinner-sm') +
      '<span class="app-loader-message">' + (message || 'Loading...') + '</span>' +
      '</span>';
  }

  function noop() {}

  function inline(message, size) {
    return buildInlineMarkup(message, size || 'app-loader-spinner-sm');
  }

  function button(message) {
    return buildInlineMarkup(message, 'app-loader-spinner-sm', 'app-loader-button-label');
  }

  function wrap(thenable) {
    return thenable;
  }

  var AppLoader = {
    show: noop,
    hide: noop,
    wrap: wrap,
    inline: inline,
    button: button
  };

  window.AppLoader = AppLoader;

  window.appLoading = window.appLoading || {};
  window.appLoading.show = noop;
  window.appLoading.hide = noop;
})(window);
