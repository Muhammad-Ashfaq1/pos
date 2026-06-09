<!doctype html>
<html lang="en" data-bs-theme="light" data-assets-path="{{ asset('assets') }}/">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title', 'Customer Portal')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        :root, [data-bs-theme=light] {
            --bs-primary: #312e81;
            --bs-primary-rgb: 49, 46, 129;
        }
        body { font-family: 'Public Sans', sans-serif; background: linear-gradient(180deg, #fafafd 0%, #f3f4fb 100%); min-height: 100vh; }
        .btn-primary { --bs-btn-bg:#312e81; --bs-btn-border-color:#312e81; --bs-btn-hover-bg:#28256a; --bs-btn-hover-border-color:#262363; }
        .text-primary { color:#312e81 !important; }
        .portal-navbar { background:#fff; border-bottom:1px solid #e7e9f5; }
        .portal-brand { font-weight:900; letter-spacing:.04em; color:#262363; font-size:1.5rem; }
        .portal-brand span { color:#fbbf24; }
        .credit-hero { background:linear-gradient(135deg,#312e81,#4338ca); color:#fff; border-radius:1.25rem; }
        .nav-pills .nav-link.active { background:#312e81; }
        .auth-card { max-width: 440px; margin: 6vh auto; }
        [data-loading] { display:none; }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.2/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <script>
        window.PORTAL = {
            apiBase: "{{ url('/api/v1/customer') }}",
            loginUrl: "{{ url('/portal/login') }}",
            dashboardUrl: "{{ url('/portal') }}",
        };
    </script>
    <script src="{{ asset('assets/js/customer/portal.js') }}"></script>
    @stack('page-script')
</body>
</html>
