<!doctype html>
<html
    lang="en"
    class="layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="{{ asset('assets') }}/"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <script>
        (function () {
            const theme = localStorage.getItem('templateCustomizer-vertical-menu-template--Theme') || 'light';
            const themeToApply = theme === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;
            document.documentElement.setAttribute('data-bs-theme', themeToApply);

            const collapsed = localStorage.getItem('templateCustomizer-vertical-menu-template--LayoutCollapsed');
            if (collapsed !== null) {
                if (collapsed === 'true') {
                    document.documentElement.classList.add('layout-menu-collapsed');
                } else {
                    document.documentElement.classList.remove('layout-menu-collapsed');
                }
            }
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title', 'My Account')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    @include('layouts.partials.pwa-head')

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-loader.css') }}?v={{ filemtime(public_path('assets/css/app-loader.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/customer-portal.css') }}?v={{ filemtime(public_path('assets/css/customer-portal.css')) }}" />
    <style>
        :root {
            --shop-brand-primary: {{ auth('customer')->user()?->tenant?->brandPrimaryColor() ?? \App\Models\Tenant::DEFAULT_BRAND_COLOR }};
        }
        .shop-brand-logo img { display: block; }
    </style>

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    @stack('styles')
</head>
<body class="customer-portal-page">
    @hasSection('portal-nav')
        <div class="cp-shell cp-shell--plain">
            @yield('portal-nav')
            <main class="cp-main">
                <div class="cp-container">
                    @yield('content')
                </div>
            </main>
        </div>
    @else
        @auth('customer')
            <div class="layout-wrapper layout-content-navbar">
                <div class="layout-container">
                    @include('customer.partials.sidebar')

                    <div class="layout-page">
                        @include('customer.partials.navbar')

                        <div class="content-wrapper">
                            <div class="container-xxl flex-grow-1 container-p-y">
                                @yield('content')
                            </div>
                            <div class="content-backdrop fade"></div>
                        </div>
                    </div>
                </div>

                <div class="layout-overlay layout-menu-toggle"></div>
                <div class="drag-target"></div>
            </div>
        @else
            <div class="cp-shell cp-shell--plain">
                <main class="cp-main">
                    <div class="cp-container">
                        @yield('content')
                    </div>
                </main>
            </div>
        @endauth
    @endif

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <script src="{{ asset('assets/js/app-loader.js') }}?v={{ filemtime(public_path('assets/js/app-loader.js')) }}"></script>
    <script>
        window.appCurrency = { symbol: @json(\App\Support\Currency::symbol()), code: @json(\App\Support\Currency::code()) };
        @if (session('success')) Notiflix.Notify.success(@json(session('success'))); @endif
        @if (session('error')) Notiflix.Notify.failure(@json(session('error'))); @endif
        @if ($errors->any()) Notiflix.Notify.failure(@json($errors->first())); @endif
    </script>
    <script src="{{ asset('assets/js/app-helpers.js') }}?v={{ filemtime(public_path('assets/js/app-helpers.js')) }}"></script>
    @stack('page-script')
</body>
</html>
