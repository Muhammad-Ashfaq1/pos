@extends('layouts.app')

@section('title', $pageTitle ?? 'Settings')

@push('page-style')
    <link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}" />
@endpush

@section('content')
    <div class="row ps-4">
        @include('layouts.sections.menu.settingsMenu')

        <div class="col-md-8 col-lg-10">
            @hasSection('header-content')
                @yield('header-content')
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            @if(isset($partial))
                                @include($partial)
                            @else
                                @yield('content-body')
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @hasSection('footer-content')
                @yield('footer-content')
            @endif
        </div>
    </div>
@endsection

@hasSection('page-script-content')
    @push('page-script')
        @yield('page-script-content')
    @endpush
@endif
