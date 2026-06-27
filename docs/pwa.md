# Progressive Web App (Installable)

The app ships as an **installable PWA**. On any HTTPS origin (or `localhost`), Chrome/Edge show an **install icon in the address bar**, and mobile browsers offer "Add to Home Screen". Once installed it launches in a standalone window (no browser chrome) with its own desktop/dock/home-screen icon.

This is a thin progressive enhancement — nothing about the server-rendered, multi-tenant request flow changes. A browser that ignores the manifest/service worker still gets the full app.

## Moving parts

| File | Role |
|------|------|
| [public/assets/pwa/manifest.json](../public/assets/pwa/manifest.json) | Web app manifest source — app identity (name, `short_name`, `start_url`, `scope`, `display: standalone`, `theme_color`, `background_color`) and the icon set. |
| [routes/web.php](../routes/web.php) (`pwa.manifest` route) | Serves the manifest at `/manifest.webmanifest` **through PHP** so the `Content-Type` is always `application/manifest+json` — see the MIME note below. |
| [public/sw.js](../public/sw.js) | Service worker — caching strategy + offline fallback. Registered at scope `/`. |
| [public/offline.html](../public/offline.html) | Branded fallback page shown when a navigation fails with no network. |
| [public/assets/img/pwa/](../public/assets/img/pwa/) | Install icons: `icon-192.png`, `icon-512.png` (purpose `any`), `icon-maskable-512.png` (purpose `maskable`, full-bleed safe zone), `apple-touch-icon.png` (180×180, iOS). |
| [resources/views/layouts/partials/pwa-head.blade.php](../resources/views/layouts/partials/pwa-head.blade.php) | Single source of the `<head>` wiring: manifest link, `theme-color`, Apple meta tags, icon links, and the service-worker registration script. |

## How it attaches to the app

`pwa-head.blade.php` is `@include`d in every layout `<head>`, right after the favicon link, so the manifest and service worker are present on every surface:

- [layouts/app.blade.php](../resources/views/layouts/app.blade.php) — admin / tenant portal
- [layouts/employee-portal.blade.php](../resources/views/layouts/employee-portal.blade.php) — POS panel
- [layouts/public.blade.php](../resources/views/layouts/public.blade.php) — landing page
- [layouts/customer-portal.blade.php](../resources/views/layouts/customer-portal.blade.php) — customer portal
- [auth/layout.blade.php](../resources/views/auth/layout.blade.php) — auth pages

Keeping it in one partial means the manifest/SW/icon markup lives in exactly one place; the five layouts only carry the one-line include.

## Service worker caching strategy

The strategy is deliberately conservative because the app is **server-rendered, auth-gated, and multi-tenant** — caching HTML would risk serving one tenant's page to another. See the comments in [public/sw.js](../public/sw.js):

| Request | Strategy | Why |
|---------|----------|-----|
| `/assets/*`, `/build/*` (GET) | **Stale-while-revalidate** | Static, content-addressable assets — fast loads, self-healing in the background. |
| Navigations (`mode: 'navigate'`) | **Network-first**, fall back to `/offline.html` only when the network is unreachable | Always fetch fresh, tenant-correct HTML; **HTML is never written to the cache.** |
| Non-GET (POST/PUT/…) | Passthrough | Never intercepted — checkout, payments, form posts hit the network directly. |

Cache versioning is via the `VERSION` constant in `sw.js`; bumping it drops stale caches on the next `activate`.

## Installability checklist

For the browser install prompt to appear, all of these must hold (they do, in production):

- Served over **HTTPS** (or `localhost` for local testing — the prompt will **not** appear on plain `http://`).
- Manifest linked, with `name`/`short_name`, a `192px` and a `512px` icon, `start_url`, and `display: standalone`.
- A registered service worker controlling the scope.

## Browser support & where to find "Install"

The app installs on every desktop and mobile OS, but each browser hides the install entry point in a different place — and Safari uses its own mechanism (no manifest required). The one real gap is **Firefox on desktop**, which has no install feature on stable; it still runs the app fine as a normal tab (manifest + service worker are simply ignored).

| Browser | Where the user clicks to install | Notes |
|---------|----------------------------------|-------|
| **Chrome / Edge / Opera / Brave** (desktop) | **Install icon in the address bar**, or ⋮ menu → *Install…* | Requires **HTTPS or `localhost`** — does **not** appear on plain `http://` or `.test` domains. |
| **Chrome / Samsung Internet / Opera** (Android) | **⋮ menu → Install app / Add to Home Screen** | Full PWA. |
| **Firefox** (Android) | **⋮ menu → Add to Home Screen** | Supported on mobile. |
| **Firefox** (desktop) | — *not supported* | Runs as a normal website; no standalone install on stable. |
| **Safari** (macOS 14+/Safari 17+) | **Share button → Add to Dock**, or **File → Add to Dock** | No address-bar icon. Works on any origin, including `.test` over HTTP — no manifest/SW required. |
| **Safari** (iOS / iPadOS) | **Share → Add to Home Screen** | Runs standalone; service workers since iOS 11.3. |

> **`.test` / local domains:** a `.test` host over plain HTTP is neither `https://` nor `localhost`, so **Chromium browsers will not show the install icon there**. Test Chromium installs via `http://localhost:8000` (`php artisan serve`) or a real HTTPS domain. Safari's *Add to Dock* has no such restriction.

## Manifest MIME type (why it's a route, not a static file)

The manifest **must** be served with `Content-Type: application/manifest+json`. Many web servers (including **Valet/nginx**) don't map the `.webmanifest` extension and fall back to `application/octet-stream`, which **Safari rejects** — the page then isn't treated as installable. To make this correct on every server, the manifest is served through the `pwa.manifest` route in [routes/web.php](../routes/web.php) (reading [public/assets/pwa/manifest.json](../public/assets/pwa/manifest.json)) with an explicit `Content-Type` header, rather than as a static `public/manifest.webmanifest` file.

Verify any environment with:

```bash
curl -skI https://your-host/manifest.webmanifest | grep -i content-type
# expect: content-type: application/manifest+json
```

## Local HTTPS (Valet)

PWA install needs a secure origin. With Valet, secure the site once:

```bash
valet secure <site>      # serves it as https://<site>.test with a trusted cert
# undo later: valet unsecure <site>
```

Then load `https://<site>.test` (Safari requires HTTPS to register the service worker; Chrome requires HTTPS or `localhost` to show the install icon). After changing the manifest, **hard-reload** (Safari caches it aggressively): ⌘ + Option + R.

## Regenerating / rebranding the icons

The icons are placeholder "POS" tiles on an indigo→purple gradient (`#4f46e5`→`#7367f0`, matching the manifest `theme_color`). To rebrand, replace the PNGs in [public/assets/img/pwa/](../public/assets/img/pwa/) keeping the **same filenames and sizes**, and update `theme_color` in [manifest.json](../public/assets/pwa/manifest.json) plus `theme-color` in [pwa-head.blade.php](../resources/views/layouts/partials/pwa-head.blade.php) if the brand color changes. The maskable icon must keep its important content within the central ~80% safe zone.

## Testing locally

```bash
php artisan serve --port=8000
# then visit http://127.0.0.1:8000 in Chrome
# DevTools → Application → Manifest / Service Workers to inspect
# DevTools → Lighthouse → "Installable" audit
```

The address-bar install icon and `chrome://apps` entry confirm a successful install.
