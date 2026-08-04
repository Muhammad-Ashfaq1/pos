<!doctype html>
<html
    lang="en"
    class="layout-wide customizer-hide"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="{{ asset('assets') }}/"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title') — {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
<body>
    <div class="container-xxl container-p-y">
        <div class="misc-wrapper">
            <div class="app-brand justify-content-center mb-6">
                <a href="{{ url('/') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        @include('layouts.partials.brand-logo', ['size' => 36])
                    </span>
                    <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('app.name') }}</span>
                </a>
            </div>

            @hasSection('code')
                <h1 class="mb-2 mx-2" style="line-height: 6rem; font-size: 6rem;">@yield('code')</h1>
            @endif

            <h4 class="mb-2 mx-2">@yield('heading')</h4>
            <p class="mb-6 mx-2">@yield('message')</p>

            <a href="@yield('action_url', url('/'))" class="btn btn-primary @yield('button_class', 'mb-10')">
                @yield('action_label', 'Back to home')
            </a>

            @hasSection('illustration')
                <div class="@yield('illustration_class', 'mt-4')">
                    <img
                        src="@yield('illustration')"
                        alt="@yield('heading')"
                        width="@yield('illustration_width', 225)"
                        class="img-fluid" />
                </div>
            @endif
        </div>
    </div>

    <div class="container-fluid misc-bg-wrapper @yield('bg_wrapper_class')">
        <img
            src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}"
            height="355"
            alt=""
            data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" />
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
