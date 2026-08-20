# UI Kit & Design System

The UI layer of this POS application, written as a **kit lifted from AWT Phone**
and renamed to the `pos-` prefix. Different domain — same shell, same surfaces,
same rules.

Source reference: AWT Phone `docs/51-ui-kit.md`.

---

## One-paragraph summary

Vuexy gives you a Bootstrap 5 shell (sidebar, navbar, cards, utilities). On top
of it sits a small kit — prefixed `pos-` — that supplies the things Vuexy does
not: a **theme system** with user-chosen palettes, a **glass surface** every
card is built from, an **AJAX table**, one **confirm dialog**, and a **mobile
master-detail** toggle. Everything else is per-screen CSS, namespaced and
additive, that reads the kit's variables and never fights it.

---

## Three layers

| Layer | Lives in | Rule |
|---|---|---|
| **1. Vendor** | `public/assets/vendor/css/core.css`, `demo.css` | Vuexy itself. **Never edited.** |
| **2. Kit** | `public/assets/css/pos-*.css`, `public/assets/js/pos-*.js` | Shared, app-agnostic. Changes affect every screen. |
| **3. Screen** | `public/assets/css/<area>-<screen>.css` | One file per screen, namespaced. |

Dependency flows downward only: screen → kit → vendor.

### Portable kit files

| File | What it gives you |
|---|---|
| `public/assets/css/pos-glass.css` | Surfaces, tone palette, stat/intro/pill primitives |
| `public/assets/css/pos-themes.css` | Palettes: sky / lake / eggplant / dark / high-contrast |
| `public/assets/css/pos-table.css` + `js/pos-table.js` + `<x-pos-table>` + `App\Support\TableFragment` | AJAX table |
| `public/assets/js/pos-confirm.js` | Confirm/prompt dialog (`PosConfirm`) |
| `public/assets/js/pos-master-detail.js` | Mobile list↔detail toggle |
| `public/assets/js/pos-theme.js` + `partials/_theme-prepaint` + `App\Support\AppTheme` | Theme engine |
| `public/assets/css/pos-menu.css` | Sidebar sub-item icons |
| `public/assets/css/pos-responsive.css` | Shared mobile fixes (loaded last) |
| `public/assets/css/pos-listing.css` + `js/pos-listing-toolbar.js` + `partials/pos-listing-assets` | Glass DataTables listing chrome (toolbar, table, modal) — see [pos-listing.md](pos-listing.md) |
| `public/assets/css/pos-navbar.css` | Glass detached navbar + account dropdown |
| `resources/views/components/settings/*` | Self-saving settings tab shells |

---

## Theme

Resolved by `AppTheme::resolve()`:

1. user's personal `theme_variant` / `theme_mode` (on `users`)
2. else tenant defaults (on `tenants`)
3. else platform default: **lake** / **light**

Variants: `sky`, `lake`, `eggplant`, `dark`, `high-contrast`  
Modes: `light`, `dark`, `system`

`<html>` carries `pos-theme-*` classes plus `data-pos-theme`, `data-pos-theme-mode`,
and `data-bs-theme`. Prefs save via `PUT /account/theme` (`account.theme.update`).
Appearance UI lives on Account → Profile. Navbar Light/Dark/System also persists.

Shop brand (`--shop-brand-primary`) maps onto `--bs-primary` / `--bs-primary-rgb`
so glass tones follow the shop colour when set.

---

## Surfaces — glass

```blade
<div class="pos-glass-card pos-tone-primary">
  <div class="pos-glass-intro">…</div>
</div>

<div class="pos-glass-card pos-tone-success h-100">
  <div class="pos-stat-body">
    <div class="pos-stat-head">
      <span class="pos-stat-icon"><i class="icon-base ti tabler-cash" aria-hidden="true"></i></span>
      <h6 class="pos-stat-label">Collected</h6>
    </div>
    <p class="pos-stat-value">…</p>
  </div>
</div>
```

Tones: `primary`, `info`, `success`, `warning`, `danger`, `secondary`, `dark`.

---

## Page anatomy

Banner → KPI row → content. Reference screens:

- Tenant: `resources/views/tenant/dashboard.blade.php` + `tenant-dashboard.css`
- Admin: `resources/views/admin/dashboard.blade.php` + `admin-dashboard.css`
- Employee: `resources/views/employee/dashboard.blade.php` + `employee-dashboard.css`

---

## Layout head order

1. Vuexy `core.css` → `demo.css`
2. `pos-themes.css`
3. `@stack('styles')`
4. `pos-responsive.css` (last), plus `pos-table.css` / `pos-menu.css` where needed
5. `_theme-prepaint` + `pos-theme.js` (+ `pos-theme-bridge.js`)

Scripts foot: vendor → `pos-confirm.js` → existing notif helpers → `pos-table.js` →
`pos-master-detail.js` → `@stack('page-script')`.

Cache-bust first-party assets with `filemtime()`.

---

## Tables & confirms

```blade
<x-pos-table id="example" :state="['page' => $rows->currentPage(), 'per_page' => $perPage]">
    <table class="table">…</table>
    {{ $rows->links() }}
</x-pos-table>
```

Header: `X-POS-Table`. Most existing lists still use DataTables — the kit is
ready for the next Blade-paginated screen.

```js
await PosConfirm.open({ title: 'Delete', message: '…', tone: 'danger' });
```

SweetAlert2 / Notiflix remain for legacy flows; prefer `PosConfirm` for new
destructive actions.

---

## CSS conventions

1. One file per screen, named `<area>-<screen>.css`
2. Namespace every class (`.pos-td-*`, `.pos-ad-*`, …)
3. Additive — no `!important`
4. Colours from theme variables, not hard hex (unless meaning must survive themes)
5. Respect `prefers-reduced-motion`

---

## Adding a screen checklist

- [ ] `@extends` the right layout
- [ ] `@push('styles')` — `pos-glass.css` first, then your screen file
- [ ] Banner → KPI row → content where it fits
- [ ] Destructive actions through `PosConfirm`
- [ ] Icons `aria-hidden`; icon-only controls labelled
- [ ] Check light variant, `dark`, and `high-contrast`
- [ ] Check at 991 / 767 / 575 px

### DataTables listing pages

For ecommerce (and similar) index tables, follow **[pos-listing.md](pos-listing.md)** — glass panel, toolbar, Vuexy `badge rounded bg-label-*`, outline row actions. Do not invent a one-off card layout.
