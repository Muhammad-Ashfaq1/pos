@php
  $authUser = auth()->user();
  $routeName = request()->route()?->getName() ?? '';
  $panelContext = trim($__env->yieldContent('panel_context'));
    $employeeChromePatterns = [
        'employee.*',
        'tenant.ecommerce.products.*',
        'tenant.ecommerce.services.*',
        'tenant.ecommerce.customers.*',
        'tenant.ecommerce.vehicles.*',
    ];
  $isEmployeePanel = $panelContext === 'employee'
      || (($authUser?->isEmployee() ?? false)
          && collect($employeeChromePatterns)->contains(
              fn (string $pattern): bool => str($routeName)->is($pattern)
          ));
  $bodyClasses = ' layout-navbar-fixed layout-menu-fixed layout-compact ';

  if ($isEmployeePanel) {
      $bodyClasses .= ' employee-panel ';
  }

  $posTheme = \App\Support\AppTheme::resolve($authUser);
  $bodyClasses .= ' '.$posTheme['classes'].' ';

  $contentContainerClass = trim($__env->yieldContent('content_container_class')) ?: ($isEmployeePanel
      ? 'container-fluid flex-grow-1 container-p-y'
      : 'container-xxl flex-grow-1 container-p-y');
@endphp

<!doctype html>

<html
  lang="en"
  class="{{ trim($bodyClasses) }}"
  dir="ltr"
  data-skin="default"
  data-bs-theme="{{ $posTheme['bs_theme'] }}"
  data-pos-theme="{{ $posTheme['variant'] }}"
  data-pos-theme-mode="{{ $posTheme['mode'] }}"
  data-assets-path="{{ asset('assets') }}/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <script>
      (function () {
        // Sidebar collapse only — theme comes from AppTheme + _theme-prepaint.
        const templateName = 'vertical-menu-template';
        const collapsed = localStorage.getItem('templateCustomizer-' + templateName + '--LayoutCollapsed');
        if (collapsed !== null) {
          if (collapsed === 'true') {
            document.documentElement.classList.add('layout-menu-collapsed');
          } else {
            document.documentElement.classList.remove('layout-menu-collapsed');
          }
        }
      })();
    </script>
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pos-table-scope" content="{{ \App\Support\TableFragment::scopeToken() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    @include('layouts.partials.pwa-head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-themes.css') }}?v={{ filemtime(public_path('assets/css/pos-themes.css')) }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-datepicker.css') }}?v={{ filemtime(public_path('assets/css/app-datepicker.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-3.2.8.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/app-loader.css') }}?v={{ filemtime(public_path('assets/css/app-loader.css')) }}" />

    <style>
      @php
        $shopBrandPrimary = app(\App\Support\Tenancy\TenantContext::class)->current()?->brandPrimaryColor();
      @endphp
      @include('partials._shop-brand-tokens')

      /* Theme customizer panel (gear) disabled — use navbar Light/Dark/System only */
      #template-customizer {
        display: none !important;
      }

      .shop-brand-logo img {
        display: block;
      }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    {{-- template-customizer.js kept for layout localStorage sync; gear UI is hidden --}}
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    @stack('styles')
    @stack('page-style')

    <link rel="stylesheet" href="{{ asset('assets/css/pos-responsive.css') }}?v={{ filemtime(public_path('assets/css/pos-responsive.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-table.css') }}?v={{ filemtime(public_path('assets/css/pos-table.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-menu.css') }}?v={{ filemtime(public_path('assets/css/pos-menu.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos-navbar.css') }}?v={{ filemtime(public_path('assets/css/pos-navbar.css')) }}" />
    @include('partials._theme-prepaint')
    <script src="{{ asset('assets/js/pos-theme.js') }}?v={{ filemtime(public_path('assets/js/pos-theme.js')) }}"></script>
    <script src="{{ asset('assets/js/pos-theme-bridge.js') }}?v={{ filemtime(public_path('assets/js/pos-theme-bridge.js')) }}"></script>

    <title>@yield('title', config('app.name', 'Oil Change POS'))</title>
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar  ">
      <div class="layout-container">
        @include($isEmployeePanel ? 'employee.partials.sidebar' : 'layouts.partials.sidebar')

        <div class="layout-page">
        @include($isEmployeePanel ? 'employee.partials.navbar' : 'layouts.partials.navbar')

          <div class="content-wrapper">
            <div class="{{ $contentContainerClass }}">
            @yield('content')
            </div>

           @unless($isEmployeePanel)
             @include('layouts.partials.footer')
           @endunless

            <div class="content-backdrop fade"></div>
          </div>
        </div>
      </div>

      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>
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

    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
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
