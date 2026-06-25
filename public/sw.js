/**
 * Service worker for the Oil Change POS PWA.
 *
 * Goal: make the app installable and resilient to brief network blips —
 * NOT to aggressively cache a server-rendered, multi-tenant, auth-gated app
 * (that would risk serving one tenant's HTML to another). So:
 *   - Static assets  -> stale-while-revalidate (fast, self-healing).
 *   - Navigations    -> network-first, fall back to an offline page only when
 *                       the network is unreachable. Authenticated HTML is
 *                       never written to the cache.
 */
const VERSION = 'v1';
const STATIC_CACHE = `pos-static-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
  OFFLINE_URL,
  '/assets/img/pwa/icon-192.png',
  '/assets/img/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

function isStaticAsset(url) {
  return url.pathname.startsWith('/assets/') || url.pathname.startsWith('/build/');
}

self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Only handle GET; let the browser deal with POST/PUT/etc.
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  // Same-origin static assets: stale-while-revalidate.
  if (url.origin === self.location.origin && isStaticAsset(url)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then(async (cache) => {
        const cached = await cache.match(request);
        const network = fetch(request)
          .then((response) => {
            if (response && response.status === 200 && response.type === 'basic') {
              cache.put(request, response.clone());
            }
            return response;
          })
          .catch(() => cached);
        return cached || network;
      })
    );
    return;
  }

  // Page navigations: network-first, offline fallback. Never cache HTML.
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }
});
