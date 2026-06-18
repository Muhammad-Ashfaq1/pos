# Public Landing Page

The public landing page is the unauthenticated marketing homepage served at `/`. It positions OCC ("Oil Change POS SaaS") as an all-in-one SaaS platform for car garages, oil change shops, tire & brake centers, detailing studios, and quick auto-service businesses. It is the entry point for the two conversion actions the platform cares about: **registering a shop** (`/register`) and **requesting a demo** (the `demo_requests` lead-capture form).

This document covers the page itself — its structure, sections, content model, styling, and scripts. For the surrounding flows see:

- The route/redirect logic and the onboarding journey it leads into → [auth-and-onboarding.md](auth-and-onboarding.md#public-landing).
- The demo-request lead capture is covered on this page (see [Request-a-Demo modal](#request-a-demo-modal)); the `demo_requests` schema is in [database.md](database.md#demo_requests-central--non-tenant).

## Route & entry logic

The page is served by the `/` route in [routes/web.php](../routes/web.php), which branches on who is visiting:

```php
Route::get('/', function () {
    if (Auth::guard('customer')->check()) {
        return redirect()->route('customer.dashboard');   // portal customer
    }
    if (! Auth::check()) {
        return view('public.home');                       // guest → landing
    }
    return redirect()->route(Auth::user()->defaultDashboardRouteName()); // staff/admin
});
```

Only fully unauthenticated visitors see the landing page. Logged-in portal customers go to the customer dashboard, and any authenticated staff/admin user is bounced to their role's dashboard via [`User::defaultDashboardRouteName()`](../app/Models/User.php#L110-L118).

The only other public route is the demo form submission:

```
POST /demo-request    name: demo.request.store    middleware: throttle:5,1
```

## Files

| Concern | File |
|---------|------|
| Page view | [resources/views/public/home.blade.php](../resources/views/public/home.blade.php) |
| Layout shell | [resources/views/layouts/public.blade.php](../resources/views/layouts/public.blade.php) |
| Styling | [public/assets/css/public-landing.css](../public/assets/css/public-landing.css) |
| Demo form handler | [`Public\DemoRequestController`](../app/Http/Controllers/Public/DemoRequestController.php) |
| Demo form validation | [`StoreDemoRequestRequest`](../app/Http/Requests/Public/StoreDemoRequestRequest.php) |

The entire page lives in **one Blade file** — there are no partials under `resources/views/public/`. Section content (services, modules, FAQ, plans, etc.) is held in **inline PHP arrays** that are `@foreach`-rendered, so editing copy is a matter of editing those arrays in place (see [Content model](#content-model)).

## Layout shell

[layouts/public.blade.php](../resources/views/layouts/public.blade.php) is a standalone HTML document independent of the admin/tenant `layouts/app.blade.php`. It pulls the Vuexy core CSS/JS (Bootstrap, Iconify icons, node-waves, perfect-scrollbar, jQuery, popper) plus the landing-specific stylesheet, and exposes the usual yield points:

- `@yield('title')` / `@yield('meta_description')` — overridden by `home.blade.php` for SEO; the layout supplies sensible OCC defaults if a child view omits them.
- `@yield('content')`, `@yield('styles')`, `@yield('scripts')`.
- `<meta name="robots" content="index, follow">` — the page is intended to be indexed.

## Page sections

The page renders top-to-bottom in this order. Sections with an `id` are reachable via the navbar/footer anchor links (the layout sets `scroll-behavior: smooth`).

| # | Section | Anchor | What it shows |
|---|---------|--------|---------------|
| 1 | Navbar | — | Sticky brand + anchor nav (Home, Features, Services, Modules, How It Works, FAQ, Contact) and Login / **Register Your Shop** buttons. Collapses to a hamburger on mobile. |
| 2 | Hero | `#home` | Headline, subcopy, feature chips, hero illustration with floating panels, and the primary CTAs (Register / Request Demo). |
| 3 | Industry strip | — | Six audience chips (Car Garages, Oil Change, Tire & Brake, Detailing, Workshops, Quick Lube). |
| 4 | Features / "Why OCC" | `#features` | Three narrative value points plus four benefit cards (front-desk clarity, stock awareness, staff roles, retention). |
| 5 | Services showcase | `#services` | ~17 service pills (oil change, filter swaps, brake/coolant/transmission service, alignment, diagnostics, detailing, etc.). |
| 6 | Modules & features | `#modules` | Twelve module cards mirroring the real app (onboarding, POS billing, inventory, services catalog, customers, vehicle history, roles, loyalty, reminders, reports, customer portal, audit oversight). |
| 7 | How it works | `#how-it-works` | Five-step onboarding timeline (register → admin approval → set up catalog → bill & manage → grow with reminders/loyalty). |
| 8 | Business benefits / Customer experience | — | Two side-by-side cards, six bullet points each. |
| 9 | Stats band | — | Four headline stats (10+ modules, 25-day reminders, multi-role, all-in-one). **Static showcase figures, not live data.** |
| 10 | Social proof | — | Three testimonial cards. **Static placeholders** (named in the copy as such). |
| 11 | Pricing | `#plans` | Three plan cards (Starter / Business / Enterprise), each priced "Contact Us". **Placeholder pricing**, no checkout. |
| 12 | FAQ | `#faq` | Six-item Bootstrap accordion; first item open by default. |
| 13 | Contact & final CTA | `#contact` | Contact strip with sales/support emails, the `demo_success` flash banner, and a repeated final CTA block. |
| 14 | Demo modal | `#demoModal` | The "Request a Demo" form (see below). |
| 15 | Footer | — | Brand blurb, social icons, Product / Company / Access / Support link columns, copyright. |

> **Showcase content.** Stats, testimonials, and pricing are deliberately static placeholders — the footer states "Static public landing page content for showcase and product positioning." Treat them as marketing copy, not as anything wired to the database. The module/service lists, however, do describe real product capabilities documented across the [domain docs](README.md#documentation-map).

## Content model

Most repeating content is defined as inline arrays at the point of use and rendered with `@foreach`. To change copy, edit the array — no controller or database is involved. Quick map of where each list lives in [home.blade.php](../resources/views/public/home.blade.php):

| Content | Approx. location |
|---------|------------------|
| Industry chips | hero industry strip `@foreach` |
| Feature value points & cards | `#features` section markup |
| Service pills | `#services` `@foreach` (flat string array) |
| Module cards | `#modules` `@foreach` (`icon` / `title` / `text`) |
| How-it-works steps | `#how-it-works` timeline markup |
| Business / customer benefit bullets | benefits section `@foreach` |
| Testimonials | social-proof section markup (avatars from `assets/img/avatars/`) |
| Pricing plans | `#plans` plan-card markup |
| FAQ items | `#faq` `@foreach` (`q` / `a`) |
| Demo modal "Type of business" options | `#demoModal` select `@foreach` |

Icons are [Tabler](https://tabler.io/icons) glyphs via the Iconify `icon-base ti tabler-*` classes bundled with the Vuexy template.

## Request-a-Demo modal

The hero, contact strip, final CTA, and footer all trigger the same `#demoModal` Bootstrap modal. It posts to `demo.request.store`:

- **Fields:** `name` *(required)*, `email` *(required)*, `business_name`, `phone`, `business_type` (select), `message`. Validation lives in [`StoreDemoRequestRequest`](../app/Http/Requests/Public/StoreDemoRequestRequest.php).
- **Handler:** [`DemoRequestController@store`](../app/Http/Controllers/Public/DemoRequestController.php) persists a row to the central `demo_requests` table (status defaults to `new`, captures `request()->ip()`), then redirects back to `…#contact` with a `demo_success` flash that renders the success banner in the `#contact` section.
- **Rate limiting:** `throttle:5,1` (5 submissions/minute/IP).
- **Error UX:** on validation failure the page re-opens the modal automatically (the `@if ($errors->any())` script at the bottom shows the modal on load) and renders inline `@error` feedback so the visitor doesn't lose their input.

Leads are **central, not tenant-scoped** — super admins triage them at `/admin/demo-requests`. The `demo_requests` schema is in [database.md](database.md#demo_requests-central--non-tenant).

## Styling

All landing-specific styling is in [public-landing.css](../public/assets/css/public-landing.css) and is namespaced with `occ-`/`landing-` prefixes so it doesn't bleed into the admin theme. It is driven by CSS custom properties defined on `:root` — change these to re-skin the page:

- **Brand colors:** `--occ-primary` (`#2563eb`), `--occ-cyan` (`#06b6d4`), and ink/body/surface tokens.
- **Gradients:** `--occ-grad`, `--occ-grad-deep` (used by `.text-gradient`, hero, and contact strips).
- **Shadows:** `--occ-shadow`, `--occ-shadow-lg`.

Reusable building blocks include `.landing-section`, `.landing-card`, `.section-kicker`, `.landing-icon`, `.hero-shell`, `.service-pill`, `.plan-card`, `.testimonial-card`, `.timeline-step`, `.stats-band`, and decorative `.section-deco` / `.deco-*` elements.

## Scripts

Inline JS in the `@section('scripts')` block of [home.blade.php](../resources/views/public/home.blade.php) provides three behaviors:

1. **Sticky navbar solidify** — adds `.is-scrolled` to `.landing-navbar` once the page scrolls past 24px (gives the navbar a solid background over the hero).
2. **Scroll reveal** — an `IntersectionObserver` adds `.is-visible` to cards/pills/testimonials/plans as they enter the viewport (graceful no-op when `IntersectionObserver` is unavailable).
3. **Demo-modal auto-reopen** — re-shows `#demoModal` on page load when the previous submission had validation errors.

## Customizing the page — quick recipes

- **Change marketing copy / lists:** edit the relevant inline array or markup in [home.blade.php](../resources/views/public/home.blade.php) (see [Content model](#content-model)).
- **Add a new section + nav link:** add a `<section id="…" class="landing-section">` block, then add a matching `<a class="nav-link" href="#…">` in the navbar (and optionally the footer).
- **Re-skin colors:** override the `:root` tokens at the top of [public-landing.css](../public/assets/css/public-landing.css).
- **Update SEO title/description:** edit the `@section('title')` / `@section('meta_description')` lines at the top of [home.blade.php](../resources/views/public/home.blade.php).
- **Add a demo-form field:** add the input to the `#demoModal` form, then update [`StoreDemoRequestRequest`](../app/Http/Requests/Public/StoreDemoRequestRequest.php) validation and the `demo_requests` migration/model fillable — see the [`demo_requests` schema](database.md#demo_requests-central--non-tenant).
