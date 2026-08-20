'use strict';

(function (window, document, $) {
  const ensureHelpers = function () {
    window.Helpers = window.Helpers || {};
    return window.Helpers;
  };

  const resolveButton = function (button) {
    if (!button) {
      return null;
    }

    if ($ && button.jquery) {
      return button;
    }

    return $(button);
  };

  const defaultButtonHtml = function ($button) {
    const storedHtml = $button.data('default-html');

    if (storedHtml !== undefined) {
      return storedHtml;
    }

    const html = $button.html();
    $button.data('default-html', html);
    return html;
  };

  // Full-screen loading overlay removed app-wide; helpers only disable buttons.
  const setButtonLoading = function (button, isLoading, loadingText, defaultHtml) {
    if (!$) {
      return;
    }

    const $button = resolveButton(button);

    if (!$button || !$button.length) {
      return;
    }

    const originalHtml = defaultHtml || defaultButtonHtml($button);

    if (isLoading) {
      $button.prop('disabled', true);
      return;
    }

    $button.prop('disabled', false).html(originalHtml);
  };

  const showLoading = function () {};

  const hideLoading = function () {};

  const addLoaderToModalHeader = function (modalSelector, text) {
    if (!$) {
      return;
    }

    const $modal = $(modalSelector);

    if (!$modal.length) {
      return;
    }

    const $header = $modal.find('.modal-header').first();

    if (!$header.length || $header.find('.modal-header-loader').length) {
      return;
    }

    $header.append(
      '<div class="modal-header-loader d-flex align-items-center text-primary ms-3">' +
        (window.AppLoader && typeof window.AppLoader.inline === 'function'
          ? window.AppLoader.inline(text || 'Loading...', 'app-loader-spinner-sm')
          : '<span>' + (text || 'Loading...') + '</span>') +
      '</div>'
    );
  };

  const removeLoaderFromModalHeader = function (modalSelector) {
    if (!$) {
      return;
    }

    $(modalSelector).find('.modal-header-loader').remove();
  };

  const makeModalsStatic = function (root) {
    const scope = root || document;

    scope.querySelectorAll('.modal').forEach(function (modal) {
      if (modal.dataset.allowOutsideClose === 'true') {
        return;
      }

      modal.setAttribute('data-bs-backdrop', 'static');
      modal.setAttribute('data-bs-keyboard', 'false');
    });
  };

  const initMutationObserver = function () {
    if (typeof MutationObserver === 'undefined') {
      return;
    }

    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (!(node instanceof HTMLElement)) {
            return;
          }

          if (node.matches && node.matches('.modal')) {
            makeModalsStatic(node.parentNode || document);
            return;
          }

          if (node.querySelectorAll) {
            makeModalsStatic(node);
          }
        });
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  };

  window.appLoading = {
    show: showLoading,
    hide: hideLoading
  };

  window.appSetButtonLoading = setButtonLoading;

  const initToolTip = function (root) {
    if (typeof window.bootstrap === 'undefined' || !window.bootstrap.Tooltip) {
      return;
    }

    const scope = root && root.querySelectorAll ? root : document;
    const tooltipTriggerList = [].slice.call(scope.querySelectorAll('[data-bs-toggle="tooltip"]'));

    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      const existing = window.bootstrap.Tooltip.getInstance(tooltipTriggerEl);
      if (existing) {
        existing.dispose();
      }
      new window.bootstrap.Tooltip(tooltipTriggerEl);
    });
  };

  const getTooltipAttributes = function (title) {
    const safeTitle = String(title == null ? '' : title)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    return 'data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary" title="' + safeTitle + '"';
  };

  const slugify = function (value, maxLength) {
    const limit = typeof maxLength === 'number' ? maxLength : 170;

    return String(value || '')
      .normalize('NFKD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .substring(0, limit);
  };

  const helpers = ensureHelpers();
  helpers.addLoaderToModalHeader = addLoaderToModalHeader;
  helpers.removeLoaderFromModalHeader = removeLoaderFromModalHeader;
  helpers.setButtonLoading = setButtonLoading;
  helpers.showAppLoading = showLoading;
  helpers.hideAppLoading = hideLoading;
  helpers.makeModalsStatic = makeModalsStatic;
  helpers.initToolTip = initToolTip;
  helpers.getTooltipAttributes = getTooltipAttributes;
  helpers.slugify = slugify;
  window.appSlugify = slugify;

  /**
   * Active shop currency symbol from layouts (window.appCurrency).
   * Defaults to `$` when regional currency is unset.
   */
  const currencySymbol = function () {
    return (window.appCurrency && window.appCurrency.symbol) || '$';
  };

  const formatMoney = function (amount, decimals) {
    const places = typeof decimals === 'number' ? decimals : 2;
    const value = Number(amount);
    const safe = Number.isFinite(value) ? value : 0;

    return currencySymbol() + safe.toFixed(places);
  };

  const stripCurrency = function (text) {
    const symbol = currencySymbol();
    let raw = String(text == null ? '' : text);
    if (symbol) {
      raw = raw.split(symbol).join('');
    }
    // Also strip a bare `$` in case older markup still used it.
    return raw.replace(/\$/g, '').replace(/,/g, '').trim();
  };

  helpers.currencySymbol = currencySymbol;
  helpers.formatMoney = formatMoney;
  helpers.stripCurrency = stripCurrency;
  window.appCurrencySymbol = currencySymbol;
  window.appFormatMoney = formatMoney;
  window.appStripCurrency = stripCurrency;

  document.addEventListener('DOMContentLoaded', function () {
    makeModalsStatic(document);

    if (typeof window.Swal !== 'undefined' && typeof window.Swal.mixin === 'function') {
      window.Swal = window.Swal.mixin({
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }

    if (typeof window.Notiflix !== 'undefined' && window.Notiflix.Loading) {
      // Notifications remain handled by Notiflix; loading is centralized in AppLoader.
    }

    initMutationObserver();
  });
})(window, document, window.jQuery);
