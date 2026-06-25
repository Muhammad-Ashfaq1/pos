{{-- PWA: web app manifest, theme color, install icons, and service worker registration. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}" />
<meta name="theme-color" content="#4f46e5" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="POS" />
<link rel="apple-touch-icon" href="{{ asset('assets/img/pwa/apple-touch-icon.png') }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/pwa/icon-192.png') }}" />
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/img/pwa/icon-512.png') }}" />
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }).catch(function (err) {
        console.warn('Service worker registration failed:', err);
      });
    });
  }
</script>
