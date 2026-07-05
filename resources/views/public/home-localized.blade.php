@extends('layouts.public')

@section('title', __('home.title'))
@section('meta_description', __('home.meta_description'))

@section('content')
@php
    $industryIcons = ['tabler-car-garage', 'tabler-droplet', 'tabler-wheel', 'tabler-spray', 'tabler-engine', 'tabler-gauge'];
    $featurePointIcons = ['tabler-receipt-2', 'tabler-car-garage', 'tabler-building-store'];
    $featureCardIcons = ['tabler-users-group', 'tabler-package', 'tabler-shield-check', 'tabler-bell-ringing'];
    $moduleIcons = [
        'tabler-building-store',
        'tabler-receipt-2',
        'tabler-package',
        'tabler-tool',
        'tabler-users',
        'tabler-car',
        'tabler-shield-lock',
        'tabler-award',
        'tabler-bell',
        'tabler-chart-bar',
        'tabler-device-desktop',
        'tabler-file-search',
    ];
    $testimonialAvatars = ['1.png', '4.png', '7.png'];
    $planBadgeClasses = ['bg-label-primary', 'bg-primary', 'bg-label-dark'];
    $businessTypeValues = [
        'Car garage / workshop',
        'Oil change / quick lube',
        'Tire & brake center',
        'Car wash & detailing',
        'Multi-service auto center',
        'Other',
    ];
@endphp

<nav class="navbar navbar-expand-lg landing-navbar sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="#home">
            <span class="brand-mark">O</span>
            <span>
                <span class="fw-bolder fs-4 text-heading d-block lh-1">OCC</span>
                <small class="text-muted">{{ __('home.brand_tagline') }}</small>
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="{{ __('menu.modules') }}">
            <span><i class="icon-base ti tabler-menu-2 fs-2 text-heading"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="#home">{{ __('menu.home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">{{ __('menu.features') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">{{ __('menu.services') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#modules">{{ __('menu.modules') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">{{ __('menu.how_it_works') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">{{ __('menu.faq') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">{{ __('menu.contact') }}</a></li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 mt-3 mt-lg-0">
                @include('layouts.partials.language-switcher')
                <a href="{{ route('login') }}" class="btn btn-label-secondary">{{ __('app.login') }}</a>
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('app.register_shop') }}</a>
            </div>
        </div>
    </div>
</nav>

<main>
    <section class="landing-section pt-5 pb-5" id="home">
        <div class="container">
            <div class="hero-shell p-4 p-lg-5">
                <span class="hero-glow"></span>
                <span class="hero-deco d-none d-lg-inline-flex" style="top: 2.5rem; right: 42%; animation-delay: .2s;"><i class="icon-base ti tabler-tool"></i></span>
                <span class="hero-deco d-none d-lg-inline-flex" style="bottom: 3rem; right: 46%; animation-delay: 1.1s;"><i class="icon-base ti tabler-droplet"></i></span>
                <span class="hero-deco d-none d-lg-inline-flex" style="top: 9rem; right: 4%; animation-delay: 1.8s;"><i class="icon-base ti tabler-settings"></i></span>
                <div class="row align-items-center g-4 g-xl-5 position-relative">
                    <div class="col-lg-7">
                        <span class="section-kicker mb-3 bg-white bg-opacity-10 border-0 text-white">
                            <i class="icon-base ti tabler-car-garage"></i>
                            {{ __('home.hero.kicker') }}
                        </span>

                        <h1 class="hero-title fw-bold text-white mb-3">
                            {!! __('home.hero.title_html') !!}
                        </h1>

                        <p class="hero-copy text-white text-opacity-75 mb-4 hero-subtext">
                            {{ __('home.hero.copy') }}
                        </p>

                        <div class="hero-chip-row mb-4">
                            @foreach (__('home.hero.chips') as $index => $chip)
                                @php($chipIcons = ['tabler-bolt', 'tabler-history', 'tabler-bell-ringing'])
                                <span class="hero-chip"><i class="icon-base ti {{ $chipIcons[$index] }}"></i> {{ $chip }}</span>
                            @endforeach
                        </div>

                        <div class="hero-actions d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">{{ __('app.register_shop') }}</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">{{ __('app.request_demo') }}</a>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-visual-card p-3 p-lg-4">
                            <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}" alt="{{ __('home.image_alt.platform_overview') }}" class="img-fluid d-block mx-auto" style="max-height: 360px;">

                            <div class="floating-panel" style="top: 1.5rem; right: 1rem;">
                                <div class="fw-semibold">{{ __('home.hero.today_title') }}</div>
                                <small class="text-muted">{{ __('home.hero.today_text') }}</small>
                            </div>

                            <div class="floating-panel" style="bottom: 1.5rem; left: 1rem;">
                                <div class="fw-semibold">{{ __('home.hero.campaigns_title') }}</div>
                                <small class="text-muted">{{ __('home.hero.campaigns_text') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-2 pt-0">
        <div class="container">
            <p class="text-center text-muted text-uppercase fw-semibold small mb-3" style="letter-spacing: .08em;">{{ __('home.industries.heading') }}</p>
            <div class="industry-strip">
                @foreach (__('home.industries.items') as $index => $label)
                    <span class="industry-chip">
                        <i class="icon-base ti {{ $industryIcons[$index] }}"></i>
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section has-deco" id="features">
        <span class="section-deco deco-spin" style="top: -2rem; right: -3rem; font-size: 16rem;"><i class="icon-base ti tabler-settings"></i></span>
        <span class="deco-dots" style="bottom: 3rem; left: 2%;"></span>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-bolt"></i>
                        {{ __('home.features.kicker') }}
                    </span>
                    <h2 class="fw-bolder mb-3">{{ __('home.features.title') }}</h2>
                    <p class="text-muted fs-5 mb-4">{{ __('home.features.body') }}</p>

                    <div class="d-flex flex-column gap-3">
                        @foreach (__('home.features.points') as $index => $point)
                            <div class="d-flex gap-3">
                                <span class="landing-icon"><i class="icon-base ti {{ $featurePointIcons[$index] }}"></i></span>
                                <div>
                                    <h6 class="mb-1">{{ $point['title'] }}</h6>
                                    <p class="text-muted mb-0">{{ $point['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        @foreach (__('home.features.cards') as $index => $card)
                            <div class="col-md-6">
                                <div class="landing-card">
                                    <span class="landing-icon mb-3"><i class="icon-base ti {{ $featureCardIcons[$index] }}"></i></span>
                                    <h5 class="mb-2">{{ $card['title'] }}</h5>
                                    <p class="text-muted mb-0">{{ $card['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section section-soft has-deco" id="services">
        <span class="section-deco deco-cyan deco-float" style="top: 4rem; left: -2.5rem; font-size: 13rem;"><i class="icon-base ti tabler-tool"></i></span>
        <span class="deco-road" style="top: 2.5rem; right: 8%; transform: rotate(-8deg);"></span>
        <span class="deco-blob" style="bottom: -5rem; right: -4rem;"></span>
        <div class="container">
            <div class="row align-items-start g-5">
                <div class="col-lg-4">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-tool"></i>
                        {{ __('home.services.kicker') }}
                    </span>
                    <h2 class="fw-bolder mb-3">{{ __('home.services.title') }}</h2>
                    <p class="text-muted fs-5 mb-0">{{ __('home.services.body') }}</p>
                </div>

                <div class="col-lg-8">
                    <div class="services-grid">
                        @foreach (__('home.services.items') as $service)
                            <div class="service-pill">
                                <span class="service-dot"></span>
                                <span class="fw-medium">{{ $service }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section has-deco" id="modules">
        <span class="section-deco deco-spin" style="bottom: -3rem; left: -3rem; font-size: 15rem;"><i class="icon-base ti tabler-gauge"></i></span>
        <span class="deco-dots" style="top: 2rem; right: 3%;"></span>
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-layout-grid"></i>
                    {{ __('home.modules.kicker') }}
                </span>
                <h2 class="fw-bolder mb-3">{{ __('home.modules.title') }}</h2>
                <p class="text-muted fs-5 mb-0">{{ __('home.modules.body') }}</p>
            </div>

            <div class="row g-3">
                @foreach (__('home.modules.cards') as $index => $module)
                    <div class="col-md-6 col-xl-4">
                        <div class="landing-card">
                            <span class="landing-icon mb-3"><i class="icon-base ti {{ $moduleIcons[$index] }}"></i></span>
                            <h5 class="mb-2">{{ $module['title'] }}</h5>
                            <p class="text-muted mb-0">{{ $module['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section section-soft has-deco" id="how-it-works">
        <span class="section-deco deco-cyan deco-float" style="top: 3rem; right: -2rem; font-size: 12rem;"><i class="icon-base ti tabler-route"></i></span>
        <span class="deco-blob" style="top: -4rem; left: -5rem;"></span>
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-route"></i>
                        {{ __('home.how.kicker') }}
                    </span>
                    <h2 class="fw-bolder mb-3">{{ __('home.how.title') }}</h2>
                    <p class="text-muted fs-5 mb-4">{{ __('home.how.body') }}</p>
                    <img src="{{ asset('assets/img/illustrations/girl-with-laptop-light.png') }}" alt="{{ __('home.image_alt.onboarding_workflow') }}" class="img-fluid">
                </div>

                <div class="col-lg-7">
                    <div class="landing-card">
                        @foreach (__('home.how.steps') as $index => $step)
                            <div class="timeline-step">
                                <span class="timeline-number">{{ $index + 1 }}</span>
                                <h5 class="mb-1">{{ $step['title'] }}</h5>
                                <p class="text-muted mb-0">{{ $step['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="landing-card">
                        <span class="section-kicker mb-3">
                            <i class="icon-base ti tabler-briefcase"></i>
                            {{ __('home.benefits.business_kicker') }}
                        </span>
                        <h3 class="fw-bolder mb-3">{{ __('home.benefits.business_title') }}</h3>
                        <div class="row g-3">
                            @foreach (__('home.benefits.business_items') as $benefit)
                                <div class="col-12">
                                    <div class="d-flex gap-3">
                                        <span class="landing-icon flex-shrink-0" style="width: 2.6rem; height: 2.6rem;"><i class="icon-base ti tabler-check"></i></span>
                                        <div class="fw-medium">{{ $benefit }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="landing-card">
                        <span class="section-kicker mb-3">
                            <i class="icon-base ti tabler-user-heart"></i>
                            {{ __('home.benefits.customer_kicker') }}
                        </span>
                        <h3 class="fw-bolder mb-3">{{ __('home.benefits.customer_title') }}</h3>
                        <div class="row g-3">
                            @foreach (__('home.benefits.customer_items') as $benefit)
                                <div class="col-12">
                                    <div class="d-flex gap-3">
                                        <span class="landing-icon flex-shrink-0" style="width: 2.6rem; height: 2.6rem;"><i class="icon-base ti tabler-star"></i></span>
                                        <div class="fw-medium">{{ $benefit }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section pt-0">
        <div class="container">
            <div class="stats-band">
                <span class="section-deco deco-spin" style="bottom: -4rem; left: 2rem; font-size: 13rem; color: #fff; opacity: .06;"><i class="icon-base ti tabler-settings"></i></span>
                <div class="row g-4 text-center">
                    @foreach (__('home.stats') as $stat)
                        <div class="col-md-3 stat-item">
                            <div class="display-6 fw-bolder">{{ $stat['value'] }}</div>
                            <div class="text-white text-opacity-75">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section section-soft">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-message-2-star"></i>
                    {{ __('home.social.kicker') }}
                </span>
                <h2 class="fw-bolder mb-3">{{ __('home.social.title') }}</h2>
                <p class="text-muted fs-5 mb-0">{{ __('home.social.body') }}</p>
            </div>

            <div class="row g-3">
                @foreach (__('home.social.testimonials') as $index => $testimonial)
                    <div class="col-lg-4">
                        <div class="testimonial-card">
                            <div class="quote-mark mb-2">"</div>
                            <p class="text-muted mb-4">{{ $testimonial['quote'] }}</p>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets/img/avatars/'.$testimonialAvatars[$index]) }}" class="rounded-circle" width="54" height="54" alt="{{ __('home.image_alt.testimonial_avatar') }}">
                                <div>
                                    <h6 class="mb-0">{{ $testimonial['name'] }}</h6>
                                    <small class="text-muted">{{ $testimonial['role'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section bg-white" id="plans">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-credit-card"></i>
                    {{ __('home.plans.kicker') }}
                </span>
                <h2 class="fw-bolder mb-3">{{ __('home.plans.title') }}</h2>
                <p class="text-muted fs-5 mb-0">{{ __('home.plans.body') }}</p>
            </div>

            <div class="row g-3 align-items-stretch">
                @foreach (__('home.plans.cards') as $index => $plan)
                    <div class="col-lg-4">
                        <div class="plan-card {{ $index === 1 ? 'featured' : '' }}">
                            <span class="badge {{ $planBadgeClasses[$index] }} mb-3">{{ $plan['badge'] }}</span>
                            <h4 class="fw-bolder">{{ $plan['title'] }}</h4>
                            <p class="text-muted">{{ $plan['text'] }}</p>
                            <h3 class="fw-bolder mb-1">{{ __('home.plans.contact_us') }}</h3>
                            <p class="text-muted mb-4">{{ __('home.plans.final_pricing') }}</p>
                            <div class="d-flex flex-column gap-3">
                                @foreach ($plan['features'] as $feature)
                                    <div>{{ $feature }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section" id="faq">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-4">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-help-circle"></i>
                        {{ __('home.faq.kicker') }}
                    </span>
                    <h2 class="fw-bolder mb-3">{{ __('home.faq.title') }}</h2>
                    <p class="text-muted fs-5 mb-0">{{ __('home.faq.body') }}</p>
                </div>

                <div class="col-lg-8">
                    <div class="faq-panel">
                        <div class="accordion" id="faqAccordion">
                            @foreach (__('home.faq.items') as $index => $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq-heading-{{ $index }}">
                                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faq-collapse-{{ $index }}">
                                            {{ $faq['q'] }}
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="faq-heading-{{ $index }}" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body text-muted">
                                            {{ $faq['a'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section pt-0" id="contact">
        <div class="container">
            @if (session('demo_success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="icon-base ti tabler-circle-check fs-4"></i>
                    <span>{{ session('demo_success') }}</span>
                </div>
            @endif

            <div class="contact-strip p-4 p-lg-4 mb-4">
                <span class="section-deco deco-float" style="bottom: -3rem; right: 1rem; font-size: 11rem; color: #fff; opacity: .07;"><i class="icon-base ti tabler-car"></i></span>
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="section-kicker mb-3 bg-white text-dark">
                            <i class="icon-base ti tabler-phone-call"></i>
                            {{ __('home.contact.kicker') }}
                        </span>
                        <h2 class="fw-bolder text-white mb-3">{{ __('home.contact.title') }}</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">{{ __('home.contact.body') }}</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-mail"></i></div>
                                    <div class="fw-semibold mb-1">{{ __('app.sales') }}</div>
                                    <div class="text-muted small">sales@oilchangepos.test</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-lifebuoy"></i></div>
                                    <div class="fw-semibold mb-1">{{ __('app.support') }}</div>
                                    <div class="text-muted small">support@oilchangepos.test</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-3 mt-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">{{ __('app.register_shop') }}</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">{{ __('app.request_demo') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-shell p-4 p-lg-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="section-kicker mb-3 bg-white text-dark">
                            <i class="icon-base ti tabler-megaphone"></i>
                            {{ __('home.contact.final_kicker') }}
                        </span>
                        <h2 class="fw-bolder text-white mb-3">{{ __('home.contact.final_title') }}</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">{{ __('home.contact.final_body') }}</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">{{ __('app.register_shop') }}</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">{{ __('app.request_demo') }}</a>
                            <a href="{{ route('login') }}" class="btn btn-label-light btn-lg">{{ __('app.login') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade" id="demoModal" tabindex="-1" aria-labelledby="demoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <span class="section-kicker mb-2"><i class="icon-base ti tabler-calendar-event"></i> {{ __('home.demo_modal.kicker') }}</span>
                    <h4 class="fw-bolder mb-1" id="demoModalLabel">{{ __('home.demo_modal.title') }}</h4>
                    <p class="text-muted mb-0">{{ __('home.demo_modal.body') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.close') }}"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('demo.request.store') }}" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="demo_name">{{ __('home.demo_modal.name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="demo_name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_business_name">{{ __('home.demo_modal.business_name') }}</label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror" id="demo_business_name" name="business_name" value="{{ old('business_name') }}">
                            @error('business_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_email">{{ __('app.email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="demo_email" name="email" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_phone">{{ __('app.phone') }}</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="demo_phone" name="phone" value="{{ old('phone') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="demo_business_type">{{ __('home.demo_modal.business_type') }}</label>
                            <select class="form-select @error('business_type') is-invalid @enderror" id="demo_business_type" name="business_type">
                                <option value="">{{ __('home.demo_modal.business_type_placeholder') }}</option>
                                @foreach (__('home.demo_modal.business_types') as $index => $label)
                                    @php($value = $businessTypeValues[$index])
                                    <option value="{{ $value }}" @selected(old('business_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('business_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="demo_message">{{ __('home.demo_modal.message') }}</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="demo_message" name="message" rows="3" placeholder="{{ __('home.demo_modal.message_placeholder') }}">{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 d-grid d-sm-flex gap-2 justify-content-sm-end">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-send me-1"></i>{{ __('home.demo_modal.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer-shell pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="brand-mark">O</span>
                    <div>
                        <div class="fw-bolder text-white">OCC</div>
                        <small>{{ __('home.footer.brand_tagline') }}</small>
                    </div>
                </div>
                <p class="mb-3">{{ __('home.footer.description') }}</p>
                <div class="d-flex gap-2">
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-facebook"></i></span>
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-linkedin"></i></span>
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-instagram"></i></span>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">{{ __('menu.product') }}</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#features">{{ __('menu.features') }}</a>
                    <a href="#services">{{ __('menu.services') }}</a>
                    <a href="#modules">{{ __('menu.modules') }}</a>
                    <a href="#how-it-works">{{ __('menu.how_it_works') }}</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">{{ __('menu.company') }}</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#faq">{{ __('menu.faq') }}</a>
                    <a href="#contact">{{ __('menu.contact') }}</a>
                    <a href="#plans">{{ __('menu.plans') }}</a>
                    <a href="#home">{{ __('menu.home') }}</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">{{ __('menu.access') }}</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('login') }}">{{ __('app.login') }}</a>
                    <a href="{{ route('register') }}">{{ __('app.register') }}</a>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#demoModal">{{ __('app.request_demo') }}</a>
                    <a href="#plans">{{ __('menu.pricing') }}</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">{{ __('menu.support') }}</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="mailto:support@oilchangepos.test">support@oilchangepos.test</a>
                    <a href="mailto:sales@oilchangepos.test">sales@oilchangepos.test</a>
                    <span>{{ __('home.footer.hours') }}</span>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
            <p class="mb-0">&copy; {{ now()->year }} OCC. {{ __('home.footer.rights') }}</p>
            <p class="mb-0">{{ __('home.footer.positioning') }}</p>
        </div>
    </div>
</footer>
@endsection

@section('scripts')
<script>
    (function () {
        var navbar = document.querySelector('.landing-navbar');
        if (navbar) {
            var onScroll = function () {
                navbar.classList.toggle('is-scrolled', window.scrollY > 24);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        var targets = document.querySelectorAll('.landing-card, .service-pill, .testimonial-card, .plan-card, .stats-band, .timeline-step');
        if (!('IntersectionObserver' in window) || !targets.length) {
            return;
        }
        targets.forEach(function (el) { el.classList.add('reveal'); });
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        targets.forEach(function (el) { observer.observe(el); });
    })();
</script>

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('demoModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });
</script>
@endif
@endsection
