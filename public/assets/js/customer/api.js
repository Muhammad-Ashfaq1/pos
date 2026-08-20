'use strict';

/**
 * Shared Customer API client (web portal + same contract as Flutter).
 * Axios is loaded from CDN in the customer-portal layout.
 */
(function (window, axios) {
  const config = window.customerApiConfig || {};
  const baseURL = config.baseUrl || '/api/v1/customer';
  const tokenUrl = config.tokenUrl || '/portal/api-token';

  let tokenPromise = null;
  let bearerToken = config.token || null;

  const client = axios.create({
    baseURL: baseURL,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  });

  client.interceptors.request.use(function (requestConfig) {
    if (bearerToken) {
      requestConfig.headers.Authorization = 'Bearer ' + bearerToken;
    }
    return requestConfig;
  });

  const ensureToken = function () {
    if (bearerToken) {
      return Promise.resolve(bearerToken);
    }

    if (tokenPromise) {
      return tokenPromise;
    }

    tokenPromise = axios
      .get(tokenUrl, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(function (response) {
        bearerToken = response.data && response.data.token ? response.data.token : null;
        window.customerApiConfig = window.customerApiConfig || {};
        window.customerApiConfig.token = bearerToken;
        return bearerToken;
      })
      .finally(function () {
        tokenPromise = null;
      });

    return tokenPromise;
  };

  const request = function (method, url, options) {
    options = options || {};

    return ensureToken().then(function () {
      return client.request({
        method: method,
        url: url,
        params: options.params || undefined,
        data: options.data || undefined
      }).then(function (response) {
        return response.data;
      });
    });
  };

  const escapeHtml = function (value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  const showError = function (error, fallback) {
    const message =
      (error && error.response && error.response.data && error.response.data.message) ||
      fallback ||
      'Unable to load data.';

    if (window.Notiflix && Notiflix.Notify) {
      Notiflix.Notify.failure(message);
    }

    return message;
  };

  const loadingHtml = function (text) {
    return '<div class="cp-list-empty"><div class="spinner-border text-primary mb-2" role="status"></div><p class="mb-0">' +
      escapeHtml(text || 'Loading...') +
      '</p></div>';
  };

  const emptyHtml = function (icon, text) {
    return '<div class="cp-list-empty"><i class="ti ' + escapeHtml(icon || 'tabler-clipboard-list') + '"></i><p class="mb-0">' +
      escapeHtml(text || 'Nothing here yet.') +
      '</p></div>';
  };

  const renderPagination = function ($footer, meta, onPage) {
    if (!$footer || !$footer.length || !meta || meta.last_page <= 1) {
      if ($footer && $footer.length) {
        $footer.addClass('d-none').empty();
      }
      return;
    }

    const current = meta.current_page;
    const last = meta.last_page;
    let html = '<nav><ul class="pagination pagination-sm mb-0 justify-content-center">';

    html += '<li class="page-item ' + (current <= 1 ? 'disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (current - 1) + '">Prev</a></li>';

    for (let page = 1; page <= last; page++) {
      if (last > 7 && Math.abs(page - current) > 2 && page !== 1 && page !== last) {
        if (page === 2 || page === last - 1) {
          html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        continue;
      }

      html += '<li class="page-item ' + (page === current ? 'active' : '') + '">' +
        '<a class="page-link" href="#" data-page="' + page + '">' + page + '</a></li>';
    }

    html += '<li class="page-item ' + (current >= last ? 'disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (current + 1) + '">Next</a></li>';
    html += '</ul></nav>';

    $footer.removeClass('d-none').html(html);
    $footer.off('click.cpPage').on('click.cpPage', 'a.page-link', function (event) {
      event.preventDefault();
      const page = Number($(this).data('page'));
      if (!page || page < 1 || page > last || page === current) {
        return;
      }
      onPage(page);
    });
  };

  window.CustomerApi = {
    get: function (url, params) {
      return request('get', url, { params: params });
    },
    patch: function (url, data) {
      return request('patch', url, { data: data });
    },
    post: function (url, data) {
      return request('post', url, { data: data });
    },
    ensureToken: ensureToken,
    escapeHtml: escapeHtml,
    showError: showError,
    loadingHtml: loadingHtml,
    emptyHtml: emptyHtml,
    renderPagination: renderPagination,
    routes: config.routes || {}
  };
})(window, window.axios);
