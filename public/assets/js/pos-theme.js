/**
 * Global theme applier.
 *
 * - Exposes `window.PosTheme.apply(variant, mode)` for the settings page to
 *   flip the theme live, before the round-trip save returns.
 * - Handles "system" mode by tracking `prefers-color-scheme` and updating
 *   `data-bs-theme` accordingly.
 * - Listens for cross-tab changes via storage event.
 */
(function () {
    const VARIANTS = ['sky', 'lake', 'eggplant', 'dark', 'high-contrast'];
    const DARK_VARIANTS = ['dark', 'high-contrast'];
    const DEFAULT_LIGHT_VARIANT = 'lake';
    const html = document.documentElement;

    const isDarkVariant = (variant) => DARK_VARIANTS.includes(variant);

    function resolveBsTheme(variant, mode) {
        if (mode === 'dark') return 'dark';
        if (mode === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        // light mode: dark variants still go dark (the variant wins)
        return isDarkVariant(variant) ? 'dark' : 'light';
    }

    function apply(variant, mode) {
        if (!VARIANTS.includes(variant)) variant = 'lake';
        if (!['light', 'dark', 'system'].includes(mode)) mode = 'light';

        const bsTheme = resolveBsTheme(variant, mode);

        // The dark-mode CSS is scoped to html.pos-theme-dark, so the class has to
        // agree with the resolved light/dark, whichever way they disagree.
        //
        // Both directions matter under 'system', where the saved variant is the
        // palette and the OS decides which end of it applies: a light variant on a
        // dark OS renders dark, and — the case this used to get wrong — a DARK
        // variant on a light OS renders the light default instead of leaving dark
        // surfaces under a light data-bs-theme.
        let renderedVariant = variant;
        if (bsTheme === 'dark' && !isDarkVariant(variant)) {
            renderedVariant = 'dark';
        } else if (bsTheme === 'light' && isDarkVariant(variant)) {
            renderedVariant = DEFAULT_LIGHT_VARIANT;
        }

        // Drop any existing pos-theme-* class and add the active one. High
        // contrast wears the dark class too: it is an overlay on the dark theme,
        // not a separate one, so every dark rule applies and its own class only
        // has to raise the contrast. Mirrors AppTheme::classesFor() server-side.
        VARIANTS.forEach(v => html.classList.remove('pos-theme-' + v));
        if (renderedVariant === 'high-contrast') html.classList.add('pos-theme-dark');
        html.classList.add('pos-theme-' + renderedVariant);

        html.setAttribute('data-bs-theme', bsTheme);
        html.setAttribute('data-pos-theme', renderedVariant);
        html.setAttribute('data-pos-theme-mode', mode);
    }

    function current() {
        return {
            variant: html.getAttribute('data-pos-theme') || 'lake',
            mode: html.getAttribute('data-pos-theme-mode') || 'light',
        };
    }

    // Re-resolve on system color-scheme change when in 'system' mode
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const onSystemChange = () => {
        const { variant, mode } = current();
        if (mode === 'system') apply(variant, mode);
    };
    if (mq.addEventListener) mq.addEventListener('change', onSystemChange);
    else if (mq.addListener) mq.addListener(onSystemChange);

    // Cross-tab sync
    window.addEventListener('storage', (e) => {
        if (e.key !== 'pos:theme:changed' || !e.newValue) return;
        try {
            const d = JSON.parse(e.newValue);
            if (d.theme_variant && d.theme_mode) apply(d.theme_variant, d.theme_mode);
        } catch (_) {}
    });

    window.PosTheme = { apply, current };

    // System-mode reconcile on load: the server can't read prefers-color-scheme,
    // so the layout may have rendered the saved light variant while the OS is
    // dark. Re-apply on boot to pick the right class.
    const boot = current();
    if (boot.mode === 'system') apply(boot.variant, boot.mode);
})();
