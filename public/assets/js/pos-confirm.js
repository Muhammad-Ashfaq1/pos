/**
 * PosConfirm — the application's one confirmation / prompt dialog.
 *
 * Replaces every native `confirm()` and `prompt()` in the product. Those cannot
 * be styled, cannot show a loading state, block the whole tab while open, and
 * render as "phone.allwetrade.com says" — which reads as a browser warning
 * rather than as this application asking a question.
 *
 * ── Using it ──────────────────────────────────────────────────────────────
 *
 *   // Ask, then act:
 *   if (await PosConfirm.open({ title: 'Cancel meeting', message: '…' })) { … }
 *
 *   // Ask AND run the work inside the dialog, so the buttons can show a
 *   // spinner and a failure can be reported without losing the dialog:
 *   PosConfirm.open({
 *       title: 'Delete recording',
 *       confirmText: 'Delete',
 *       tone: 'danger',
 *       onConfirm: async () => { const r = await fetch(url); if (!r.ok) throw new Error('…'); },
 *   });
 *
 *   // Ask for a value:
 *   const url = await PosConfirm.prompt({ title: 'Insert link', label: 'URL' });
 *
 *   // Or declaratively, with no JS at the call site at all — this is what
 *   // replaced the inline `onsubmit="return confirm(…)"` attributes:
 *   <form … data-pos-confirm="Everyone invited will lose access."
 *            data-pos-confirm-title="Cancel meeting"
 *            data-pos-confirm-text="Cancel meeting"
 *            data-pos-confirm-tone="danger">
 *
 * ── Why it is self-contained ──────────────────────────────────────────────
 *
 * Markup and styles are injected once, on first use, rather than living in a
 * Blade partial and four stylesheets. Four layouts (admin, tenant, operator,
 * auth) would otherwise each need the same include, and any that was missed
 * would silently fall back to a native dialog — which is the bug being fixed.
 * It also means the overlay is a direct child of <body>, above every header,
 * dropdown and drawer, with no stacking-context surprises.
 *
 * No dependency on Bootstrap, SweetAlert2, Notiflix or jQuery: this file loads
 * on pages where some of those are absent, and a confirmation that silently
 * does not appear would let a destructive action through unguarded.
 */
(function () {
    'use strict';

    if (window.PosConfirm) return;      // already loaded

    var STYLE_ID = 'pos-confirm-styles';
    var Z = 20000;                      // above every header, drawer and modal

    var state = {
        root: null,
        resolve: null,
        lastFocus: null,
        busy: false,
        isPrompt: false,
    };

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) return;

        var css = ''
            + '.pos-confirm-overlay{position:fixed;inset:0;z-index:' + Z + ';display:flex;'
            + 'align-items:center;justify-content:center;padding:16px;'
            + 'background:rgba(15,23,42,.45);opacity:0;transition:opacity .12s ease}'
            + '.pos-confirm-overlay.is-open{opacity:1}'
            + '.pos-confirm-box{width:100%;max-width:460px;background:#fff;border-radius:10px;'
            + 'box-shadow:0 12px 40px rgba(15,23,42,.18);transform:translateY(8px);'
            + 'transition:transform .12s ease;max-height:calc(100vh - 32px);overflow:auto}'
            + '.pos-confirm-overlay.is-open .pos-confirm-box{transform:none}'
            + '.pos-confirm-body{display:flex;gap:14px;padding:24px 24px 4px}'
            + '.pos-confirm-icon{flex:0 0 40px;width:40px;height:40px;border-radius:50%;'
            + 'display:inline-flex;align-items:center;justify-content:center;font-size:1.25rem}'
            + '.pos-confirm-icon.is-danger{background:rgba(239,91,91,.12);color:#ef5b5b}'
            + '.pos-confirm-icon.is-warning{background:rgba(255,171,0,.16);color:#b76e00}'
            + '.pos-confirm-icon.is-primary{background:rgba(6,111,193,.12);color:#066fc1}'
            + '.pos-confirm-text{flex:1 1 auto;min-width:0}'
            + '.pos-confirm-title{margin:0 0 6px;font-size:1.05rem;font-weight:600;color:#384551;line-height:1.35}'
            + '.pos-confirm-message{margin:0;font-size:.9rem;line-height:1.5;color:#8592a3;overflow-wrap:anywhere}'
            + '.pos-confirm-field{margin-top:14px}'
            + '.pos-confirm-label{display:block;margin-bottom:4px;font-size:.8rem;color:#8592a3}'
            + '.pos-confirm-input{width:100%;padding:8px 10px;border:1px solid #d0d5dd;border-radius:6px;'
            + 'font-size:.9rem;color:#384551}'
            + '.pos-confirm-input:focus{outline:none;border-color:#066fc1;box-shadow:0 0 0 3px rgba(6,111,193,.15)}'
            + '.pos-confirm-error{display:none;margin:12px 24px 0;padding:8px 10px;border-radius:6px;'
            + 'background:rgba(239,91,91,.1);color:#b23b32;font-size:.82rem;line-height:1.4}'
            + '.pos-confirm-error.is-shown{display:block}'
            + '.pos-confirm-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;'
            + 'padding:18px 24px 22px}'
            + '.pos-confirm-btn{border:1px solid transparent;border-radius:8px;padding:9px 18px;'
            + 'font-size:.9rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px}'
            + '.pos-confirm-btn:disabled{opacity:.65;cursor:not-allowed}'
            + '.pos-confirm-cancel{background:transparent;border-color:#d0d5dd;color:#566a7f}'
            + '.pos-confirm-cancel:hover:not(:disabled){background:#f7f8fa}'
            + '.pos-confirm-ok{color:#fff}'
            + '.pos-confirm-ok.is-danger{background:#ef5b5b}'
            + '.pos-confirm-ok.is-danger:hover:not(:disabled){background:#d94646}'
            + '.pos-confirm-ok.is-warning{background:#b76e00}'
            + '.pos-confirm-ok.is-primary{background:#066fc1}'
            + '.pos-confirm-ok.is-primary:hover:not(:disabled){background:#054e87}'
            + '.pos-confirm-spin{width:14px;height:14px;border:2px solid rgba(255,255,255,.45);'
            + 'border-top-color:#fff;border-radius:50%;animation:pos-confirm-spin .7s linear infinite}'
            + '@keyframes pos-confirm-spin{to{transform:rotate(360deg)}}'
            + '@media (max-width:575.98px){.pos-confirm-body{padding:20px 18px 4px}'
            + '.pos-confirm-actions{padding:16px 18px 20px;flex-direction:column-reverse;align-items:stretch}'
            + '.pos-confirm-btn{justify-content:center}}';

        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = css;
        document.head.appendChild(style);
    }

    function build() {
        // Turbo replaces <body> (and prunes <head> elements the incoming page
        // does not declare) on every navigation, which detaches a dialog built
        // on an earlier page. Re-attach the existing node instead of returning
        // one that is no longer in the document: its listeners are already
        // bound, and a dialog that silently fails to appear would let the
        // destructive action through unguarded — the exact bug this file fixes.
        injectStyles();

        if (state.root) {
            if (!state.root.isConnected) document.body.appendChild(state.root);
            return state.root;
        }

        var overlay = document.createElement('div');
        overlay.className = 'pos-confirm-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'posConfirmTitle');
        overlay.setAttribute('aria-describedby', 'posConfirmMessage');
        overlay.hidden = true;

        overlay.innerHTML = ''
            + '<div class="pos-confirm-box">'
            + '  <div class="pos-confirm-body">'
            + '    <span class="pos-confirm-icon" data-pos-icon aria-hidden="true"></span>'
            + '    <div class="pos-confirm-text">'
            + '      <h5 class="pos-confirm-title" id="posConfirmTitle"></h5>'
            + '      <p class="pos-confirm-message" id="posConfirmMessage"></p>'
            + '      <div class="pos-confirm-field" data-pos-field hidden>'
            + '        <label class="pos-confirm-label" for="posConfirmInput"></label>'
            + '        <input class="pos-confirm-input" id="posConfirmInput" type="text">'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '  <p class="pos-confirm-error" data-pos-error role="alert"></p>'
            + '  <div class="pos-confirm-actions">'
            + '    <button type="button" class="pos-confirm-btn pos-confirm-cancel" data-pos-cancel></button>'
            + '    <button type="button" class="pos-confirm-btn pos-confirm-ok" data-pos-ok></button>'
            + '  </div>'
            + '</div>';

        document.body.appendChild(overlay);

        overlay.addEventListener('click', function (e) {
            // Backdrop dismisses — but never mid-request, which would leave the
            // agent unsure whether the thing happened.
            if (e.target === overlay && !state.busy) settle(false);
        });

        overlay.querySelector('[data-pos-cancel]').addEventListener('click', function () {
            if (!state.busy) settle(false);
        });

        overlay.querySelector('[data-pos-ok]').addEventListener('click', onConfirmClick);

        overlay.querySelector('#posConfirmInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); onConfirmClick(); }
        });

        state.root = overlay;

        return overlay;
    }

    /** Everything focusable inside the dialog, in tab order. */
    function focusables() {
        return Array.prototype.filter.call(
            state.root.querySelectorAll('button, input, a[href], [tabindex]:not([tabindex="-1"])'),
            function (el) { return !el.disabled && el.offsetParent !== null; }
        );
    }

    function onKeydown(e) {
        if (!state.root || state.root.hidden) return;

        if (e.key === 'Escape') {
            // Safe to dismiss only when nothing is in flight.
            if (!state.busy) { e.preventDefault(); settle(false); }
            return;
        }

        if (e.key !== 'Tab') return;

        // Trap: the dialog is modal, so focus must not walk out into the page
        // behind it.
        var items = focusables();
        if (items.length === 0) return;

        var first = items[0];
        var last = items[items.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    function setBusy(busy) {
        state.busy = busy;

        var ok = state.root.querySelector('[data-pos-ok]');
        var cancel = state.root.querySelector('[data-pos-cancel]');

        ok.disabled = busy;
        cancel.disabled = busy;

        var spin = ok.querySelector('.pos-confirm-spin');

        if (busy && !spin) {
            spin = document.createElement('span');
            spin.className = 'pos-confirm-spin';
            ok.insertBefore(spin, ok.firstChild);
        } else if (!busy && spin) {
            spin.remove();
        }
    }

    function showError(message) {
        var el = state.root.querySelector('[data-pos-error]');
        el.textContent = message;
        el.classList.add('is-shown');
    }

    function clearError() {
        var el = state.root.querySelector('[data-pos-error]');
        el.textContent = '';
        el.classList.remove('is-shown');
    }

    function onConfirmClick() {
        if (state.busy) return;         // one execution per opening, however many clicks

        clearError();

        var value = state.isPrompt ? state.root.querySelector('#posConfirmInput').value.trim() : true;

        if (state.isPrompt && state.required && value === '') {
            showError('Please enter a value.');
            return;
        }

        if (typeof state.onConfirm !== 'function') {
            settle(value);
            return;
        }

        // The caller does its work INSIDE the dialog, so the buttons can show a
        // spinner and a failure keeps the dialog open with a reason.
        setBusy(true);

        Promise.resolve()
            .then(function () { return state.onConfirm(value); })
            .then(function () { setBusy(false); settle(value); })
            .catch(function (err) {
                setBusy(false);
                showError((err && err.message) ? err.message : 'That didn’t work. Please try again.');
            });
    }

    function settle(result) {
        var resolve = state.resolve;

        state.resolve = null;
        state.onConfirm = null;
        state.busy = false;

        document.removeEventListener('keydown', onKeydown, true);

        state.root.classList.remove('is-open');

        window.setTimeout(function () {
            if (!state.resolve) state.root.hidden = true;   // a re-open may have won the race
        }, 120);

        // Focus goes back where it came from, so a keyboard user is not dumped
        // at the top of the document.
        if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
            try { state.lastFocus.focus(); } catch (e) { /* the trigger may be gone */ }
        }
        state.lastFocus = null;

        if (typeof resolve === 'function') resolve(result);
    }

    var ICONS = {
        danger: '⚠',
        warning: '⚠',
        primary: 'ℹ',
    };

    function open(options) {
        options = options || {};

        var root = build();

        // A second open() while one is up: answer the first as cancelled rather
        // than stranding its promise forever.
        if (typeof state.resolve === 'function') settle(false);

        state.isPrompt = options.isPrompt === true;
        state.required = options.required !== false;
        state.onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        state.lastFocus = document.activeElement;

        var tone = options.tone === 'danger' || options.tone === 'warning' ? options.tone : 'primary';

        var icon = root.querySelector('[data-pos-icon]');
        icon.className = 'pos-confirm-icon is-' + tone;
        icon.textContent = options.icon || ICONS[tone];
        icon.hidden = options.icon === false;

        root.querySelector('#posConfirmTitle').textContent = options.title || 'Are you sure?';

        var message = root.querySelector('#posConfirmMessage');
        message.textContent = options.message || '';
        message.hidden = !options.message;

        var field = root.querySelector('[data-pos-field]');
        var input = root.querySelector('#posConfirmInput');

        field.hidden = !state.isPrompt;
        if (state.isPrompt) {
            root.querySelector('.pos-confirm-label').textContent = options.label || '';
            input.type = options.inputType || 'text';
            input.value = options.value || '';
            input.placeholder = options.placeholder || '';
        }

        var ok = root.querySelector('[data-pos-ok]');
        ok.className = 'pos-confirm-btn pos-confirm-ok is-' + tone;
        ok.textContent = options.confirmText || 'Confirm';

        root.querySelector('[data-pos-cancel]').textContent = options.cancelText || 'Cancel';

        clearError();
        setBusy(false);

        root.hidden = false;
        // Next frame, so the opening transition actually runs.
        window.requestAnimationFrame(function () { root.classList.add('is-open'); });

        document.addEventListener('keydown', onKeydown, true);

        window.setTimeout(function () {
            if (state.isPrompt) { input.focus(); input.select(); }
            else ok.focus();
        }, 20);

        return new Promise(function (resolve) { state.resolve = resolve; });
    }

    window.PosConfirm = {
        /** @returns {Promise<boolean>} */
        open: function (options) {
            return open(options).then(function (r) { return r === true; });
        },

        /** @returns {Promise<string|null>} the value, or null if cancelled */
        prompt: function (options) {
            options = options || {};
            options.isPrompt = true;
            options.confirmText = options.confirmText || 'OK';

            return open(options).then(function (r) {
                return typeof r === 'string' ? r : null;
            });
        },
    };

    // If a Turbo visit starts while the dialog is up, the page it was asking
    // about is going away. Answer it as cancelled so its promise is not
    // stranded and the document-level key handler is unbound — a caller left
    // awaiting forever would otherwise never run, and never say why.
    document.addEventListener('turbo:before-render', function () {
        if (typeof state.resolve === 'function') settle(false);
    });

    /**
     * Declarative layer.
     *
     * `data-pos-confirm` on a form, link or button asks first and then does
     * exactly what it would have done. This is what the inline
     * `onsubmit="return confirm(…)"` attributes became — the guard is still
     * one attribute at the call site, it just no longer opens a browser dialog.
     */
    function optionsFrom(el) {
        return {
            title: el.getAttribute('data-pos-confirm-title') || 'Are you sure?',
            message: el.getAttribute('data-pos-confirm') || '',
            confirmText: el.getAttribute('data-pos-confirm-text') || 'Confirm',
            cancelText: el.getAttribute('data-pos-confirm-cancel') || 'Cancel',
            tone: el.getAttribute('data-pos-confirm-tone') || 'danger',
        };
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.matches || !form.matches('[data-pos-confirm]')) return;
        if (form.dataset.posConfirmed === '1') return;      // second pass: let it through

        e.preventDefault();
        e.stopPropagation();

        window.PosConfirm.open(optionsFrom(form)).then(function (ok) {
            if (!ok) return;
            form.dataset.posConfirmed = '1';
            // requestSubmit keeps native validation and the submitter; submit()
            // is the fallback for older engines.
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        });
    }, true);

    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('a[data-pos-confirm], button[data-pos-confirm]') : null;
        if (!el) return;
        if (el.closest('form[data-pos-confirm]')) return;   // the form guard owns it
        if (el.dataset.posConfirmed === '1') { delete el.dataset.posConfirmed; return; }

        e.preventDefault();
        e.stopPropagation();

        window.PosConfirm.open(optionsFrom(el)).then(function (ok) {
            if (!ok) return;
            el.dataset.posConfirmed = '1';
            el.click();
        });
    }, true);
})();
