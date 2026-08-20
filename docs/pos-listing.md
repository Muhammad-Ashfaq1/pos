# Glass listing pages (quick port guide)

How to restyle a DataTables listing to match **Categories / Discounts** without
changing AJAX contracts, routes, or CRUD logic. UI only.

Reference implementations:

| Page | Blade | JS |
|------|-------|-----|
| Categories | `resources/views/tenant/ecommerce/categories/index.blade.php` | `public/assets/js/tenant/e-com/categories.js` |
| Discounts | `resources/views/tenant/ecommerce/discounts/index.blade.php` | `public/assets/js/tenant/e-com/discounts.js` |
| Discount groups | `resources/views/tenant/ecommerce/discounts/group/index.blade.php` | `public/assets/js/tenant/discount-groups.js` |
| Sub-categories | `…/sub-categories/index.blade.php` | `…/subcategories.js` |
| Product types | `…/product-types/index.blade.php` | `…/product-types.js` |

Still to port (same pattern): products, services, customers, vehicles, cards, …

---

## Kit files

| File | Role |
|------|------|
| `public/assets/css/pos-glass.css` | Glass surface + `pos-tone-*` (loaded by layout and/or listing assets) |
| `public/assets/css/pos-listing.css` | Listing chrome: toolbar, table pad, modal, pagination, actions |
| `resources/views/partials/pos-listing-assets.blade.php` | One include: glass + listing CSS (`filemtime` cache-bust) |
| `public/assets/js/pos-listing-toolbar.js` | Moves DataTables `.dt-search` into the Blade search slot |
| `public/assets/js/pos-confirm.js` | Glass delete confirm (`PosConfirm`) — prefer over SweetAlert |

Shell navbar (separate from listings): `pos-navbar.css` + `layouts/partials/navbar.blade.php`.

---

## Blade skeleton

Copy from Discounts / Categories. Keep permission checks and form field names.

```blade
@extends('layouts.app')

@section('title', 'Your Page')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
    <div class="pos-listing">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <h4 class="pos-listing-title">Your Page</h4>
                <div class="pos-listing-search-slot" aria-hidden="true"></div>
                <div class="pos-listing-toolbar-tools">
                    <div class="pos-listing-toolbar-actions" id="yourTableActions">
                        {{-- Filter dropdown (btn-label-secondary btn-icon) + Add button --}}
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="your-datatables table table-hover align-middle">
                    <thead>
                        <tr>…</tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="modal fade pos-listing-modal" id="yourModal" …>
            <div class="modal-dialog modal-*-centered">
                <div class="modal-content">…</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/your-page.js') }}?v={{ filemtime(…) }}"></script>
@endsection
```

### Toolbar rules

1. **Title, filter, Add stay in Blade** — always visible; never park them only in DataTables layout (overflow clips them).
2. **Search only moves via JS** — `PosListingToolbar.align(api)` relocates `.dt-search` into `.pos-listing-search-slot`.
3. Order in the row: **title | search slot | tools (filter + add)**.
4. Drop `thead class="bg-label-primary"` — listing CSS styles headers on glass; keep plain `<thead>`.

### Pages without DataTables search

Omit `.pos-listing-search-slot` (or leave it empty). Toolbar tools still sit on the right (see Discount groups).

---

## JS checklist

### Toolbar hook

Replace any “move filter/Add into `.dt-layout-start`” helper with:

```js
const alignYourActionsWithSearch = function (table) {
  if (window.PosListingToolbar && typeof window.PosListingToolbar.align === 'function') {
    window.PosListingToolbar.align(table, '#yourTableActions');
  }
};

// After DataTable init + in drawCallback:
alignYourActionsWithSearch(this.api()); // or yourTable
```

### Vuexy badges (required pattern)

Use soft-label badges — same as Discounts. Prefer `rounded`:

```js
// Status (from repository: status_badge_class / status_label)
return '<span class="badge rounded ' + row.status_badge_class + '">' +
  escapeHtml(row.status_label) + '</span>';

// Static tone
return '<span class="badge rounded bg-label-info">' + escapeHtml(data) + '</span>';
```

Repository side (unchanged contract):

```php
'status_badge_class' => $model->is_active ? 'bg-label-success' : 'bg-label-secondary',
'status_label' => $model->is_active ? 'Active' : 'Inactive',
```

Common tones: `bg-label-primary`, `info`, `success`, `warning`, `danger`, `secondary`.

Do **not** invent custom badge colours in `pos-listing.css` that fight Vuexy `bg-label-*`.

### Row actions

```js
'<button type="button" class="btn btn-sm btn-icon btn-outline-primary edit-…-btn" …>' +
  '<i class="icon-base ti tabler-edit icon-md"></i></button>'

'<button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-…-btn" …>' +
  '<i class="icon-base ti tabler-trash icon-md"></i></button>'
```

### Delete confirm

```js
await window.PosConfirm.open({
  title: 'Delete …?',
  message: '…',
  confirmText: 'Yes, delete it',
  tone: 'danger',
});
```

Do not switch listing deletes back to SweetAlert on glass pages.

---

## Class cheat sheet

| Class | Where |
|-------|--------|
| `.pos-listing` | Page wrapper |
| `.pos-glass-card.pos-tone-secondary.pos-listing-panel` | Single glass card |
| `.pos-listing-toolbar` / `-title` / `-search-slot` / `-toolbar-tools` / `-toolbar-actions` | Header row |
| `.pos-listing-table` | Around DataTable |
| `.pos-listing-modal` | On `.modal.fade` |
| `badge rounded bg-label-*` | Status / type / flags in cells |
| `btn-outline-primary` / `btn-outline-danger` + `btn-sm btn-icon` | Row actions |
| `btn-label-secondary btn-icon` | Filter trigger |

---

## Port checklist (copy per page)

- [ ] `@push('styles')` → `@include('partials.pos-listing-assets')`
- [ ] Wrap content in `.pos-listing` → `.pos-listing-panel`
- [ ] Toolbar: title | search-slot | filter + Add
- [ ] Table: `.pos-listing-table` + `table-hover align-middle`; plain `<thead>`
- [ ] Modal: add `pos-listing-modal`
- [ ] Scripts: load `pos-listing-toolbar.js` before page JS
- [ ] JS: `PosListingToolbar.align` on init + `drawCallback`
- [ ] Badges: `badge rounded` + `bg-label-*`
- [ ] Actions: outline primary / danger icon buttons
- [ ] Deletes: `PosConfirm` (if interactive delete)
- [ ] Smoke: search in toolbar, filter/Add visible, modal X visible, badges readable light + dark

---

## Do / don’t

**Do**

- Keep DataTables AJAX URLs, column `data` keys, and form `name`s unchanged
- Cache-bust first-party assets with `filemtime()`
- Use theme / Bootstrap variables (primary RGB) — shop brand follows `--bs-primary`

**Don’t**

- Move filter/Add out of Blade into DT layout only (gets clipped)
- Put `overflow: hidden` on the listing panel if dropdowns must escape
- Nest `backdrop-filter` heavily inside glass (dropdowns: prefer near-opaque fill)
- Edit Vuexy `core.css` / `demo.css`

---

## Related

- [ui-kit.md](ui-kit.md) — full design system (glass, themes, confirm, layout head order)
- [catalog.md](catalog.md) — domain routes / repositories for ecommerce listings
