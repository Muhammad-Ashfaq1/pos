@extends('layouts.public')

@section('title', 'OCC - Oil Change POS SaaS for Quick Auto Service Shops')
@section('meta_description', 'OCC helps oil change shops and quick automotive service businesses manage POS billing, inventory, service history, reminders, loyalty, staff roles, and customer operations in one SaaS platform.')

@section('content')
<nav class="navbar navbar-expand-lg landing-navbar sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="#home">
            <span class="brand-mark">O</span>
            <span>
                <span class="fw-bolder fs-4 text-heading d-block lh-1">OCC</span>
                <small class="text-muted">Oil Change POS SaaS</small>
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
                <a href="{{ route('register') }}" class="btn btn-primary">Register Your Shop</a>
            </div>
        </div>
    </div>
</nav>

<main>
    <section class="landing-section pt-5 pb-5" id="home">
        <div class="container">
            <div class="hero-shell p-4 p-lg-5">
                <div class="row align-items-center g-4 g-xl-5 position-relative">
                    <div class="col-lg-7">
                        <span class="section-kicker mb-3">
                            <i class="icon-base ti tabler-gas-station"></i>
                            Built For Oil Change Operations
                        </span>

                        <h1 class="hero-title fw-bold text-white mb-3">
                            Manage your oil change shop with faster billing, cleaner service records, and stronger customer retention.
                        </h1>

                        <p class="hero-copy text-white text-opacity-75 mb-4 hero-subtext">
                            OCC is a business-focused SaaS platform for oil change shops, quick lube counters, and automotive service centers that need POS billing, inventory visibility, customer and vehicle history, reminders, loyalty, and staff access control in one place.
                        </p>

                        <div class="hero-actions d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="#contact" class="btn btn-outline-light btn-lg">Request Demo</a>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-visual-card p-3 p-lg-4">
                            <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}" alt="OCC platform overview" class="img-fluid d-block mx-auto" style="max-height: 360px;">

                            <div class="floating-panel" style="top: 1.5rem; right: 1rem;">
                                <div class="fw-semibold">Today at the service bay</div>
                                <small class="text-muted">27 jobs billed, 9 repeat visits tracked</small>
                            </div>

                            <div class="floating-panel" style="bottom: 1.5rem; left: 1rem;">
                                <div class="fw-semibold">Follow-up campaigns ready</div>
                                <small class="text-muted">Reminder flows prepared for the next service visit</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="landing-section" id="features">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-bolt"></i>
                        Why OCC
                    </span>
                    <h2 class="fw-bolder mb-3">The system that helps quick-service automotive businesses move beyond manual handling.</h2>
                    <p class="text-muted fs-5 mb-4">
                        OCC is designed for shops that are still juggling paper slips, WhatsApp follow-ups, disconnected stock notes, or basic billing tools. It gives owners and managers one more professional way to run daily service operations.
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

    <section class="landing-section section-soft" id="services">
        <div class="container">
            <div class="row align-items-start g-5">
                <div class="col-lg-4">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-tool"></i>
                        Services Showcase
                    </span>
                    <h2 class="fw-bolder mb-3">Structured for the actual services quick oil change and workshop teams deliver every day.</h2>
                    <p class="text-muted fs-5 mb-0">
                        OCC fits routine maintenance services, fast-turn visits, consumable sales, and add-on workshop work that usually happens in high-movement quick service businesses.
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
                            'Tire check',
                            'Car wash',
                            'Battery water',
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

    <section class="landing-section" id="modules">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-layout-grid"></i>
                    Modules And Features
                </span>
                <h2 class="fw-bolder mb-3">A cleaner business stack for onboarding, service workflow, customer handling, and operational visibility.</h2>
                <p class="text-muted fs-5 mb-0">
                    OCC combines shop onboarding, operations, CRM, billing, reminders, and reports in one business-focused system tailored to oil change and quick automotive service businesses.
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

    <section class="landing-section section-soft" id="how-it-works">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-route"></i>
                        How It Works
                    </span>
                    <h2 class="fw-bolder mb-3">A straightforward rollout path from registration to daily operations.</h2>
                    <p class="text-muted fs-5 mb-4">
                        OCC is built to keep onboarding practical. Start with registration, get approved, prepare your products and services, and begin day-to-day billing with much better record discipline.
                    </p>
                    <img src="{{ asset('assets/img/illustrations/girl-with-laptop-light.png') }}" alt="OCC onboarding workflow" class="img-fluid">
                </div>

                <div class="col-lg-7">
                    <div class="landing-card">
                        <div class="timeline-step">
                            <span class="timeline-number">1</span>
                            <h5 class="mb-1">Register your shop</h5>
                            <p class="text-muted mb-0">Submit owner and business details to begin your OCC workspace request.</p>
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

    <section class="landing-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
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

                <div class="col-lg-6">
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
                <div class="row g-4 text-center">
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder">10+</div>
                        <div class="text-white text-opacity-75">Business modules in one platform</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder">25-Day</div>
                        <div class="text-white text-opacity-75">Reminder support showcase value</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder">Multi-Role</div>
                        <div class="text-white text-opacity-75">Access for owners, managers, cashiers, and staff</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="display-6 fw-bolder">All-In-One</div>
                        <div class="text-white text-opacity-75">Service, inventory, CRM, billing, and reports</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section section-soft">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-message-2-star"></i>
                    Social Proof
                </span>
                <h2 class="fw-bolder mb-3">Voices from the kind of businesses OCC is designed to support.</h2>
                <p class="text-muted fs-5 mb-0">
                    These testimonials are static placeholders for now, but they reflect the real operational concerns workshop and quick-lube businesses usually have.
                </p>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="quote-mark mb-2">“</div>
                        <p class="text-muted mb-4">
                            We were using manual notes for service history and simple billing for sales. OCC feels more like an actual shop operating system instead of just an invoice screen.
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
                        <div class="quote-mark mb-2">“</div>
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
                        <div class="quote-mark mb-2">“</div>
                        <p class="text-muted mb-4">
                            For us the appeal is that OCC speaks the language of quick service shops: vehicles, services, reminders, loyalty, staff roles, and repeat visits.
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

    <section class="landing-section bg-white" id="plans">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="section-kicker mb-3">
                    <i class="icon-base ti tabler-credit-card"></i>
                    Pricing Placeholder
                </span>
                <h2 class="fw-bolder mb-3">Positioned for shops evaluating software as a real growth investment.</h2>
                <p class="text-muted fs-5 mb-0">
                    Pricing is static for now, but the structure below helps present OCC as a serious SaaS product for different levels of workshop operations.
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

    <section class="landing-section" id="faq">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-4">
                    <span class="section-kicker mb-3">
                        <i class="icon-base ti tabler-help-circle"></i>
                        Frequently Asked Questions
                    </span>
                    <h2 class="fw-bolder mb-3">Answers for shop owners and managers comparing systems.</h2>
                    <p class="text-muted fs-5 mb-0">
                        OCC is aimed at practical business use, so the questions below focus on real workshop and oil-change operations rather than generic software talking points.
                    </p>
                </div>

                <div class="col-lg-8">
                        <div class="faq-panel">
                        <div class="accordion" id="faqAccordion">
                            @foreach ([
                                ['q' => 'Is OCC only for oil change shops?', 'a' => 'OCC is designed mainly for oil change shops, quick lube counters, and similar quick automotive service businesses where repeat visits, consumables, service history, and fast billing matter.'],
                                ['q' => 'Can I manage billing and inventory together?', 'a' => 'Yes. OCC is positioned as an all-in-one business platform that combines service billing, workshop products, consumables, and stock visibility into one operating flow.'],
                                ['q' => 'Can my staff have different permissions?', 'a' => 'Yes. OCC is built with role-based access so owners, managers, technicians, cashiers, and other team members can operate with different permission levels.'],
                                ['q' => 'Can customers view their history?', 'a' => 'The product direction includes customer-facing service and invoice visibility. Even where the full portal is still evolving, OCC is designed around better visit transparency and customer trust.'],
                                ['q' => 'Do shops need admin approval?', 'a' => 'Yes. Shop registration is followed by platform admin review and approval before the tenant workspace is activated.'],
                                ['q' => 'Can I manage walk-in and registered customers?', 'a' => 'Yes. OCC is intended to support real counter operations where businesses deal with both repeat registered customers and walk-in service traffic.'],
                            ] as $index => $faq)
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
            <div class="contact-strip p-4 p-lg-4 mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="section-kicker mb-3 bg-white text-dark">
                            <i class="icon-base ti tabler-phone-call"></i>
                            Contact And Demo
                        </span>
                        <h2 class="fw-bolder text-white mb-3">Talk to us about onboarding, product walkthroughs, or the right rollout path for your workshop.</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">
                            Whether you run one oil change counter or you are planning a more structured quick-service operation, OCC can be presented around your business workflow.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-mail"></i></div>
                                    <div class="fw-semibold mb-1">Sales</div>
                                    <div class="text-muted small">sales@oilchangepos.test</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="contact-card">
                                    <div class="landing-icon mb-3"><i class="icon-base ti tabler-lifebuoy"></i></div>
                                    <div class="fw-semibold mb-1">Support</div>
                                    <div class="text-muted small">support@oilchangepos.test</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-3 mt-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="#plans" class="btn btn-outline-light btn-lg">Request Demo</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-shell p-4 p-lg-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="section-kicker mb-3 bg-white text-dark">
                            <i class="icon-base ti tabler-megaphone"></i>
                            Final CTA
                        </span>
                        <h2 class="fw-bolder text-white mb-3">Start managing your shop smarter with a system built for oil change and quick service operations.</h2>
                        <p class="text-white text-opacity-75 fs-5 mb-0">
                            Move from paper slips, spreadsheet notes, and scattered follow-up into a more structured operating model for billing, service history, stock awareness, and repeat-customer growth.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-3">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg">Register Your Shop</a>
                            <a href="#contact" class="btn btn-outline-light btn-lg">Request Demo</a>
                            <a href="{{ route('login') }}" class="btn btn-label-light btn-lg">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer-shell pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="brand-mark">O</span>
                    <div>
                        <div class="fw-bolder text-white">OCC</div>
                        <small>Oil Change POS SaaS</small>
                    </div>
                </div>
                <p class="mb-3">
                    OCC is a business-focused SaaS platform for oil change shops, quick automotive service centers, and workshop teams that want POS, inventory, customer records, reminders, and operational visibility in one system.
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
                    <a href="#contact">Request Demo</a>
                    <a href="#plans">Pricing</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="text-white mb-3">Support</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="mailto:support@oilchangepos.test">support@oilchangepos.test</a>
                    <a href="mailto:sales@oilchangepos.test">sales@oilchangepos.test</a>
                    <span>Mon-Sat | 9:00 AM - 6:00 PM</span>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
            <p class="mb-0">&copy; {{ now()->year }} OCC. All rights reserved.</p>
            <p class="mb-0">Static public landing page content for showcase and product positioning.</p>
        </div>
    </div>
</footer>
@endsection
