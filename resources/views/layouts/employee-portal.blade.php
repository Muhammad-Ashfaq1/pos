<!doctype html>
<html
    lang="en"
    class="layout-wide"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="{{ asset('assets') }}/"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="@yield('meta_description', 'Employee Portal UI preview for the POS app.')" />
    <title>@yield('title', 'Employee Portal')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}" />

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        .employee-admin-preview {
            --employee-indigo-50: #eef2ff;
            --employee-indigo-100: #e0e7ff;
            --employee-indigo-300: #a5b4fc;
            --employee-indigo-600: #4f46e5;
            --employee-indigo-700: #4338ca;
            --employee-indigo-800: #3730a3;
            --employee-amber-100: #fef3c7;
            --employee-amber-400: #fbbf24;
            --employee-slate-200: #e2e8f0;
            --employee-slate-400: #94a3b8;
            --employee-slate-500: #64748b;
            --employee-slate-700: #334155;
        }

        .employee-admin-preview {
            background:
                radial-gradient(circle at top right, rgba(79, 70, 229, 0.06), transparent 24%),
                linear-gradient(180deg, #f8f7fb 0%, #f5f5f9 100%);
        }

        .employee-admin-preview .employee-preview-navbar {
            backdrop-filter: saturate(180%) blur(14px);
            background-color: rgba(255, 255, 255, 0.88);
            border-bottom: 1px solid rgba(47, 43, 61, 0.08);
        }

        .employee-admin-preview .employee-preview-shell {
            min-height: 100vh;
        }

        .employee-admin-preview .employee-preview-navbar .navbar-brand {
            font-size: 1rem;
        }

        .employee-admin-preview .employee-surface-card {
            border: 1px solid rgba(165, 180, 252, 0.45);
            box-shadow: 0 0.25rem 1rem rgba(47, 43, 61, 0.04);
        }

        .employee-admin-preview .employee-surface-card,
        .employee-admin-preview .employee-dashboard-chip,
        .employee-admin-preview .employee-tile-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .employee-admin-preview .employee-surface-card:hover,
        .employee-admin-preview .employee-dashboard-chip:hover,
        .employee-admin-preview .employee-tile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1rem 1.5rem rgba(47, 43, 61, 0.08);
        }

        .employee-admin-preview .employee-dashboard-chip {
            border-radius: 1rem;
            padding: 1.5rem;
            min-height: 5.9rem;
        }

        .employee-admin-preview .employee-dashboard-chip--primary {
            background: linear-gradient(135deg, rgba(224, 231, 255, 0.95), rgba(147, 197, 253, 0.85));
        }

        .employee-admin-preview .employee-dashboard-chip--secondary {
            background: linear-gradient(135deg, rgba(243, 232, 255, 0.95), rgba(196, 181, 253, 0.85));
        }

        .employee-admin-preview .employee-dashboard-chip--accent {
            background: linear-gradient(135deg, rgba(238, 242, 255, 0.95), rgba(165, 180, 252, 0.92));
        }

        .employee-admin-preview .employee-tile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .employee-admin-preview .employee-tile-card {
            border: 1px solid rgba(165, 180, 252, 0.45);
            min-height: 9.75rem;
        }

        .employee-admin-preview .employee-tile-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--employee-amber-100);
            color: var(--employee-amber-400);
            font-size: 1.5rem;
        }

        .employee-admin-preview .employee-tile-title,
        .employee-admin-preview .employee-preview-title,
        .employee-admin-preview .employee-preview-link,
        .employee-admin-preview .employee-updated-text {
            color: var(--employee-indigo-700);
        }

        .employee-admin-preview .employee-dashboard-chip i,
        .employee-admin-preview .employee-ops-icon {
            color: var(--employee-indigo-600);
        }

        .employee-admin-preview .employee-ops-item + .employee-ops-item {
            border-top: 1px solid rgba(47, 43, 61, 0.08);
        }

        .employee-admin-preview .employee-table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--employee-slate-400);
        }

        @media (max-width: 991.98px) {
            .employee-admin-preview .employee-tile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="employee-admin-preview">
    <div class="employee-preview-shell">
        <nav class="employee-preview-navbar navbar navbar-expand-lg">
            <div class="container-fluid px-4 px-lg-5">
                <a href="{{ route('employee.dashboard') }}" class="navbar-brand d-flex align-items-center gap-3 mb-0">
                    <span class="avatar bg-label-primary">
                        <i class="ti tabler-steering-wheel"></i>
                    </span>
                    <span>
                        <span class="fw-bold d-block text-body">{{ config('app.name', 'Oil Change POS') }}</span>
                        <small class="text-muted">Employee Portal Preview</small>
                    </span>
                </a>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <span class="badge bg-label-primary">UI Only</span>
                    <span class="badge bg-label-success">Vuexy Preview</span>
                </div>
            </div>
        </nav>

        <main class="container-fluid px-4 px-lg-5 py-4 py-lg-5">
            @yield('content')
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
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('page-script')
</body>
</html>
