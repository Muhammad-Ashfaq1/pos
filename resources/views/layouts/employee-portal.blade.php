@php
    $posTheme = \App\Support\AppTheme::resolve(auth()->user());
@endphp
<!doctype html>
<html
    lang="en"
    class="layout-wide {{ $posTheme['classes'] }}"
    dir="ltr"
    data-skin="default"
    data-bs-theme="{{ $posTheme['bs_theme'] }}"
    data-pos-theme="{{ $posTheme['variant'] }}"
    data-pos-theme-mode="{{ $posTheme['mode'] }}"
    data-assets-path="{{ asset('assets') }}/"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pos-table-scope" content="{{ \App\Support\TableFragment::scopeToken() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="@yield('meta_description', 'Employee Portal UI preview for the POS app.')" />
    <title>@yield('title', 'Employee Portal')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    @include('layouts.partials.pwa-head')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-themes.css') }}?v={{ filemtime(public_path('assets/css/pos-themes.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-datepicker.css') }}?v={{ filemtime(public_path('assets/css/app-datepicker.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-loader.css') }}?v={{ filemtime(public_path('assets/css/app-loader.css')) }}" />
    <style>
        @php
            $shopBrandPrimary = app(\App\Support\Tenancy\TenantContext::class)->current()?->brandPrimaryColor();
        @endphp
        @include('partials._shop-brand-tokens')

        /* Theme customizer panel (gear) disabled — use header Light/Dark/System only */
        #template-customizer {
            display: none !important;
        }

        .shop-brand-logo img {
            display: block;
        }
    </style>

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    @yield('extra-css')
    @stack('extra-css')

    <style>
        body.employee-admin-preview {
            --preview-page: #f8f8fc;
            --preview-card: #ffffff;
            --preview-border: #c7d2fe;
            /* --preview-indigo: #4338ca;
            --preview-indigo-dark: #312e81; */
            --preview-indigo: #312e81;
            --preview-indigo-dark: #262363;
            --preview-muted: #64748b;
            --preview-slate-light: #94a3b8;
            --preview-amber: #fbbf24;
            --preview-amber-soft: #fef3c7;
            --preview-blue-soft: #dbeafe;
            --preview-purple-soft: #eedcff;
            --preview-violet-soft: #ddd6fe;
            margin: 0;
            min-height: 100vh;
            font-family: 'Public Sans', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(165, 180, 252, 0.18), transparent 22%),
                linear-gradient(180deg, #fafafd 0%, #f5f6fb 100%);
        }

        :root, [data-bs-theme=light] {
            --bs-primary: #312e81;
            --bs-primary-rgb: 49, 46, 129;
            --bs-link-color: #312e81;
            --bs-link-hover-color: #262363;
        }

        .btn-primary {
            --bs-btn-bg: #312e81;
            --bs-btn-border-color: #312e81;
            --bs-btn-hover-bg: #28256a;
            --bs-btn-hover-border-color: #262363;
            --bs-btn-active-bg: #262363;
            --bs-btn-active-border-color: #23215d;
        }

        .text-primary {
            color: #312e81 !important;
        }

        @if ($shopBrandPrimary)
            @php
                $brandRgb = sscanf($shopBrandPrimary, '#%02x%02x%02x');
            @endphp
            :root, [data-bs-theme=light] {
                --bs-primary: {{ $shopBrandPrimary }};
                --bs-primary-rgb: {{ $brandRgb[0] }}, {{ $brandRgb[1] }}, {{ $brandRgb[2] }};
                --bs-link-color: {{ $shopBrandPrimary }};
                --bs-link-hover-color: {{ $shopBrandPrimary }};
            }

            .btn-primary {
                --bs-btn-bg: {{ $shopBrandPrimary }};
                --bs-btn-border-color: {{ $shopBrandPrimary }};
                --bs-btn-hover-bg: {{ $shopBrandPrimary }};
                --bs-btn-hover-border-color: {{ $shopBrandPrimary }};
                --bs-btn-active-bg: {{ $shopBrandPrimary }};
                --bs-btn-active-border-color: {{ $shopBrandPrimary }};
            }

            .text-primary {
                color: {{ $shopBrandPrimary }} !important;
            }

            body.employee-admin-preview {
                --preview-indigo: {{ $shopBrandPrimary }};
                --preview-indigo-dark: {{ $shopBrandPrimary }};
            }
        @endif

        .employee-admin-preview .preview-shell {
            min-height: 100vh;
        }

        .employee-admin-preview .preview-header {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid rgba(199, 210, 254, 0.9);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
        }

        .employee-admin-preview .preview-container {
            /* max-width: 1280px; */
            margin: 0 auto;
            padding: 0rem 1rem;
        }

        .employee-admin-preview .preview-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .employee-admin-preview .preview-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            min-width: 0;
        }

        .employee-admin-preview .preview-brand .shop-brand-logo img {
            width: 40px;
            height: 40px;
        }

        .employee-admin-preview .preview-brand-text {
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0.01em;
            color: var(--shop-brand-primary, var(--preview-indigo-dark));
            max-width: min(42vw, 18rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .employee-admin-preview .preview-header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .employee-admin-preview .preview-circle-btn {
            width: 2.6rem;
            height: 2.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 0;
            font-size: 1.2rem;
            text-decoration: none;
        }

        .employee-admin-preview .preview-circle-btn--indigo {
            background: #eef2ff;
            color: var(--preview-indigo);
        }

        .employee-admin-preview .preview-circle-btn--slate {
            background: #f1f5f9;
            color: #475569;
        }

        .employee-admin-preview .preview-circle-btn--red {
            background: #fef2f2;
            color: #f87171;
        }

        .employee-admin-preview .impersonation-banner {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1;
        }

        .employee-admin-preview .impersonation-banner > i {
            font-size: 1.1rem;
            color: #d97706;
        }

        .employee-admin-preview .impersonation-banner-text {
            white-space: nowrap;
        }

        .employee-admin-preview .impersonation-banner-text strong {
            font-weight: 700;
        }

        .employee-admin-preview .impersonation-banner-stop {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: #d97706;
            color: #fff !important;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: background-color 0.15s ease;
        }

        .employee-admin-preview .impersonation-banner-stop:hover {
            background: #b45309;
            color: #fff !important;
        }

        .employee-admin-preview .impersonation-banner-stop i {
            font-size: 0.95rem;
        }

        @media (max-width: 767px) {
            .employee-admin-preview .impersonation-banner-text {
                display: none;
            }
        }

        .employee-admin-preview .preview-main {
            padding-top: 0.75rem;
            padding-bottom: 8rem;
        }

        .employee-admin-preview .preview-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem;
        }

        .employee-admin-preview .preview-left-column,
        .employee-admin-preview .preview-right-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-top: 2rem;
        }

        .employee-admin-preview .preview-card {
            border: 1px solid var(--preview-border);
            border-radius: 1.4rem;
            background: var(--preview-card);
            box-shadow: 0 10px 26px rgba(67, 56, 202, 0.04);
            overflow: hidden;
        }

        .employee-admin-preview .preview-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(199, 210, 254, 0.55);
        }

        .employee-admin-preview .preview-card-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .employee-admin-preview .preview-card-body {
            padding: 1rem 1.25rem 1.25rem;
        }

        .employee-admin-preview .preview-card-tools {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .employee-admin-preview .preview-select {
            min-width: 10rem;
            border: 1px solid var(--preview-border);
            border-radius: 0.8rem;
            background: #fff;
            padding: 0.6rem 0.85rem;
            font-size: 0.88rem;
            color: #334155;
        }

        .employee-admin-preview .preview-updated {
            text-align: right;
        }

        .employee-admin-preview .preview-updated-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--preview-indigo);
        }

        .employee-admin-preview .preview-updated-time {
            display: block;
            font-size: 0.9rem;
            color: #475569;
        }

        .employee-admin-preview .preview-refresh-btn {
            width: 2.55rem;
            height: 2.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--preview-border);
            border-radius: 999px;
            background: #eef2ff;
            color: var(--preview-indigo);
            font-size: 1.25rem;
        }

        .employee-admin-preview .preview-status-dot {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 999px;
            background: #d4d4d8;
            margin-top: 0.9rem;
        }

        .employee-admin-preview .preview-stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.9rem;
        }

        .employee-admin-preview .preview-chip {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            border-radius: 1.2rem;
            padding: 1.35rem;
            min-height: 6rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .employee-admin-preview .preview-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(67, 56, 202, 0.08);
        }

        .employee-admin-preview .preview-chip--blue {
            background: linear-gradient(135deg, var(--preview-blue-soft) 0%, #bfdbfe 100%);
        }

        .employee-admin-preview .preview-chip--purple {
            background: linear-gradient(135deg, #f3e8ff 0%, #d8b4fe 100%);
        }

        .employee-admin-preview .preview-chip--violet {
            background: linear-gradient(135deg, #ede9fe 0%, #a5b4fc 100%);
        }

        .employee-admin-preview .preview-chip-number-row {
            display: flex;
            align-items: flex-end;
            gap: 0.45rem;
        }

        .employee-admin-preview .preview-chip-value {
            font-size: 2.05rem;
            font-weight: 800;
            line-height: 1;
            color: #374151;
        }

        .employee-admin-preview .preview-chip-label {
            font-size: 1.05rem;
            font-weight: 600;
            color: #374151;
            padding-bottom: 0.18rem;
        }

        .employee-admin-preview .preview-chip-meta {
            margin-top: 0.7rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: rgba(71, 85, 105, 0.9);
        }

        .employee-admin-preview .preview-chip-icon {
            font-size: 2.35rem;
            color: rgba(67, 56, 202, 0.72);
        }

        .employee-admin-preview .preview-operations-card {
            min-height: 20.5rem;
        }

        .employee-admin-preview .preview-operation-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        }

        .employee-admin-preview .preview-operation-item:last-child {
            border-bottom: 0;
        }

        .employee-admin-preview .preview-operation-main {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .employee-admin-preview .preview-operation-icon {
            font-size: 1.3rem;
            color: var(--preview-amber);
        }

        .employee-admin-preview .preview-operation-label {
            font-size: 0.96rem;
            font-weight: 600;
            color: #334155;
        }

        .employee-admin-preview .preview-operation-link {
            font-size: 1.2rem;
            color: var(--preview-indigo);
        }

        .employee-admin-preview .preview-tiles-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
        }

        .employee-admin-preview .preview-tile {
            min-height: 9.7rem;
            height: auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .employee-admin-preview .preview-tile-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            padding-top: 0.35rem;
        }

        .employee-admin-preview .preview-tile-icon-wrap {
            width: 4rem;
            height: 4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            margin-bottom: 1rem;
            background: var(--preview-amber-soft);
            color: var(--preview-amber);
            font-size: 1.95rem;
        }

        .employee-admin-preview .preview-tile-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--preview-indigo);
        }

        /* Same lavender hover on every tile — icon/title styles unchanged */
        .employee-admin-preview .preview-tile:hover {
            background: #d2d1e1;
            border-color: #d2d1e1;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(67, 56, 202, 0.12);
        }

        .employee-admin-preview .preview-tile:focus-visible {
            outline: 2px solid var(--preview-indigo);
            outline-offset: 2px;
        }

        .employee-admin-preview .preview-bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 1.25rem;
            transform: translateX(-50%);
            z-index: 25;
            width: min(90%, 54rem);
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 0.5rem;
            padding: 0.7rem 0.9rem;
            border-radius: 1rem;
            border: 1px solid rgba(165, 180, 252, 0.9);
            background: rgba(224, 231, 255, 0.92);
            backdrop-filter: blur(20px);
            box-shadow: 0 24px 50px rgba(67, 56, 202, 0.18);
        }

        .employee-admin-preview .preview-bottom-link {
            min-width: 6rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            padding: 0.55rem 0.75rem;
            text-decoration: none;
            border-radius: 0.85rem;
            color: var(--preview-indigo);
            transition: background-color 0.2s ease;
        }

        .employee-admin-preview .preview-bottom-link:hover {
            background: rgba(255, 255, 255, 0.72);
        }

        .employee-admin-preview .preview-bottom-icon {
            font-size: 1.35rem;
        }

        .employee-admin-preview .preview-bottom-label {
            font-size: 0.86rem;
            font-weight: 600;
        }

        @media (min-width: 768px) {
            .employee-admin-preview .preview-container {
                padding: 0rem 1.5rem;
            }

            .employee-admin-preview .preview-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .employee-admin-preview .preview-stats-grid .preview-chip:last-child {
                max-width: 48%;
            }

            .employee-admin-preview .preview-tiles-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .employee-admin-preview .preview-container {
                padding: 1.35rem 1.75rem;
            }

            .employee-admin-preview .preview-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 1.5rem;
                align-items: start;
            }

            .employee-admin-preview .preview-left-column,
            .employee-admin-preview .preview-right-column {
                padding-top: 2.25rem;
            }
        }

        /* Dark mode — keep header light, darken main workspace (POS screens) */
        [data-bs-theme="dark"] body.employee-admin-preview {
            background: #25293c;
        }

        [data-bs-theme="dark"] .employee-admin-preview .preview-main {
            background: #25293c;
        }

        [data-bs-theme="dark"] .employee-admin-preview .preview-header {
            background: rgba(255, 255, 255, 0.96);
            border-bottom-color: rgba(199, 210, 254, 0.9);
        }

        [data-bs-theme="dark"] .employee-admin-preview .preview-brand-text {
            color: var(--preview-indigo-dark);
        }

        .employee-admin-preview .preview-user-toggle {
            padding: 0;
            overflow: hidden;
            border: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .employee-admin-preview .preview-user-toggle:focus,
        .employee-admin-preview .preview-user-toggle:active {
            border: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .employee-admin-preview .preview-user-toggle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
            border: 0;
            display: block;
        }
    </style>

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-responsive.css') }}?v={{ filemtime(public_path('assets/css/pos-responsive.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-table.css') }}?v={{ filemtime(public_path('assets/css/pos-table.css')) }}" />
    @include('partials._theme-prepaint')
    <script src="{{ asset('assets/js/pos-theme.js') }}?v={{ filemtime(public_path('assets/js/pos-theme.js')) }}"></script>
    <script src="{{ asset('assets/js/pos-theme-bridge.js') }}?v={{ filemtime(public_path('assets/js/pos-theme-bridge.js')) }}"></script>
</head>
<body class="employee-admin-preview">
    <div class="preview-shell">
        <header class="preview-header">
            <div class="preview-container py-0">
                <div class="preview-header-inner">
                    <a href="{{ route('employee.dashboard') }}" class="preview-brand">
                        @include('layouts.partials.shop-brand', [
                            'size' => 40,
                            'textClass' => 'preview-brand-text',
                        ])
                    </a>

                    <div class="preview-header-actions">
                        @if (session()->has('impersonator_id'))
                            <div class="impersonation-banner">
                                <i class="ti tabler-user-exclamation"></i>
                                <span class="impersonation-banner-text">
                                    Impersonating as <strong>{{ auth()->user()?->name }}</strong>
                                </span>
                                <a href="{{ route('admin.impersonate.stop') }}" class="impersonation-banner-stop">
                                    <i class="ti tabler-x"></i>
                                    <span>Stop</span>
                                </a>
                            </div>
                        @endif
                        @can('reports.view')
                            <a href="{{ route('employee.reports.index', 'sales') }}" class="preview-circle-btn preview-circle-btn--indigo" title="Reports">
                                <i class="ti tabler-chart-histogram"></i>
                            </a>
                        @endcan
                        {{-- Theme switcher disabled for employee panel for now
                        @include('layouts.partials.theme-switcher', [
                            'wrapperTag' => 'div',
                            'wrapperClass' => 'dropdown',
                            'themeTriggerClass' => 'preview-circle-btn preview-circle-btn--indigo dropdown-toggle hide-arrow',
                            'themeIconClass' => 'ti tabler-sun theme-icon-active',
                        ])
                        --}}
                        <button type="button" class="preview-circle-btn preview-circle-btn--indigo">
                            <i class="ti tabler-bell"></i>
                        </button>
                        @php
                            $navUser = auth()->user();
                            $navAvatarUrl = $navUser ? \App\Support\AccountSettings::avatarUrl($navUser) : null;
                        @endphp
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="preview-circle-btn preview-circle-btn--red" title="Logout">
                                <i class="ti tabler-logout"></i>
                            </button>
                        </form>
                        <a href="{{ route('account.profile') }}"
                           class="preview-circle-btn preview-circle-btn--slate preview-user-toggle"
                           title="Profile">
                            @if ($navAvatarUrl)
                                <img src="{{ $navAvatarUrl }}" alt="{{ $navUser?->name }}">
                            @else
                                <i class="ti tabler-user"></i>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="preview-main">
            <div class="preview-container py-0">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/app-datepicker.js') }}?v={{ filemtime(public_path('assets/js/app-datepicker.js')) }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        window.appCurrency = { symbol: @json(\App\Support\Currency::symbol()), code: @json(\App\Support\Currency::code()) };
        window.posThemeSaveUrl = @json(route('account.theme.update'));
    </script>
    <script>
        window.sessionMessages = window.sessionMessages || {};
        @if (session('success'))
            window.sessionMessages.success = @json(session('success'));
        @endif
        @if (session('error'))
            window.sessionMessages.error = @json(session('error'));
        @endif
        @if (session('info'))
            window.sessionMessages.info = @json(session('info'));
        @endif
        @if (session('warning'))
            window.sessionMessages.warning = @json(session('warning'));
        @endif
        @if (session('status'))
            window.sessionMessages.status = @json(session('status'));
        @endif
        @if (session('errors') && $errors->any())
            window.sessionMessages.errors = @json($errors->all());
        @endif
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <script src="{{ asset('assets/js/pos-confirm.js') }}?v={{ filemtime(public_path('assets/js/pos-confirm.js')) }}"></script>
    <script src="{{ asset('assets/js/app-helpers.js') }}?v={{ filemtime(public_path('assets/js/app-helpers.js')) }}"></script>
    <script src="{{ asset('assets/js/app-loader.js') }}?v={{ filemtime(public_path('assets/js/app-loader.js')) }}"></script>
    <script src="{{ asset('assets/js/session-notifications.js') }}"></script>
    <script src="{{ asset('assets/js/pos-table.js') }}?v={{ filemtime(public_path('assets/js/pos-table.js')) }}"></script>
    <script src="{{ asset('assets/js/pos-master-detail.js') }}?v={{ filemtime(public_path('assets/js/pos-master-detail.js')) }}"></script>

    @stack('page-script')
    @yield('scripts')
</body>
</html>
