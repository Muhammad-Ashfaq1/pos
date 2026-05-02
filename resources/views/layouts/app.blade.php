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

  if (str_starts_with($routeName, 'tenant.settings.')) {
      $bodyClasses .= ' layout-menu-collapsed ';
  }

  if ($isEmployeePanel) {
      $bodyClasses .= ' employee-panel ';
  }

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
  data-bs-theme="light"
  data-assets-path="{{ asset('assets') }}/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <script>
      (function () {
        const theme = localStorage.getItem('templateCustomizer-vertical-menu-template--Theme') || 'light';
        const themeToApply = theme === 'system' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
        document.documentElement.setAttribute('data-bs-theme', themeToApply);
      })();
    </script>
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />


    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />

    <!-- endbuild -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-3.2.8.min.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    {{-- <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script> --}}

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('assets/js/config.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
    @stack('page-style')
    <title>@yield('title', config('app.name', 'Oil Change POS'))</title>

  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
      <div class="layout-container">
        <!-- Menu -->

        @include($isEmployeePanel ? 'employee.partials.sidebar' : 'layouts.partials.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
        @include($isEmployeePanel ? 'employee.partials.navbar' : 'layouts.partials.navbar')

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="{{ $contentContainerClass }}">

            @yield('content')
            </div>
            <!-- / Content -->

           @unless($isEmployeePanel)
             @include('layouts.partials.footer')
           @endunless

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->

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

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <!-- Main JS -->

    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
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
    <script src="{{ asset('assets/js/app-helpers.js') }}"></script>
    <script src="{{ asset('assets/js/session-notifications.js') }}"></script>
    @stack('page-script')
    @yield('scripts')

  </body>
</html>
