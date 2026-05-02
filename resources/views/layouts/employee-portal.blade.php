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
            text-decoration: none;
        }

        .employee-admin-preview .preview-brand-text {
            font-size: 2.25rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0.04em;
            color: var(--preview-indigo-dark);
        }

        .employee-admin-preview .preview-brand-text span {
            color: var(--preview-amber);
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

        .employee-admin-preview .preview-main {
            padding-bottom: 8rem;
        }

        .employee-admin-preview .preview-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }

        .employee-admin-preview .preview-left-column,
        .employee-admin-preview .preview-right-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
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

        .employee-admin-preview .preview-chip:hover,
        .employee-admin-preview .preview-tile:hover {
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
        }

        .employee-admin-preview .preview-tile {
            min-height: 9.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .employee-admin-preview .preview-tile-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            }
        }
    </style>

    @stack('styles')
</head>
<body class="employee-admin-preview">
    <div class="preview-shell">
        <header class="preview-header">
            <div class="preview-container py-0">
                <div class="preview-header-inner">
                    <a href="{{ route('employee.dashboard') }}" class="preview-brand">
                        <span class="preview-brand-text">OIL<span>POS</span></span>
                    </a>

                    <div class="preview-header-actions">
                        <button type="button" class="preview-circle-btn preview-circle-btn--indigo">
                            <i class="ti tabler-bell"></i>
                        </button>
                        <button type="button" class="preview-circle-btn preview-circle-btn--slate">
                            <i class="ti tabler-user"></i>
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="preview-circle-btn preview-circle-btn--red" title="Logout">
                                <i class="ti tabler-logout"></i>
                            </button>
                        </form>
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
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('page-script')
</body>
</html>
