/* ============================================================================
 * pos-master-detail.js — shared mobile single-pane toggle for rc-* layouts
 * ----------------------------------------------------------------------------
 * The two/three-pane "list + detail" screens (SMS, calls, contacts, fax, video
 * hub) render side-by-side on desktop. Below md (≤767.98px) pos-responsive.css
 * collapses them to a single pane; this script flips WHICH pane is shown:
 *   • tapping a list row  → show the detail pane   (add `.pos-md-detail-active`)
 *   • tapping a back arrow → show the list again   (remove the class)
 *
 * It is deliberately generic and OPT-IN via three markers a page adds to its
 * EXISTING containers (no new nesting, no per-row edits):
 *   [data-pos-md]         on the flex body wrapping the two panes
 *   [data-pos-md-list]    on the list pane
 *   [data-pos-md-detail]  on the detail pane
 *   [data-pos-md-back]    on a back button placed inside the detail pane
 *
 * On a narrow screen the detail opens as a BOTTOM SHEET covering 60% of the
 * viewport, with the list still visible (and dimmed) behind it — you can see
 * where you were, and dismissing lands back on it.
 *
 * A row tap opens the detail directly — no second tap on a HUD/details icon.
 * Opening pushes one history entry on mobile, so the browser/hardware Back
 * button returns to the list (with its tab, search, filters, pagination and
 * scroll position all intact, because the list is never re-rendered).
 *
 * Non-regression: this only toggles ONE class on the body wrapper. It never
 * touches the existing row-click handlers (they still fire and load data as
 * before — we run alongside them), reads no dimensions, and on desktop the
 * class has no visual effect because the collapsing CSS lives inside the media
 * query — and no history entry is pushed there either. Delegated on document,
 * bound once, so Turbo navigations never double-bind.
 * ==========================================================================*/
(function () {
    'use strict';

    if (window.__posMasterDetailBound) return;
    window.__posMasterDetailBound = true;

    var ACTIVE = 'pos-md-detail-active';
    // Row types across the rc-* families, plus an explicit opt-in hook.
    // `.rc-fav-item` = Phone favourites, `.adl-item` = Auto-Dialer sessions.
    var ITEM_SELECTOR = '.rc-conv-item, .rc-call-item, .rc-contact-item, .rc-contacts-fav-row, ' +
        '.rc-fav-item, .adl-item, ' +
        // Video meetings (Upcoming and Past) are one row type and mark
        // themselves with the master-detail hook rather than a styling class.
        '[data-video-card], [data-pos-md-open]';

    /**
     * Controls that DO something on their own.
     *
     * A tap on a row's Call, Text, Play, Delete or Favourite button must run
     * that action and nothing else — opening the detail as well would yank the
     * agent away from the list they were working through. The row itself is
     * allowed to be one of these (some lists render rows as buttons/links);
     * only a control NESTED inside the row suppresses the navigation.
     */
    var ACTION_SELECTOR = 'button, a[href], input, select, textarea, label, [role="button"], [data-pos-md-ignore]';

    function listPaneOf(root) {
        return root ? root.querySelector('[data-pos-md-list]') : null;
    }

    function detailPaneOf(root) {
        return root ? root.querySelector('[data-pos-md-detail]') : null;
    }

    /**
     * Has the detail taken over the screen?
     *
     * Asked of the computed style rather than of a breakpoint, because the
     * collapse point differs per module — the SMS/Contacts/Video panes split at
     * 768px, the Phone family at 992px. Two shapes count:
     *
     *   • a bottom sheet — `position: fixed`, set by exactly the rules that
     *     make it a sheet (the list stays visible behind it, so its visibility
     *     cannot be the signal)
     *   • a full-screen detail — the Text tab's chat room, where the list is
     *     swapped out entirely
     *
     * On desktop neither is true, so no history entry is pushed and Back keeps
     * meaning what it always did there.
     */
    function isSheet(root) {
        var dp = detailPaneOf(root);
        if (!dp || !window.getComputedStyle) return false;

        if (window.getComputedStyle(dp).position === 'fixed') return true;

        // Full-screen mode: the list is gone, so the detail is the screen.
        var lp = listPaneOf(root);

        return !!lp && window.getComputedStyle(lp).display === 'none';
    }

    /**
     * When the detail last closed.
     *
     * A tap that dismisses the sheet lands near the top of it — which is
     * exactly where the LIST is once the sheet slides away. Touch browsers can
     * deliver a second, synthetic click at those coordinates after the first
     * one has already closed the sheet, and that ghost tap would land on a row
     * and reopen it: the "closes for a moment, then opens again" behaviour.
     * Opens are ignored for a moment after a close, which is far shorter than
     * any deliberate tap-close-tap and long enough to swallow the ghost.
     *
     * Recorded PER ROOT: dismissing the Phone sheet has no business blocking a
     * tap in Contacts.
     */
    var REOPEN_GUARD_MS = 400;

    function closedRecently(root) {
        var at = parseInt(root.dataset.posMdClosedAt || '0', 10);

        return at > 0 && (Date.now() - at) < REOPEN_GUARD_MS;
    }

    /** How long to wait for popstate before closing without it. */
    var HISTORY_FALLBACK_MS = 150;

    function showDetail(root, viaHistory) {
        if (!root || root.classList.contains(ACTIVE)) return;
        if (!viaHistory && closedRecently(root)) return;

        // Preserve the list scroll position so returning lands where you were.
        var lp = listPaneOf(root);
        if (lp) root.dataset.posMdScroll = String(lp.scrollTop || 0);
        root.classList.add(ACTIVE);

        // Make the hardware / browser Back button close the detail instead of
        // leaving the page — the list, its tab, search, filters and scroll are
        // all still in the DOM, so returning to them costs nothing and loses
        // nothing. Only where the detail actually took over the screen.
        if (!viaHistory && isSheet(root)) {
            try {
                window.history.pushState({ posMdDetail: true }, '');
                root.dataset.posMdHistory = '1';
            } catch (e) { /* history unavailable — the back control still works */ }
        }

        // Move focus into the detail pane for keyboard / screen-reader users.
        var back = root.querySelector('[data-pos-md-back]');
        if (back) back.focus();
    }

    /**
     * Actually close. The ONLY place the active class is removed.
     *
     * Having one exit is the point: when the control removed the class itself
     * AND asked the browser to go back, the state could be written twice for a
     * single dismissal, and anything that re-entered in between (a ghost tap, a
     * restored history entry) left the sheet open again.
     */
    function closeNow(root) {
        if (!root || !root.classList.contains(ACTIVE)) return;

        root.classList.remove(ACTIVE);
        root.dataset.posMdClosedAt = String(Date.now());

        // Restore the remembered list scroll position.
        var lp = listPaneOf(root);
        if (lp && root.dataset.posMdScroll != null) {
            lp.scrollTop = parseInt(root.dataset.posMdScroll, 10) || 0;
        }

        delete root.dataset.posMdHistory;
    }

    /**
     * Ask to close — from the back control, or a tap on the backdrop.
     *
     * When the open pushed a history entry, this goes BACK and lets the popstate
     * handler do the closing, so the entry is unwound and the class is removed
     * by one path rather than two. A safety timer covers the case where popstate
     * never arrives, so a dismissal can never be swallowed.
     */
    function requestClose(root) {
        if (!root || !root.classList.contains(ACTIVE)) return;

        if (root.dataset.posMdHistory === '1') {
            root.dataset.posMdHistory = 'closing';
            try {
                window.history.back();
                window.setTimeout(function () { closeNow(root); }, HISTORY_FALLBACK_MS);

                return;
            } catch (e) {
                root.dataset.posMdHistory = '1';
            }
        }

        closeNow(root);
    }

    // Kept as the public name the rest of the file (and any caller) uses.
    function showList(root, viaHistory) {
        if (viaHistory) {
            closeNow(root);

            return;
        }

        requestClose(root);
    }

    // Browser / hardware Back while a detail is open → back to the list.
    // Browser / hardware Back, and the entry unwound by requestClose(), both
    // land here — the single close path.
    window.addEventListener('popstate', function () {
        var open = document.querySelector('[data-pos-md].' + ACTIVE);
        if (open) closeNow(open);
    });

    document.addEventListener('click', function (e) {
        // Back arrow → return to the list.
        var back = e.target.closest('[data-pos-md-back]');
        if (back) {
            showList(back.closest('[data-pos-md]'));
            return;
        }

        // The dimmed area above the sheet is the ROOT's own ::after, so a tap on
        // it lands on the root element rather than on any child. Dismiss, as a
        // bottom sheet should.
        if (e.target.hasAttribute && e.target.hasAttribute('data-pos-md')) {
            if (e.target.classList.contains(ACTIVE) && isSheet(e.target)) {
                showList(e.target);
                return;
            }
        }

        // Explicit "open the secondary pane" trigger (e.g. the Phone HUD button)
        // works regardless of where it sits.
        var opener = e.target.closest('[data-pos-md-open]');
        if (opener) {
            showDetail(opener.closest('[data-pos-md]'));
            return;
        }

        var listPane = e.target.closest('[data-pos-md-list]');
        if (!listPane) return;

        var root = listPane.closest('[data-pos-md]');
        // In "manual" mode generic row taps must NOT open the secondary pane.
        // Retained for any pane that is genuinely an on-demand surface rather
        // than the detail of the row you tapped.
        if (root && root.hasAttribute('data-pos-md-manual')) return;

        var item = e.target.closest(ITEM_SELECTOR);
        if (!item || !listPane.contains(item)) return;

        // A row's own action button (Call, Text, Play, Delete, Favourite, the
        // select checkbox, a dropdown toggle) does its job WITHOUT navigating.
        // The row may itself be a button or link, so only a nested control
        // counts.
        var action = e.target.closest(ACTION_SELECTOR);
        if (action && action !== item && item.contains(action)) return;

        showDetail(root);
    }, false);
})();
