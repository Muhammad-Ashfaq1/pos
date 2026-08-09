{{--
    Pre-paint theme reconcile. Include in <head>, BEFORE the stylesheets.

    The server already renders the authoritative theme onto <html> (class +
    data-pos-theme + data-bs-theme), so there is no flash to fix in the normal
    case, and this script deliberately does NOT override that. Its only jobs:

      1. `system` mode — the server cannot read prefers-color-scheme, so it
         renders the saved palette and the browser may need the other end of
         it. Resolving that here rather than in pos-theme.js moves it ahead of
         the stylesheets, so a system-dark user stops seeing a light frame for
         the first paint.

      2. Nothing rendered — a cached or otherwise attribute-less document.
         Only then does the localStorage cache get a say.

    Order matters and is the whole point: SERVER > localStorage, always. A
    cache that could outrank the database is exactly how "I saved a theme and
    it came back wrong" happens, so the stored copy is a fallback and a
    pre-paint hint, never a source of truth.

    Inline on purpose: an external file cannot run before the stylesheets it is
    meant to pre-empt.
--}}
<script>
(function () {
    var html = document.documentElement;
    var VARIANTS = ['sky', 'lake', 'eggplant', 'dark', 'high-contrast'];
    var DARK = ['dark', 'high-contrast'];

    var variant = html.getAttribute('data-pos-theme');
    var mode = html.getAttribute('data-pos-theme-mode');

    // (2) Server said nothing — fall back to the cache, if it is sane.
    if (VARIANTS.indexOf(variant) === -1) {
        try {
            var cached = JSON.parse(localStorage.getItem('pos_theme') || 'null');
            if (cached && VARIANTS.indexOf(cached.variant) !== -1) {
                variant = cached.variant;
                mode = cached.mode || mode;
            }
        } catch (e) {}
    }

    if (VARIANTS.indexOf(variant) === -1) return;

    // (1) Only 'system' needs resolving here; light and dark were already
    // settled server-side and re-deciding them would just risk disagreeing.
    if (mode !== 'system') return;

    var prefersDark = window.matchMedia
        && window.matchMedia('(prefers-color-scheme: dark)').matches;

    var isDarkVariant = DARK.indexOf(variant) !== -1;
    var rendered = variant;

    if (prefersDark && !isDarkVariant) {
        rendered = 'dark';
    } else if (!prefersDark && isDarkVariant) {
        rendered = 'lake';
    }

    if (rendered === variant) return;

    for (var i = 0; i < VARIANTS.length; i++) {
        html.classList.remove('pos-theme-' + VARIANTS[i]);
    }
    // High contrast is an overlay ON the dark theme — it wears both classes,
    // mirroring AppTheme::classesFor().
    if (rendered === 'high-contrast') html.classList.add('pos-theme-dark');
    html.classList.add('pos-theme-' + rendered);

    html.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
    html.setAttribute('data-pos-theme', rendered);
})();
</script>
