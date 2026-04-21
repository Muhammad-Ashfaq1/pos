'use strict';

(function () {
  const fallbackNotify = {
    success: function (message) { alert(message); },
    error: function (message) { alert(message); },
    info: function (message) { alert(message); },
    warning: function (message) { alert(message); }
  };

  const notifier = function () {
    return typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify
      ? window.Notiflix.Notify
      : fallbackNotify;
  };

  window.appNotify = function (type, message) {
    if (! message) {
      return;
    }

    const notify = notifier();
    const methodMap = {
      success: 'success',
      error: 'failure',
      warning: 'warning',
      info: 'info'
    };
    const method = methodMap[type] && typeof notify[methodMap[type]] === 'function'
      ? methodMap[type]
      : 'info';

    notify[method](message);
  };

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify) {
      window.Notiflix.Notify.init({
        width: '320px',
        position: 'right-top',
        distance: '20px',
        opacity: 1,
        borderRadius: '10px',
        timeout: 3500,
        success: {
          background: '#28c76f',
          textColor: '#ffffff'
        },
        failure: {
          background: '#ea5455',
          textColor: '#ffffff'
        },
        warning: {
          background: '#ff9f43',
          textColor: '#ffffff'
        },
        info: {
          background: '#00cfe8',
          textColor: '#ffffff'
        }
      });
    }

    if (! window.sessionMessages || typeof window.sessionMessages !== 'object') {
      return;
    }

    ['success', 'error', 'info', 'warning'].forEach(function (type) {
      if (window.sessionMessages[type]) {
        window.appNotify(type, window.sessionMessages[type]);
      }
    });

    if (window.sessionMessages.status) {
      window.appNotify('success', window.sessionMessages.status);
    }

    if (Array.isArray(window.sessionMessages.errors)) {
      window.sessionMessages.errors.forEach(function (message) {
        window.appNotify('error', message);
      });
    }
  });
})();
