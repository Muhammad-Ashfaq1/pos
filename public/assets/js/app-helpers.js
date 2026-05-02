'use strict';

(function (window, document, $) {
  const fallbackLoading = {
    standard: function () {},
    remove: function () {}
  };

  const getLoading = function () {
    return typeof window.Notiflix !== 'undefined' && window.Notiflix.Loading
      ? window.Notiflix.Loading
      : fallbackLoading;
  };

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
      $button
        .prop('disabled', true)
        .html(
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
          (loadingText || 'Processing...')
        );
      return;
    }

    $button.prop('disabled', false).html(originalHtml);
  };

  const showLoading = function (message) {
    getLoading().standard(message || 'Please wait...', {
      backgroundColor: 'rgba(255, 255, 255, 0.85)',
      svgColor: '#7367f0',
      messageColor: '#5d596c',
      clickToClose: false
    });
  };

  const hideLoading = function (delay) {
    getLoading().remove(delay || 0);
  };

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
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
        '<span>' + (text || 'Loading...') + '</span>' +
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

  const helpers = ensureHelpers();
  helpers.addLoaderToModalHeader = addLoaderToModalHeader;
  helpers.removeLoaderFromModalHeader = removeLoaderFromModalHeader;
  helpers.setButtonLoading = setButtonLoading;
  helpers.showAppLoading = showLoading;
  helpers.hideAppLoading = hideLoading;
  helpers.makeModalsStatic = makeModalsStatic;
  helpers.initToolTip = initToolTip;
  helpers.getTooltipAttributes = getTooltipAttributes;

  document.addEventListener('DOMContentLoaded', function () {
    makeModalsStatic(document);

    if (typeof window.Swal !== 'undefined' && typeof window.Swal.mixin === 'function') {
      window.Swal = window.Swal.mixin({
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }

    if (typeof window.Notiflix !== 'undefined' && window.Notiflix.Loading) {
      window.Notiflix.Loading.init({
        className: 'notiflix-loading',
        zindex: 20000,
        clickToClose: false,
        svgColor: '#7367f0',
        messageColor: '#5d596c',
        backgroundColor: 'rgba(255, 255, 255, 0.85)'
      });
    }

    initMutationObserver();
  });
})(window, document, window.jQuery);
