/**
 * Bridge Vuexy's Light/Dark/System toggles to PosTheme + the prefs API.
 * Also wires the account-page variant picker ([data-pos-theme-picker]).
 */
(function () {
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function saveTheme(payload) {
        const url = window.posThemeSaveUrl;
        if (!url) return Promise.resolve(null);

        return fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        }).then((r) => (r.ok ? r.json() : null)).catch(() => null);
    }

    function applyAndPersist(variant, mode) {
        if (window.PosTheme) {
            window.PosTheme.apply(variant, mode);
        }
        try {
            localStorage.setItem('pos_theme', JSON.stringify({ variant, mode }));
            localStorage.setItem('pos:theme:changed', JSON.stringify({
                theme_variant: variant,
                theme_mode: mode,
                t: Date.now(),
            }));
        } catch (_) {}
        return saveTheme({ theme_variant: variant, theme_mode: mode });
    }

    function currentVariant() {
        return document.documentElement.getAttribute('data-pos-theme') || 'lake';
    }

    // Navbar mode toggles
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-pos-theme-mode]');
        if (!btn) return;
        const mode = btn.getAttribute('data-pos-theme-mode');
        if (!['light', 'dark', 'system'].includes(mode)) return;
        applyAndPersist(currentVariant(), mode);
    });

    // Account variant picker
    function bindPicker(root) {
        if (!root || root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        const status = root.querySelector('[data-pos-theme-status]');
        const setStatus = (text, tone) => {
            if (!status) return;
            status.textContent = text || '';
            status.classList.remove('text-success', 'text-danger', 'text-muted');
            status.classList.add(tone || 'text-muted');
        };

        const selected = () => ({
            variant: root.querySelector('[data-pos-theme-variant]:checked')?.value || currentVariant(),
            mode: root.querySelector('[data-pos-theme-mode-select]')?.value || 'light',
        });

        const syncSelected = (variant) => {
            root.querySelectorAll('[data-pos-theme-card]').forEach((card) => {
                card.classList.toggle('is-selected', card.getAttribute('data-pos-theme-card') === variant);
            });
        };

        root.addEventListener('change', function (e) {
            const t = e.target;
            if (!t.matches('[data-pos-theme-variant], [data-pos-theme-mode-select]')) return;
            const { variant, mode } = selected();
            syncSelected(variant);
            setStatus('Saving…', 'text-muted');
            applyAndPersist(variant, mode).then((res) => {
                if (res && res.ok) {
                    setStatus('Saved', 'text-success');
                    if (res.theme?.variant) syncSelected(res.theme.variant);
                } else {
                    setStatus('Could not save', 'text-danger');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-pos-theme-picker]').forEach(bindPicker);
    });
})();
