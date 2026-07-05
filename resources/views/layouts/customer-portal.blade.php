<!doctype html>
<html lang="en" data-bs-theme="light" data-assets-path="{{ asset('assets') }}/">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title', 'My Account')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    @include('layouts.partials.pwa-head')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-loader.css') }}?v={{ filemtime(public_path('assets/css/app-loader.css')) }}" />

    <style>
        :root, [data-bs-theme=light] { --bs-primary: #312e81; --bs-primary-rgb: 49, 46, 129; }
        body { font-family: 'Public Sans', sans-serif; background: linear-gradient(180deg, #fafafd 0%, #f3f4fb 100%); min-height: 100vh; }
        .btn-primary { --bs-btn-bg:#312e81; --bs-btn-border-color:#312e81; --bs-btn-hover-bg:#28256a; --bs-btn-hover-border-color:#262363; }
        .text-primary { color:#312e81 !important; }
        .portal-navbar { background:#fff; border-bottom:1px solid #e7e9f5; }
        .portal-brand { font-weight:900; letter-spacing:.04em; color:#262363; font-size:1.5rem; }
        .portal-brand span { color:#fbbf24; }
        .credit-hero { background:linear-gradient(135deg,#312e81,#4338ca); color:#fff; border-radius:1.25rem; }
        .nav-pills .nav-link.active { background:#312e81; }
    </style>
    @stack('styles')
</head>
<body>
    @hasSection('portal-nav')
        @yield('portal-nav')
    @else
        @auth('customer')
            <nav class="portal-navbar py-3 mb-4">
                <div class="container d-flex align-items-center justify-content-between">
                    <a href="{{ route('customer.dashboard') }}" class="text-decoration-none portal-brand d-inline-flex align-items-center gap-2">
                        @include('layouts.partials.brand-logo', ['size' => 34])
                        Auto<span>Serve</span>
                    </a>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small d-none d-sm-inline">{{ auth('customer')->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary"><i class="ti tabler-logout me-1"></i>Sign out</button>
                        </form>
                    </div>
                </div>
            </nav>

            <div class="container mb-3">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('customer.dashboard')) active @endif" href="{{ route('customer.dashboard') }}">Overview</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('customer.orders*')) active @endif" href="{{ route('customer.orders') }}">Service History</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('customer.credits')) active @endif" href="{{ route('customer.credits') }}">Store Credit</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('customer.profile')) active @endif" href="{{ route('customer.profile') }}">Profile</a></li>
                </ul>
            </div>
        @endauth
    @endif

    <div class="container pb-5">
        @yield('content')
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <script src="{{ asset('assets/js/app-loader.js') }}?v={{ filemtime(public_path('assets/js/app-loader.js')) }}"></script>
    <script>
        @if (session('success')) Notiflix.Notify.success(@json(session('success'))); @endif
        @if (session('error')) Notiflix.Notify.failure(@json(session('error'))); @endif
        @if ($errors->any()) Notiflix.Notify.failure(@json($errors->first())); @endif
    </script>
    @stack('page-script')
</body>
</html>
