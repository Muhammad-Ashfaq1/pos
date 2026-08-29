/* ============================================================================
 * pos-workspace-fullscreen.js — admin order/invoice canvas toggle
 * ----------------------------------------------------------------------------
 * Shared order screens (orders, POS, details, returns, invoices) render inside
 * the tenant admin chrome (sidebar + navbar). The employee panel already uses
 * a full-width canvas; this script gives admins the same workspace without
 * swapping layouts. The existing sidebar stays the default; fullscreen hides
 * the menu and stretches the navbar full-width, matching the employee header.
 *
 * Bound once, delegated, persisted in localStorage so moving between related
 * order pages keeps the mode. ESC exits unless a modal/offcanvas is open.
 * ==========================================================================*/
(function () {
    'use strict';

    if (window.__posWorkspaceFullscreenBound) return;
    window.__posWorkspaceFullscreenBound = true;

    var STORAGE_KEY = 'pos.workspaceFullscreen';
    var CLASS_NAME = 'pos-workspace-fullscreen';
    var EXTRA_CLASSES = ['layout-without-menu'];

    function root() {
        return document.documentElement;
    }

    function isOn() {
        return root().classList.contains(CLASS_NAME);
    }

    function persist(on) {
        try {
            window.localStorage.setItem(STORAGE_KEY, on ? '1' : '0');
        } catch (error) {
            /* private mode / quota */
        }
    }

    function chromeBlockingEscape() {
        return Boolean(
            document.querySelector('.modal.show, .offcanvas.show, .swal2-container')
        );
    }

    function syncButtons(on) {
        document.querySelectorAll('[data-workspace-fullscreen]').forEach(function (btn) {
            var label = on ? 'Exit full screen' : 'Full screen';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.setAttribute('title', label);
            btn.setAttribute('aria-label', label);
        });
    }

    function setFullscreen(on) {
        var html = root();
        html.classList.toggle(CLASS_NAME, on);
        EXTRA_CLASSES.forEach(function (cls) {
            html.classList.toggle(cls, on);
        });

        if (on) {
            html.classList.remove('layout-menu-expanded', 'layout-menu-hover');
        }

        persist(on);
        syncButtons(on);

        window.dispatchEvent(new Event('resize'));
        if (window.Helpers && typeof window.Helpers.update === 'function') {
            window.Helpers.update();
        }
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-workspace-fullscreen]');
        if (!btn) return;
        event.preventDefault();
        setFullscreen(!isOn());
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || !isOn() || event.defaultPrevented) return;
        if (chromeBlockingEscape()) return;
        setFullscreen(false);
    });

    syncButtons(isOn());
})();
