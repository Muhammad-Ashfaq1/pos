@extends('layouts.public')

@section('title', 'AutoServe - POS & Operations Software for Car Garages, Oil Change & Auto Service Shops')
@section('meta_description', 'AutoServe is an all-in-one SaaS platform for car garages, oil change shops, tire & brake centers, detailing studios and auto service businesses — POS billing, inventory, vehicle & service history, reminders, loyalty, and staff roles in one place.')
@section('meta_keywords', 'auto repair shop software, garage management software, oil change POS software, quick lube software, tire shop POS system, auto service point of sale, vehicle service history software, workshop billing software, car detailing shop software, spare parts inventory software, mechanic shop management software, auto shop CRM, car service reminder software, brake shop software, multi tenant auto shop SaaS, POS for car garages, automotive workshop software, auto parts store POS')

@php
    $faqs = [
        ['q' => 'Is AutoServe only for oil change shops?', 'a' => 'AutoServe is designed mainly for oil change shops, quick lube counters, and similar quick automotive service businesses where repeat visits, consumables, service history, and fast billing matter.'],
        ['q' => 'Can I manage billing and inventory together?', 'a' => 'Yes. AutoServe is positioned as an all-in-one business platform that combines service billing, workshop products, consumables, and stock visibility into one operating flow.'],
        ['q' => 'Can my staff have different permissions?', 'a' => 'Yes. AutoServe is built with role-based access so owners, managers, technicians, cashiers, and other team members can operate with different permission levels.'],
        ['q' => 'Can customers view their history?', 'a' => 'The product direction includes customer-facing service and invoice visibility. Even where the full portal is still evolving, AutoServe is designed around better visit transparency and customer trust.'],
        ['q' => 'Do shops need admin approval?', 'a' => 'Yes. Shop registration is followed by platform admin review and approval before the tenant workspace is activated.'],
        ['q' => 'Can I manage walk-in and registered customers?', 'a' => 'Yes. AutoServe is intended to support real counter operations where businesses deal with both repeat registered customers and walk-in service traffic.'],
    ];
@endphp

@section('content')
<div class="scroll-progress" aria-hidden="true"><span id="scrollProgressBar"></span></div>
<nav class="navbar navbar-expand-lg landing-navbar sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="#home">
            @include('layouts.partials.brand-logo', ['size' => 44])
            <span>
                <span class="fw-bolder fs-4 text-heading d-block lh-1">AutoServe</span>
                <small class="text-muted">Integrated Automotive Solutions</small>
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span><i class="icon-base ti tabler-menu-2 fs-2 text-heading"></i></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#modules">Modules</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>

            <div class="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
                <a href="{{ route('login') }}" class="btn btn-label-secondary">Login</a>
                <a href="{{ route('register') }}" class="btn btn-warning">Register Your Shop</a>
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
                            Built For Garages & Auto Service Shops
                        </span>

                        <h1 class="hero-title fw-bold text-white mb-3">
                            Run your auto shop with <span class="text-gradient">faster billing</span>, cleaner service records, and stronger customer retention.
                        </h1>

                        <p class="hero-copy text-white text-opacity-75 mb-4 hero-subtext">
                            AutoServe is an all-in-one SaaS platform for car garages, oil change shops, tire &amp; brake centers, detailing studios, and quick auto service businesses — POS billing, inventory visibility, customer and vehicle history, reminders, loyalty, and staff access control in one place.
                        </p>

                        <div class="hero-chip-row mb-4">
                            <span class="hero-chip"><i class="icon-base ti tabler-bolt"></i> Faster front-desk billing</span>
                            <span class="hero-chip"><i class="icon-base ti tabler-history"></i> Full vehicle history</span>
                            <span class="hero-chip"><i class="icon-base ti tabler-bell-ringing"></i> Service reminders</span>
                        </div>

                        <div class="hero-actions d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">Request Demo</a>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-visual-card p-3 p-lg-4">
                            <div class="mockup-window" aria-hidden="true">
                                <div class="mockup-topbar">
                                    <span class="mockup-dot"></span>
                                    <span class="mockup-dot"></span>
                                    <span class="mockup-dot"></span>
                                    <span class="mockup-url"><i class="icon-base ti tabler-lock icon-xs"></i> autoserve.app/pos</span>
                                </div>
                                <div class="mockup-body">
                                    <div class="mockup-stats">
                                        <div class="mockup-stat">
                                            <small>Today's Sales</small>
                                            <div class="mockup-stat-value">$2,840</div>
                                            <span class="mockup-trend up"><i class="icon-base ti tabler-trending-up icon-xs"></i> +12%</span>
                                        </div>
                                        <div class="mockup-stat">
                                            <small>Jobs Billed</small>
                                            <div class="mockup-stat-value">27</div>
                                            <span class="mockup-trend up"><i class="icon-base ti tabler-trending-up icon-xs"></i> +4</span>
                                        </div>
                                        <div class="mockup-stat">
                                            <small>Due Reminders</small>
                                            <div class="mockup-stat-value">9</div>
                                            <span class="mockup-trend warn"><i class="icon-base ti tabler-bell icon-xs"></i> today</span>
                                        </div>
                                    </div>
                                    <div class="mockup-chart">
                                        <span style="--h: 42%"></span>
                                        <span style="--h: 68%"></span>
                                        <span style="--h: 54%"></span>
                                        <span style="--h: 82%"></span>
                                        <span style="--h: 60%"></span>
                                        <span style="--h: 92%"></span>
                                        <span style="--h: 74%"></span>
                                    </div>
                                    <div class="mockup-rows">
                                        <div class="mockup-row">
                                            <span class="mockup-row-icon"><i class="icon-base ti tabler-droplet"></i></span>
                                            <div class="mockup-row-text">
                                                <div class="mockup-row-title">Oil Change — Toyota Corolla</div>
                                                <div class="mockup-row-sub">5W-30 Synthetic · Filter replaced</div>
                                            </div>
                                            <span class="mockup-badge paid">Paid</span>
                                        </div>
                                        <div class="mockup-row">
                                            <span class="mockup-row-icon"><i class="icon-base ti tabler-wheel"></i></span>
                                            <div class="mockup-row-text">
                                                <div class="mockup-row-title">Tire Rotation — Honda Civic</div>
                                                <div class="mockup-row-sub">Alignment check included</div>
                                            </div>
                                            <span class="mockup-badge wip">In Bay</span>
                                        </div>
                                        <div class="mockup-row">
                                            <span class="mockup-row-icon"><i class="icon-base ti tabler-battery-charging"></i></span>
                                            <div class="mockup-row-text">
                                                <div class="mockup-row-title">Battery Service — Kia Sportage</div>
                                                <div class="mockup-row-sub">Loyalty discount applied</div>
                                            </div>
                                            <span class="mockup-badge paid">Paid</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="floating-panel" style="top: 2.4rem; right: -0.5rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="floating-panel-icon success"><i class="icon-base ti tabler-circle-check"></i></span>
                                    <div>
                                        <div class="fw-semibold">Repeat visit detected</div>
                                        <small class="text-muted">Full vehicle history loaded</small>
                                    </div>
                                </div>
                            </div>

                            <div class="floating-panel" style="bottom: 2rem; left: -0.5rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="floating-panel-icon info"><i class="icon-base ti tabler-bell-ringing"></i></span>
                                    <div>
                                        <div class="fw-semibold">Reminders queued</div>
                                        <small class="text-muted">9 customers due for service</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="pb-2 pt-0">
        <div class="container">
            <p class="text-center text-muted text-uppercase fw-semibold small mb-3" style="letter-spacing: .08em;">Built for every corner of the auto service market</p>
            @php
                $industries = [
                    ['icon' => 'tabler-car-garage', 'label' => 'Car Garages'],
                    ['icon' => 'tabler-droplet', 'label' => 'Oil Change'],
                    ['icon' => 'tabler-wheel', 'label' => 'Tire & Brake'],
                    ['icon' => 'tabler-spray', 'label' => 'Detailing'],
                    ['icon' => 'tabler-engine', 'label' => 'Workshops'],
                    ['icon' => 'tabler-gauge', 'label' => 'Quick Lube'],
                    ['icon' => 'tabler-settings', 'label' => 'Spare Parts'],
                    ['icon' => 'tabler-battery-charging', 'label' => 'Battery Service'],
                    ['icon' => 'tabler-car-crash', 'label' => 'Repair Centers'],
                ];
            @endphp
            <div class="industry-strip">
                <div class="industry-track">
                    @foreach ([0, 1] as $copy)
                        @foreach ($industries as $industry)
                            <span class="industry-chip" @if($copy === 1) aria-hidden="true" @endif>
                                <i class="icon-base ti {{ $industry['icon'] }}"></i>
                                {{ $industry['label'] }}
                            </span>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section has-deco" id="features">
        <span class="section-deco deco-spin" style="top: -2rem; right: -3rem; font-size: 16rem;"><i class="icon-base ti tabler-settings"></i></span>
        <span class="deco-dots" style="bottom: 3rem; left: 2%;"></span>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5" data-reveal="left">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-bolt"></i>
                        Why AutoServe
                    </span>
                    <h2 class="fw-bolder mb-3">The system that helps garages and auto service businesses move beyond manual handling.</h2>
                    <p class="text-muted fs-5 mb-4">
                        AutoServe is designed for car garages, oil change shops, tire and brake centers, and detailing studios still juggling paper slips, WhatsApp follow-ups, disconnected stock notes, or basic billing tools. It gives owners and managers a professional way to run daily service operations.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-3">
                            <span class="landing-icon"><i class="icon-base ti tabler-receipt-2"></i></span>
                            <div>
                                <h6 class="mb-1">One counter workflow</h6>
                                <p class="text-muted mb-0">Bill services, products, add-ons, and quick jobs from one structured POS process instead of scattered manual notes.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <span class="landing-icon"><i class="icon-base ti tabler-car-garage"></i></span>
                            <div>
                                <h6 class="mb-1">Memory for every customer and vehicle</h6>
                                <p class="text-muted mb-0">Know what was done, what was sold, what oil grade was used, and when the next visit should happen.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <span class="landing-icon"><i class="icon-base ti tabler-building-store"></i></span>
                            <div>
                                <h6 class="mb-1">Built as a real SaaS product</h6>
                                <p class="text-muted mb-0">The platform is structured around multi-tenant shop workspaces, admin approval, and controlled access for each business.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="landing-card">
                                <span class="landing-icon mb-3"><i class="icon-base ti tabler-users-group"></i></span>
                                <h5 class="mb-2">Less confusion at the front desk</h5>
                                <p class="text-muted mb-0">Replace handwritten records, scattered spreadsheets, and disconnected messages with one reliable operating flow.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="landing-card">
                                <span class="landing-icon mb-3"><i class="icon-base ti tabler-package"></i></span>
                                <h5 class="mb-2">Better stock awareness</h5>
                                <p class="text-muted mb-0">Know what oils, filters, additives, and workshop consumables are available before the queue gets busy.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="landing-card">
                                <span class="landing-icon mb-3"><i class="icon-base ti tabler-shield-check"></i></span>
                                <h5 class="mb-2">Controlled staff roles</h5>
                                <p class="text-muted mb-0">Owners, managers, cashiers, technicians, and inventory staff can operate with the right level of access.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="landing-card">
                                <span class="landing-icon mb-3"><i class="icon-base ti tabler-bell-ringing"></i></span>
                                <h5 class="mb-2">Retention that feels organized</h5>
                                <p class="text-muted mb-0">Bring customers back with service reminders, loyalty support, and better visibility into their visit timeline.</p>
                            </div>
                        </div>
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
                <div class="col-lg-4" data-reveal="left">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-tool"></i>
                        Services Showcase
                    </span>
                    <h2 class="fw-bolder mb-3">Structured for the actual services garages, oil change shops, and workshop teams deliver every day.</h2>
                    <p class="text-muted fs-5 mb-0">
                        AutoServe fits routine maintenance, repairs, fast-turn visits, consumable sales, and add-on workshop work that happens across busy auto service businesses.
                    </p>
                </div>

                <div class="col-lg-8">
                    <div class="services-grid">
                        @foreach ([
                            'Engine oil change',
                            'Oil filter replacement',
                            'Air filter replacement',
                            'AC filter replacement',
                            'Brake oil replacement',
                            'Gear oil replacement',
                            'Coolant service',
                            'Transmission oil service',
                            'Tire rotation and check',
                            'Brake pad replacement',
                            'Battery service',
                            'Wheel alignment',
                            'Car wash and detailing',
                            'Vehicle diagnostics',
                            'Lubrication and greasing',
                            'Add-on consumables and products',
                            'Quick service inspection packages',
                        ] as $service)
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
                    Modules And Features
                </span>
                <h2 class="fw-bolder mb-3">A cleaner business stack for onboarding, service workflow, customer handling, and operational visibility.</h2>
                <p class="text-muted fs-5 mb-0">
                    AutoServe combines shop onboarding, operations, CRM, billing, reminders, and reports in one business-focused system tailored to car garages, oil change shops, and auto service businesses.
                </p>
            </div>

            <div class="row g-3">
                @foreach ([
                    ['icon' => 'tabler-building-store', 'title' => 'Shop onboarding', 'text' => 'Register your business, submit details, and move into an approved tenant workspace.'],
                    ['icon' => 'tabler-receipt-2', 'title' => 'POS billing', 'text' => 'Handle service billing, product sales, discounts, and counter-ready transactions.'],
                    ['icon' => 'tabler-package', 'title' => 'Inventory management', 'text' => 'Track oils, filters, workshop products, additives, and frequently used consumables.'],
                    ['icon' => 'tabler-tool', 'title' => 'Services catalog', 'text' => 'Organize service offerings, pricing, and structured quick-service jobs.'],
                    ['icon' => 'tabler-users', 'title' => 'Customer management', 'text' => 'Keep customer details, visit context, and repeat-visit intelligence easier to access.'],
                    ['icon' => 'tabler-car', 'title' => 'Vehicle history', 'text' => 'Store vehicle records, service history, product usage, and maintenance timelines.'],
                    ['icon' => 'tabler-shield-lock', 'title' => 'Staff roles and permissions', 'text' => 'Give each team member the right access based on responsibility and role.'],
                    ['icon' => 'tabler-award', 'title' => 'Loyalty and vouchers', 'text' => 'Support promotions, discounts, and retention offers for returning customers.'],
                    ['icon' => 'tabler-bell', 'title' => 'Reminders', 'text' => 'Bring customers back on time with service reminders and follow-up communication.'],
                    ['icon' => 'tabler-chart-bar', 'title' => 'Reports and analytics', 'text' => 'Review billing, stock movement, shop activity, and operational performance.'],
                    ['icon' => 'tabler-device-desktop', 'title' => 'Customer portal', 'text' => 'Enable service visibility, invoice transparency, and better customer trust.'],
                    ['icon' => 'tabler-file-search', 'title' => 'Audit-ready oversight', 'text' => 'Keep clearer operational records and track important business actions more professionally.'],
                ] as $module)
                    <div class="col-md-6 col-xl-4">
                        <div class="landing-card">
                            <span class="landing-icon mb-3"><i class="icon-base ti {{ $module['icon'] }}"></i></span>
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
                <div class="col-lg-5" data-reveal="left">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-route"></i>
                        How It Works
                    </span>
                    <h2 class="fw-bolder mb-3">A straightforward rollout path from registration to daily operations.</h2>
                    <p class="text-muted fs-5 mb-4">
                        AutoServe is built to keep onboarding practical. Start with registration, get approved, prepare your products and services, and begin day-to-day billing with much better record discipline.
                    </p>
                    <img src="{{ asset('assets/img/illustrations/girl-with-laptop-light.png') }}" alt="AutoServe onboarding workflow" class="img-fluid">
                </div>

                <div class="col-lg-7">
                    <div class="landing-card">
                        <div class="timeline-step">
                            <span class="timeline-number">1</span>
                            <h5 class="mb-1">Register your shop</h5>
                            <p class="text-muted mb-0">Submit owner and business details to begin your AutoServe workspace request.</p>
                        </div>
                        <div class="timeline-step">
                            <span class="timeline-number">2</span>
                            <h5 class="mb-1">Get approved by platform admin</h5>
                            <p class="text-muted mb-0">The central admin reviews registrations and activates approved shops.</p>
                        </div>
                        <div class="timeline-step">
                            <span class="timeline-number">3</span>
                            <h5 class="mb-1">Set up products and services</h5>
                            <p class="text-muted mb-0">Configure categories, inventory items, and service offerings that match your workshop flow.</p>
                        </div>
                        <div class="timeline-step">
                            <span class="timeline-number">4</span>
                            <h5 class="mb-1">Start billing and managing customers</h5>
                            <p class="text-muted mb-0">Run faster front-desk operations and keep customer and vehicle records properly organized.</p>
                        </div>
                        <div class="timeline-step">
                            <span class="timeline-number">5</span>
                            <h5 class="mb-1">Grow repeat visits with reminders and loyalty</h5>
                            <p class="text-muted mb-0">Stay in touch after service and make the next visit easier to win back.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section has-deco">
        <span class="deco-road" style="top: 3rem; left: 6%; transform: rotate(6deg);"></span>
        <span class="deco-dots" style="bottom: 2rem; right: 3%;"></span>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6" data-reveal="left">
                    <div class="landing-card">
                        <span class="section-kicker mb-3">
                            <i class="icon-base ti tabler-briefcase"></i>
                            Business Benefits
                        </span>
                        <h3 class="fw-bolder mb-3">Why shop owners move away from manual records and basic billing tools.</h3>
                        <div class="row g-3">
                            @foreach ([
                                'Faster billing at the front desk',
                                'Reduced mistakes in service and product records',
                                'Better visibility into workshop stock and consumables',
                                'Cleaner customer and vehicle histories',
                                'Stronger repeat-customer follow-up',
                                'A more professional operating model for growth',
                            ] as $benefit)
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

                <div class="col-lg-6" data-reveal="right">
                    <div class="landing-card">
                        <span class="section-kicker mb-3">
                            <i class="icon-base ti tabler-user-heart"></i>
                            Customer Experience
                        </span>
                        <h3 class="fw-bolder mb-3">A more trustworthy and transparent experience for the vehicle owner too.</h3>
                        <div class="row g-3">
                            @foreach ([
                                'Service history becomes easier to understand',
                                'Invoices and prior visits are easier to reference',
                                'Vehicle-based records reduce confusion on repeat visits',
                                'Reminder support helps customers come back on time',
                                'Loyalty and rewards improve customer satisfaction',
                                'A digital process feels more professional than handwritten slips',
                            ] as $benefit)
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
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder"><span data-count="12">12</span>+</div>
                        <div class="text-white text-opacity-75">Business modules in one platform</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder"><span data-count="17">17</span>+</div>
                        <div class="text-white text-opacity-75">Auto services supported out of the box</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder"><span data-count="5">5</span></div>
                        <div class="text-white text-opacity-75">Steps from registration to daily billing</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder"><span data-count="100">100</span>%</div>
                        <div class="text-white text-opacity-75">Cloud-based — run it from any device</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section section-soft has-deco">
        <span class="section-deco deco-spin" style="top: -2rem; left: -3rem; font-size: 13rem;"><i class="icon-base ti tabler-steering-wheel"></i></span>
        <span class="deco-blob" style="bottom: -5rem; right: -4rem;"></span>
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-message-2-star"></i>
                    What Shops Say
                </span>
                <h2 class="fw-bolder mb-3">Voices from the kind of businesses AutoServe is designed to support.</h2>
                <p class="text-muted fs-5 mb-0">
                    Built around the daily operational concerns of workshops, quick-lube counters, tire shops, and detailing studios.
                </p>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="quote-mark">“</div>
                            <div class="star-row">
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-4">
                            We were using manual notes for service history and simple billing for sales. AutoServe feels more like an actual shop operating system instead of just an invoice screen.
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="54" height="54" alt="Testimonial avatar">
                            <div>
                                <h6 class="mb-0">Imran Qureshi</h6>
                                <small class="text-muted">Owner, Quick Lube Corner</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="quote-mark">“</div>
                            <div class="star-row">
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-4">
                            The biggest value is seeing service activity, stock visibility, and customer history together. It gives our team a more professional workflow at the counter.
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset('assets/img/avatars/4.png') }}" class="rounded-circle" width="54" height="54" alt="Testimonial avatar">
                            <div>
                                <h6 class="mb-0">Sarah Malik</h6>
                                <small class="text-muted">Operations Manager, Metro Auto Service</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="quote-mark">“</div>
                            <div class="star-row">
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                                <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-4">
                            For us the appeal is that AutoServe speaks the language of quick service shops: vehicles, services, reminders, loyalty, staff roles, and repeat visits.
                        </p>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset('assets/img/avatars/7.png') }}" class="rounded-circle" width="54" height="54" alt="Testimonial avatar">
                            <div>
                                <h6 class="mb-0">Usman Raza</h6>
                                <small class="text-muted">Director, DriveCare Workshop Group</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section bg-white has-deco" id="plans">
        <span class="section-deco deco-float" style="top: 3rem; right: -2rem; font-size: 12rem;"><i class="icon-base ti tabler-key"></i></span>
        <span class="deco-dots" style="bottom: 4rem; left: 2%;"></span>
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-credit-card"></i>
                    Plans & Pricing
                </span>
                <h2 class="fw-bolder mb-3">A plan for every stage of your shop's growth.</h2>
                <p class="text-muted fs-5 mb-0">
                    From a single oil change counter to a multi-location service network — start with the plan that matches your operation today and scale when you're ready.
                </p>
            </div>

            <div class="row g-3 align-items-stretch">
                <div class="col-lg-4">
                    <div class="plan-card">
                        <span class="badge bg-label-primary mb-3">Starter</span>
                        <h4 class="fw-bolder">Starter Shop</h4>
                        <p class="text-muted">For smaller oil change counters beginning the move away from manual records and basic billing.</p>
                        <h3 class="fw-bolder mb-1">Contact Us</h3>
                        <p class="text-muted mb-4">For final pricing</p>
                        <div class="d-flex flex-column gap-3">
                            <div>Core service and product setup</div>
                            <div>POS billing workflow</div>
                            <div>Customer and vehicle records</div>
                            <div>Single-shop onboarding</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="plan-card featured">
                        <span class="plan-ribbon"><i class="icon-base ti tabler-flame icon-xs"></i> Most Popular</span>
                        <span class="badge bg-primary mb-3">Business</span>
                        <h4 class="fw-bolder">Business Growth</h4>
                        <p class="text-muted">For active quick service centers that need stronger stock visibility, retention features, and staff structure.</p>
                        <h3 class="fw-bolder mb-1">Contact Us</h3>
                        <p class="text-muted mb-4">For final pricing</p>
                        <div class="d-flex flex-column gap-3">
                            <div>Inventory and service operations</div>
                            <div>Discounts, vouchers, and loyalty support</div>
                            <div>Reminders and follow-up workflow</div>
                            <div>Role-based access for teams</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="plan-card">
                        <span class="badge bg-label-dark mb-3">Enterprise</span>
                        <h4 class="fw-bolder">Enterprise Network</h4>
                        <p class="text-muted">For businesses planning broader scale, stronger reporting visibility, and more advanced operational control.</p>
                        <h3 class="fw-bolder mb-1">Contact Us</h3>
                        <p class="text-muted mb-4">For final pricing</p>
                        <div class="d-flex flex-column gap-3">
                            <div>Multi-location rollout planning</div>
                            <div>Advanced reporting review</div>
                            <div>Operational support alignment</div>
                            <div>Structured customer experience roadmap</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section has-deco" id="faq">
        <span class="deco-blob" style="top: -4rem; right: -5rem;"></span>
        <span class="deco-road" style="bottom: 3.5rem; left: 5%; transform: rotate(-5deg);"></span>
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-4" data-reveal="left">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-help-circle"></i>
                        Frequently Asked Questions
                    </span>
                    <h2 class="fw-bolder mb-3">Answers for shop owners and managers comparing systems.</h2>
                    <p class="text-muted fs-5 mb-0">
                        AutoServe is aimed at practical business use, so the questions below focus on real workshop and oil-change operations rather than generic software talking points.
                    </p>
                </div>

                <div class="col-lg-8">
                        <div class="faq-panel">
                        <div class="accordion" id="faqAccordion">
                            @foreach ($faqs as $index => $faq)
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
                            Contact And Demo
                        </span>
                        <h2 class="fw-bolder text-white mb-3">Talk to us about onboarding, product walkthroughs, or the right rollout path for your workshop.</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">
                            Whether you run one oil change counter or you are planning a more structured quick-service operation, AutoServe can be presented around your business workflow.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-mail"></i></div>
                                    <div class="fw-semibold mb-1">Sales</div>
                                    <div class="text-muted small">sales@autoserve.test</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-lifebuoy"></i></div>
                                    <div class="fw-semibold mb-1">Support</div>
                                    <div class="text-muted small">support@autoserve.test</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-3 mt-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">Request Demo</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-shell p-4 p-lg-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="section-kicker mb-3 bg-white text-dark">
                            <i class="icon-base ti tabler-megaphone"></i>
                            Get Started Today
                        </span>
                        <h2 class="fw-bolder text-white mb-3">Start managing your shop smarter with a system built for garages, oil change, and auto service operations.</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">
                            Move from paper slips, spreadsheet notes, and scattered follow-up into a more structured operating model for billing, service history, stock awareness, and repeat-customer growth.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="javascript:void(0);" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#demoModal">Request Demo</a>
                            <a href="{{ route('login') }}" class="btn btn-label-light btn-lg">Login</a>
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
                    <span class="section-kicker mb-2"><i class="icon-base ti tabler-calendar-event"></i> Request A Demo</span>
                    <h4 class="fw-bolder mb-1" id="demoModalLabel">See AutoServe in action at your shop</h4>
                    <p class="text-muted mb-0">Tell us a little about your business and our team will reach out to schedule a walkthrough.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('demo.request.store') }}" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="demo_name">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="demo_name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_business_name">Business Name</label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror" id="demo_business_name" name="business_name" value="{{ old('business_name') }}">
                            @error('business_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="demo_email" name="email" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="demo_phone">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="demo_phone" name="phone" value="{{ old('phone') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="demo_business_type">Type Of Business</label>
                            <select class="form-select @error('business_type') is-invalid @enderror" id="demo_business_type" name="business_type">
                                <option value="">Select a category</option>
                                @foreach ([
                                    'Car garage / workshop',
                                    'Oil change / quick lube',
                                    'Tire & brake center',
                                    'Car wash & detailing',
                                    'Multi-service auto center',
                                    'Other',
                                ] as $type)
                                    <option value="{{ $type }}" @selected(old('business_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('business_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="demo_message">What would you like to see?</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="demo_message" name="message" rows="3" placeholder="Tell us about your shop, number of staff, or anything specific you want to cover.">{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 d-grid d-sm-flex gap-2 justify-content-sm-end">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-send me-1"></i>Request My Demo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<a href="#home" id="backToTop" class="back-to-top" aria-label="Back to top">
    <i class="icon-base ti tabler-arrow-up"></i>
</a>

<footer class="footer-shell pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @include('layouts.partials.brand-logo', ['size' => 40])
                    <div>
                        <div class="fw-bolder text-white">AutoServe</div>
                        <small>Integrated Automotive Solutions</small>
                    </div>
                </div>
                <p class="mb-3">
                    AutoServe is a business-focused SaaS platform for car garages, oil change shops, tire and brake centers, detailing studios, and workshop teams that want POS, inventory, customer records, reminders, and operational visibility in one system.
                </p>
                <div class="d-flex gap-2">
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-facebook"></i></span>
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-linkedin"></i></span>
                    <span class="landing-icon bg-white bg-opacity-10 text-white"><i class="icon-base ti tabler-brand-instagram"></i></span>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">Product</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#features">Features</a>
                    <a href="#services">Services</a>
                    <a href="#modules">Modules</a>
                    <a href="#how-it-works">How It Works</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">Company</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#faq">FAQ</a>
                    <a href="#contact">Contact</a>
                    <a href="#plans">Plans</a>
                    <a href="#home">Home</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">Access</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#demoModal">Request Demo</a>
                    <a href="#plans">Pricing</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">Support</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="mailto:support@autoserve.test">support@autoserve.test</a>
                    <a href="mailto:sales@autoserve.test">sales@autoserve.test</a>
                    <span>Mon-Sat | 9:00 AM - 6:00 PM</span>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
            <p class="mb-0">&copy; {{ now()->year }} AutoServe. All rights reserved.</p>
            <p class="mb-0">Crafted for garages, oil change shops, and auto service businesses.</p>
        </div>
    </div>
</footer>
@endsection

@section('scripts')
@php
    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => config('app.name'),
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => url('/'),
            'image' => asset('assets/img/logo/occ.png'),
            'description' => 'All-in-one SaaS platform for car garages, oil change shops, tire & brake centers, detailing studios and auto service businesses — POS billing, inventory, vehicle & service history, reminders, loyalty, and staff roles.',
            'audience' => [
                '@type' => 'BusinessAudience',
                'name' => 'Car garages, oil change shops, tire and brake centers, detailing studios, auto repair workshops, spare parts shops',
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('assets/img/logo/occ.png'),
            'slogan' => 'Integrated Automotive Solutions',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ])->values()->all(),
        ],
    ];
@endphp
@foreach ($structuredData as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
<script>
    (function () {
        // Solidify navbar background after scrolling past the hero.
        var navbar = document.querySelector('.landing-navbar');
        var backToTop = document.getElementById('backToTop');
        var progressBar = document.getElementById('scrollProgressBar');
        var onScroll = function () {
            if (navbar) {
                navbar.classList.toggle('is-scrolled', window.scrollY > 24);
            }
            if (backToTop) {
                backToTop.classList.toggle('is-visible', window.scrollY > 600);
            }
            if (progressBar) {
                var max = document.documentElement.scrollHeight - window.innerHeight;
                progressBar.style.width = (max > 0 ? (window.scrollY / max) * 100 : 0) + '%';
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Count the stats band numbers up when it scrolls into view.
        var counters = document.querySelectorAll('[data-count]');
        if (counters.length && 'IntersectionObserver' in window && !reduceMotion) {
            var counterObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    counterObserver.unobserve(entry.target);
                    var el = entry.target;
                    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                    var start = null;
                    var step = function (ts) {
                        if (!start) start = ts;
                        var progress = Math.min((ts - start) / 1400, 1);
                        el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                        if (progress < 1) requestAnimationFrame(step);
                    };
                    el.textContent = '0';
                    requestAnimationFrame(step);
                });
            }, { threshold: 0.5 });
            counters.forEach(function (el) { counterObserver.observe(el); });
        }

        // Highlight the nav link of the section currently in view.
        if (window.bootstrap && bootstrap.ScrollSpy) {
            new bootstrap.ScrollSpy(document.body, { target: '#publicNavbar', rootMargin: '-30% 0px -60% 0px' });
        }

        // Reveal sections as they enter the viewport, staggered within each row.
        var targets = document.querySelectorAll('.landing-card, .service-pill, .testimonial-card, .plan-card, .stats-band, .timeline-step, .contact-card, [data-reveal]');
        if (!('IntersectionObserver' in window) || !targets.length) {
            return;
        }
        targets.forEach(function (el) {
            el.classList.add('reveal');
            var direction = el.getAttribute('data-reveal');
            if (direction === 'left') el.classList.add('reveal-left');
            if (direction === 'right') el.classList.add('reveal-right');
            // Cards sit alone inside col-* wrappers; climb until the node has
            // real siblings so grid items get a left-to-right stagger.
            var node = el;
            while (node.parentElement && node.parentElement.children.length === 1) {
                node = node.parentElement;
            }
            var siblings = node.parentElement ? node.parentElement.children : [node];
            var index = Array.prototype.indexOf.call(siblings, node);
            el.style.transitionDelay = Math.min(Math.max(index, 0), 5) * 70 + 'ms';
        });
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
