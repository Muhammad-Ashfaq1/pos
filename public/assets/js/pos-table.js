/* ============================================================================
 * pos-table.js — shared pagination engine for server-paginated tables
 * ----------------------------------------------------------------------------
 * Every paginated table in the app used to page the same way: the paginator
 * rendered <a href="?page=2">, the browser (or Turbo) navigated to it, and the
 * whole page came back. That is where both reported problems came from —
 *
 *   • the address bar filled up with page / per_page / filter values, and
 *   • the new document started at the top, so changing page threw the user
 *     away from the table they were reading.
 *
 * This script intercepts those links instead. It fetches the same URL over
 * XHR, lifts the matching table out of the response, and swaps ONLY that
 * table's markup in. Nothing else on the page is touched: no navigation, no
 * history entry, no scroll, no re-render of modals, drawers, detail panes or
 * any other table on the same page.
 *
 * Markup contract — produced by <x-pos-table> (resources/views/components):
 *
 *   [data-pos-table]            the region; carries id, scope, route, state
 *   [data-pos-table-viewport]   the only part that is ever replaced
 *   .pagination a               intercepted (Laravel's paginator markup)
 *   [data-pos-table-link]       intercepted (a filter rendered as a link)
 *   [data-pos-table-form]       intercepted on submit AND on change
 *
 * Anything else inside the region — row actions, View buttons, mailto: links,
 * POST forms — is deliberately NOT intercepted and behaves exactly as before.
 *
 * State (page, rows-per-page, whichever filters the table declares) is kept in
 * sessionStorage, never in the URL, keyed by viewer + route + table id. Two
 * tables on one page therefore have two independent keys, and one table's
 * paging can never disturb the other.
 *
 * Progressive enhancement: with this script absent or failing, every link in
 * the region is still an ordinary GET link and the table still works.
 * ==========================================================================*/
(function () {
    'use strict';

    if (window.__posTableBound) return;
    window.__posTableBound = true;

    var STORE_PREFIX = 'pos.table.v1:';
    var DT_STORE_PREFIX = 'pos.dt.v1:';

    /* ---------------------------------------------------------------- utils */

    /**
     * The region a control drives.
     *
     * Normally the one it sits inside. A control that has to live elsewhere on
     * the page — a filter in the page header, say — names its table instead:
     * data-pos-table-form="admin-usage-events".
     */
    function regionOf(el, explicitId) {
        if (explicitId) {
            var byId = null;
            Array.prototype.slice.call(document.querySelectorAll('[data-pos-table]')).some(function (region) {
                if (region.dataset.posTableId === explicitId) { byId = region; return true; }

                return false;
            });

            return byId;
        }

        return el && el.closest ? el.closest('[data-pos-table]') : null;
    }

    function viewportOf(region) {
        return region ? region.querySelector('[data-pos-table-viewport]') : null;
    }

    /**
     * The table's storage key.
     *
     * Scope comes from the server as an opaque hash of user + tenant (see
     * App\Support\TableFragment), so switching account — or being impersonated
     * — lands on a different bucket without a user id ever being written to
     * browser storage.
     */
    function storageKey(region) {
        return STORE_PREFIX + (region.dataset.posTableScope || 'anon') +
            ':' + (region.dataset.posTableRoute || '') +
            ':' + (region.dataset.posTableId || '');
    }

    /** The state the server says this region is currently rendering. */
    function currentState(region) {
        try {
            return JSON.parse(region.dataset.posTableState || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    /**
     * Whitelist. The keys of the rendered state ARE the permitted keys, so a
     * table only ever stores (and only ever replays) what its own view chose to
     * declare — never a stray id, token or cursor that happened to be on a URL.
     */
    function permittedKeys(region) {
        return Object.keys(currentState(region));
    }

    function readStored(region) {
        try {
            var raw = window.sessionStorage.getItem(storageKey(region));

            return raw ? (JSON.parse(raw) || {}) : null;
        } catch (e) {
            return null;
        }
    }

    function writeStored(region, state) {
        try {
            window.sessionStorage.setItem(storageKey(region), JSON.stringify(state));
        } catch (e) { /* private mode / quota — paging still works, it just won't be remembered */ }
    }

    /** Same state, ignoring key order and value types (1 vs "1"). */
    function sameState(a, b) {
        var keys = Object.keys(a || {}).concat(Object.keys(b || {}))
            .filter(function (k, i, all) { return all.indexOf(k) === i; });

        return keys.every(function (k) {
            return String((a || {})[k] == null ? '' : (a || {})[k]) ===
                String((b || {})[k] == null ? '' : (b || {})[k]);
        });
    }

    /** Pull the permitted keys out of a URL, so storage mirrors what was asked for. */
    function stateFromUrl(region, url) {
        var out = {};
        var allowed = permittedKeys(region);

        try {
            new URL(url, window.location.origin).searchParams.forEach(function (value, key) {
                if (allowed.indexOf(key) !== -1) out[key] = value;
            });
        } catch (e) { /* unparsable URL — fall through with what we have */ }

        return out;
    }

    function urlForState(region, state) {
        var base = region.dataset.posTableEndpoint || window.location.pathname;
        var url;

        try {
            url = new URL(base, window.location.origin);
        } catch (e) {
            return null;
        }

        Object.keys(state || {}).forEach(function (key) {
            var value = state[key];
            if (value === null || value === undefined || value === '') return;
            url.searchParams.set(key, value);
        });

        return url.pathname + (url.search || '');
    }

    /* -------------------------------------------------------------- loading */

    /**
     * Remember enough about the focused control to put focus back on its twin
     * after the swap. Keyboard users paging with Enter should stay on the
     * pagination, not be dumped at the top of the document.
     */
    function captureFocus(region) {
        var active = document.activeElement;
        if (!active || !region.contains(active)) return null;

        return {
            label: active.getAttribute('aria-label'),
            name: active.getAttribute('name'),
            rel: active.getAttribute('rel'),
            text: (active.textContent || '').trim(),
            tag: active.tagName,
        };
    }

    function restoreFocus(region, mark) {
        if (!mark) return;

        var candidates = Array.prototype.slice.call(
            region.querySelectorAll('a[href], button, input, select, textarea')
        );

        var match = candidates.filter(function (el) {
            if (mark.label && el.getAttribute('aria-label') === mark.label) return true;
            if (mark.name && el.getAttribute('name') === mark.name) return true;

            return el.tagName === mark.tag && (el.textContent || '').trim() === mark.text;
        })[0];

        if (match) {
            try { match.focus({ preventScroll: true }); } catch (e) { match.focus(); }
        }
    }

    /**
     * Keep a related link outside the region honest.
     *
     * The Usage Ledger's "Export CSV" has to carry the same period/tenant the
     * table is showing, but it sits in the page header rather than in the
     * region. Marking it data-pos-table-sync="<table id>" re-points it after
     * every load. `page` is dropped: an export is the whole filtered set, not
     * the page on screen.
     */
    function syncLinks(region, url) {
        var id = region.dataset.posTableId || '';
        var params;

        try {
            params = new URL(url, window.location.origin).searchParams;
        } catch (e) {
            return;
        }

        Array.prototype.slice.call(document.querySelectorAll('[data-pos-table-sync="' + id + '"]')).forEach(function (link) {
            var href;

            try {
                href = new URL(link.getAttribute('href'), window.location.origin);
            } catch (e) {
                return;
            }

            params.forEach(function (value, key) {
                if (key === 'page') return;
                if (value === '') href.searchParams.delete(key);
                else href.searchParams.set(key, value);
            });

            href.searchParams.delete('page');
            link.setAttribute('href', href.pathname + (href.search || ''));
        });
    }

    function clearError(region) {
        var existing = region.querySelector('[data-pos-table-error]');
        if (existing) existing.remove();
    }

    /**
     * A failed page keeps the table it already had.
     *
     * The rows on screen are still valid data, so they stay; the error is an
     * inline strip above them with a Retry that repeats the exact request. The
     * stored state is NOT updated — a page that never loaded must not become
     * the page we restore on the next visit.
     */
    function showError(region, url) {
        clearError(region);

        var box = document.createElement('div');
        box.className = 'pos-table-error';
        box.setAttribute('data-pos-table-error', '');
        box.setAttribute('role', 'alert');
        box.innerHTML =
            '<span class="pos-table-error-text">That page could not be loaded. The table below is unchanged.</span>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" data-pos-table-retry>Retry</button>';
        box.querySelector('[data-pos-table-retry]').dataset.posTableUrl = url;

        region.insertBefore(box, region.firstChild);
    }

    /**
     * Fetch `url` and swap in the table it contains.
     *
     * Concurrency: the previous request for THIS region is aborted and a
     * sequence number is bumped, so when someone clicks through pages faster
     * than the server answers, only the page they asked for last is rendered —
     * an earlier response arriving late is dropped rather than painted.
     */
    function load(region, url) {
        if (!region || !url) return;

        // A second click on the page already being fetched is a no-op. Clicking
        // a DIFFERENT page is not — that one supersedes the request in flight.
        if (region.__posPendingUrl === url) return;

        var viewport = viewportOf(region);
        if (!viewport) return;

        if (region.__posAbort) {
            try { region.__posAbort.abort(); } catch (e) { /* already settled */ }
        }

        var controller = typeof AbortController === 'function' ? new AbortController() : null;
        var seq = (region.__posSeq = (region.__posSeq || 0) + 1);

        region.__posAbort = controller;
        region.__posPendingUrl = url;

        // Hold the height the table already has, so swapping a full page for a
        // short last page cannot make the document jump under the cursor.
        viewport.style.minHeight = viewport.offsetHeight + 'px';
        region.setAttribute('data-pos-table-busy', '');
        region.setAttribute('aria-busy', 'true');
        clearError(region);

        var mark = captureFocus(region);
        var scrollY = window.scrollY;

        var settle = function () {
            if (seq !== region.__posSeq) return;
            region.__posPendingUrl = null;
            region.__posAbort = null;
            region.removeAttribute('data-pos-table-busy');
            region.removeAttribute('aria-busy');
            viewport.style.minHeight = '';
        };

        window.fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-POS-Table': region.dataset.posTableId || '',
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/xhtml+xml',
            },
            signal: controller ? controller.signal : undefined,
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);

            return response.text();
        }).then(function (html) {
            // A stale response — the user has already asked for another page.
            if (seq !== region.__posSeq) return;

            var doc = new DOMParser().parseFromString(html, 'text/html');
            var fresh = null;
            var id = region.dataset.posTableId || '';

            // Works against a fragment response AND against a full page, which
            // is what makes the server-side optimisation optional.
            Array.prototype.slice.call(doc.querySelectorAll('[data-pos-table]')).some(function (el) {
                if (el.dataset.posTableId === id) { fresh = el; return true; }

                return false;
            });

            var freshViewport = fresh ? fresh.querySelector('[data-pos-table-viewport]') : null;
            if (!freshViewport) throw new Error('table fragment missing');

            viewport.innerHTML = freshViewport.innerHTML;
            region.dataset.posTableState = fresh.dataset.posTableState || region.dataset.posTableState;

            writeStored(region, stateFromUrl(region, url));
            syncLinks(region, url);
            restoreFocus(region, mark);

            // Nothing here scrolls; this only undoes a clamp the browser may
            // have applied while the swapped content was momentarily shorter.
            if (window.scrollY !== scrollY) window.scrollTo(window.scrollX, scrollY);

            region.dispatchEvent(new CustomEvent('pos:table:load', {
                bubbles: true,
                detail: { id: id, url: url, state: currentState(region) },
            }));
        }).catch(function (error) {
            if (error && error.name === 'AbortError') return;
            if (seq !== region.__posSeq) return;

            showError(region, url);
        }).then(settle, settle);
    }

    /* --------------------------------------------------------------- events */

    /**
     * Which clicks page the table.
     *
     * Deliberately narrow: the paginator's own links, and anything a view has
     * explicitly marked. A row's View / Restore / mailto: link is left alone,
     * because it navigates somewhere real.
     */
    function tableLinkFor(target) {
        var retry = target.closest('[data-pos-table-retry]');
        if (retry) return { el: retry, url: retry.dataset.posTableUrl };

        var link = target.closest('a[href]');
        if (!link) return null;
        if (!link.closest('.pagination') && !link.matches('[data-pos-table-link]')) return null;

        var href = link.getAttribute('href');
        if (!href || href === '#' || href.charAt(0) === '#') return null;

        return { el: link, url: href };
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var hit = tableLinkFor(e.target);
        if (!hit || !hit.url) return;

        var region = regionOf(hit.el);
        if (!region) return;

        // preventDefault() before anything else: it is also what tells Turbo to
        // keep its hands off this click on the pages where Turbo Drive is on.
        e.preventDefault();
        load(region, hit.url);
    });

    /**
     * Rows-per-page and filter forms.
     *
     * These are GET forms that used to submit and reload the page. Serialising
     * them here keeps every value they carry (per_page plus whatever hidden
     * filters the view renders) while the address bar stays untouched.
     */
    function submitForm(form) {
        var region = regionOf(form, form.dataset.posTableForm);
        if (!region) return;

        var action = form.getAttribute('action') || region.dataset.posTableEndpoint || window.location.pathname;
        var query = new URLSearchParams(new FormData(form)).toString();

        load(region, action.split('?')[0] + (query ? '?' + query : ''));
    }

    document.addEventListener('submit', function (e) {
        var form = e.target.closest ? e.target.closest('form[data-pos-table-form]') : null;
        if (!form || !regionOf(form, form.dataset.posTableForm)) return;

        e.preventDefault();
        submitForm(form);
    });

    document.addEventListener('change', function (e) {
        var form = e.target.closest ? e.target.closest('form[data-pos-table-form]') : null;
        if (!form || !regionOf(form, form.dataset.posTableForm)) return;
        if (!form.hasAttribute('data-pos-table-auto')) return;

        submitForm(form);
    });

    /* -------------------------------------------------------------- restore */

    /**
     * Put a table back where the viewer left it.
     *
     * The URL is clean, so a refresh (or coming back to the route) renders page
     * one. If this tab remembers a different page for this exact table, it is
     * re-requested once, quietly. Tables whose remembered state matches what
     * the server already rendered cost nothing.
     */
    function restoreAll() {
        Array.prototype.slice.call(document.querySelectorAll('[data-pos-table]')).forEach(function (region) {
            if (region.__posRestored) return;
            region.__posRestored = true;

            var stored = readStored(region);
            if (!stored || sameState(stored, currentState(region))) return;

            // Only replay keys this table still declares — a filter that has
            // since been removed from the view must not be resurrected.
            var allowed = permittedKeys(region);
            var replay = {};
            Object.keys(stored).forEach(function (key) {
                if (allowed.indexOf(key) !== -1) replay[key] = stored[key];
            });

            if (!Object.keys(replay).length || sameState(replay, currentState(region))) return;

            var url = urlForState(region, replay);
            if (url) load(region, url);
        });
    }

    /* ------------------------------------------------- client-side DataTables */

    /**
     * The DataTables-backed tables (users, numbers, departments, …) page in the
     * browser: they never touched the URL and never scrolled, so they need none
     * of the above. The one thing they lacked is surviving a refresh, so they
     * get DataTables' OWN state store, pointed at sessionStorage and keyed the
     * same way as everything else here.
     *
     * Only layout state is kept — page, rows-per-page, sort. The search box and
     * per-column filters are stripped before saving, so nothing a viewer typed
     * (a phone number, a customer name) is written to browser storage.
     */
    function dtKey(settings) {
        var node = settings.nTable || {};
        var meta = document.querySelector('meta[name="pos-table-scope"]');

        return DT_STORE_PREFIX + ((meta && meta.content) || 'anon') +
            ':' + window.location.pathname + ':' + (node.id || 'table');
    }

    function patchDataTables() {
        var DT = window.DataTable ||
            (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable) || null;

        if (!DT || !DT.defaults || DT.defaults.__posStatePatched) return;

        DT.defaults.__posStatePatched = true;
        DT.defaults.stateSave = true;
        // -1 disables DataTables' own expiry check; the lifetime is the tab's.
        DT.defaults.stateDuration = -1;

        DT.defaults.stateSaveParams = function (settings, data) {
            delete data.search;
            if (data.columns) {
                data.columns.forEach(function (column) { delete column.search; });
            }
        };

        DT.defaults.stateSaveCallback = function (settings, data) {
            try {
                window.sessionStorage.setItem(dtKey(settings), JSON.stringify(data));
            } catch (e) { /* storage unavailable — the table still works */ }
        };

        DT.defaults.stateLoadCallback = function (settings) {
            try {
                return JSON.parse(window.sessionStorage.getItem(dtKey(settings)));
            } catch (e) {
                return null;
            }
        };
    }

    /* ----------------------------------------------------------------- boot */

    function init() {
        patchDataTables();
        restoreAll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Turbo replaces the body on in-app navigation, so the regions on screen
    // after a visit are new elements with no __posRestored marker of their own.
    document.addEventListener('turbo:load', init);
})();
