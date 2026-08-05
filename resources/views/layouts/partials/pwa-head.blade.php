{{-- PWA: web app manifest, theme color, install icons, and service worker registration. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}" />
<meta name="theme-color" content="#4f46e5" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
<link rel="apple-touch-icon" href="{{ asset('assets/img/pwa/apple-touch-icon.png') }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/pwa/icon-192.png') }}" />
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/img/pwa/icon-512.png') }}" />
<script>
  (function () {
    if (!('serviceWorker' in navigator)) {
      return;
    }

    var pwaEnabled = @json((bool) config('app.pwa_enabled'));

    window.addEventListener('load', function () {
      if (!pwaEnabled) {
        // Remove any previously registered worker so offline.html cannot trap users.
        navigator.serviceWorker.getRegistrations().then(function (regs) {
          return Promise.all(regs.map(function (reg) { return reg.unregister(); }));
        }).then(function () {
          if (!('caches' in window)) {
            return;
          }

          return caches.keys().then(function (keys) {
            return Promise.all(keys.map(function (key) { return caches.delete(key); }));
          });
        }).catch(function () {});

        return;
      }

      navigator.serviceWorker.register(@json(asset('sw.js')), { scope: '/' }).catch(function (err) {
        console.warn('Service worker registration failed:', err);
      });
    });
  })();
</script>
