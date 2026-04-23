@extends('layouts.app')

@section('title', 'Shop Settings')

@php
    $activeTab = old('active_tab', 'general');
@endphp

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Shop Settings</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Shop Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Shop Profile</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-label-primary">{{ $tenant->display_name }}</span>
            <span class="badge bg-label-{{ $tenant->status->badgeClass() }}">{{ ucfirst($tenant->status->value) }}</span>
            <span class="badge bg-label-info text-capitalize">Onboarding {{ $tenant->onboarding_state }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <span class="badge bg-label-primary mb-2">Setup Progress</span>
                            <h5 class="mb-1">{{ $tenant->display_name }}</h5>
                            <p class="text-muted mb-0">Keep your shop profile, defaults, and billing preferences aligned before POS billing goes live.</p>
                        </div>
                        <span class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-building-store icon-lg"></i>
                            </span>
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium">Readiness</span>
                            <span>{{ $readiness['percentage'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $readiness['percentage'] }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($readiness['items'] as $item)
                            <div class="d-flex align-items-center justify-content-between gap-3 border rounded p-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="icon-base ti {{ $item['completed'] ? 'tabler-circle-check text-success' : 'tabler-circle-dashed text-muted' }}"></i>
                                    <span class="fw-medium">{{ $item['label'] }}</span>
                                </div>
                                <span class="badge {{ $item['completed'] ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $item['completed'] ? 'Done' : 'Pending' }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="rounded bg-label-primary p-3">
                        <div class="fw-medium mb-1">Current defaults</div>
                        <div class="small text-muted">Currency: {{ old('currency', $form['currency']) }}</div>
                        <div class="small text-muted">Timezone: {{ old('timezone', $form['timezone']) }}</div>
                        <div class="small text-muted">Invoice prefix: {{ old('invoice_prefix', $form['invoice_prefix']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">Profile, Defaults, and Notifications</h5>
                    <p class="text-muted mb-0">These settings are stored against the current tenant and are safe for this shop only.</p>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs widget-nav-tabs pb-3 gap-2 flex-column flex-sm-row" role="tablist">
                        <li class="nav-item">
                            <button
                                type="button"
                                class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                                data-bs-toggle="tab"
                                data-bs-target="#shop-settings-general"
                                data-tab-value="general"
                                role="tab"
                            >
                                <i class="icon-base ti tabler-building-store me-2"></i>General
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                type="button"
                                class="nav-link {{ $activeTab === 'regional' ? 'active' : '' }}"
                                data-bs-toggle="tab"
                                data-bs-target="#shop-settings-regional"
                                data-tab-value="regional"
                                role="tab"
                            >
                                <i class="icon-base ti tabler-world me-2"></i>Regional & Billing
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                type="button"
                                class="nav-link {{ $activeTab === 'operations' ? 'active' : '' }}"
                                data-bs-toggle="tab"
                                data-bs-target="#shop-settings-operations"
                                data-tab-value="operations"
                                role="tab"
                            >
                                <i class="icon-base ti tabler-settings-cog me-2"></i>Operations
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                type="button"
                                class="nav-link {{ $activeTab === 'notifications' ? 'active' : '' }}"
                                data-bs-toggle="tab"
                                data-bs-target="#shop-settings-notifications"
                                data-tab-value="notifications"
                                role="tab"
                            >
                                <i class="icon-base ti tabler-bell me-2"></i>Notifications & Loyalty
                            </button>
                        </li>
                    </ul>

                    <form action="{{ route('tenant.settings.shop-profile.update') }}" method="POST" class="mt-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="active_tab" id="shop_settings_active_tab" value="{{ $activeTab }}">

                        <div class="tab-content px-0 pb-0">
                            <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="shop-settings-general" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="shop_name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('shop_name') is-invalid @enderror" id="shop_name" name="shop_name" maxlength="150" value="{{ old('shop_name', $form['shop_name']) }}">
                                        @error('shop_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('business_name') is-invalid @enderror" id="business_name" name="business_name" maxlength="150" value="{{ old('business_name', $form['business_name']) }}">
                                        @error('business_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="owner_name" class="form-label">Primary Contact <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('owner_name') is-invalid @enderror" id="owner_name" name="owner_name" maxlength="150" value="{{ old('owner_name', $form['owner_name']) }}">
                                        @error('owner_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website_url" class="form-label">Website URL</label>
                                        <input type="url" class="form-control @error('website_url') is-invalid @enderror" id="website_url" name="website_url" maxlength="255" value="{{ old('website_url', $form['website_url']) }}" placeholder="https://example.com">
                                        @error('website_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="business_email" class="form-label">Business Email</label>
                                        <input type="email" class="form-control @error('business_email') is-invalid @enderror" id="business_email" name="business_email" maxlength="150" value="{{ old('business_email', $form['business_email']) }}">
                                        @error('business_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="business_phone" class="form-label">Business Phone</label>
                                        <input type="text" class="form-control @error('business_phone') is-invalid @enderror" id="business_phone" name="business_phone" maxlength="30" value="{{ old('business_phone', $form['business_phone']) }}">
                                        @error('business_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" maxlength="1000">{{ old('address', $form['address']) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" maxlength="150" value="{{ old('city', $form['city']) }}">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="state" class="form-label">State / Region</label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" maxlength="150" value="{{ old('state', $form['state']) }}">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" maxlength="150" value="{{ old('country', $form['country']) }}">
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'regional' ? 'show active' : '' }}" id="shop-settings-regional" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                        <select id="currency" name="currency" class="form-select select2 @error('currency') is-invalid @enderror" data-placeholder="Select currency">
                                            @foreach($currencyOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('currency', $form['currency']) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('currency')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                                        <select id="timezone" name="timezone" class="form-select select2 @error('timezone') is-invalid @enderror" data-placeholder="Select timezone">
                                            @foreach($timezoneOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('timezone', $form['timezone']) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('timezone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="locale" class="form-label">Locale <span class="text-danger">*</span></label>
                                        <select id="locale" name="locale" class="form-select select2 @error('locale') is-invalid @enderror" data-placeholder="Select locale">
                                            @foreach($localeOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('locale', $form['locale']) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('locale')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tax_name" class="form-label">Tax Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('tax_name') is-invalid @enderror" id="tax_name" name="tax_name" maxlength="100" value="{{ old('tax_name', $form['tax_name']) }}">
                                        @error('tax_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tax_percentage" class="form-label">Tax Percentage <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', $form['tax_percentage']) }}">
                                        @error('tax_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="invoice_prefix" class="form-label">Invoice Prefix <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('invoice_prefix') is-invalid @enderror" id="invoice_prefix" name="invoice_prefix" maxlength="20" value="{{ old('invoice_prefix', $form['invoice_prefix']) }}">
                                        @error('invoice_prefix')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="invoice_next_number" class="form-label">Invoice Next Number <span class="text-danger">*</span></label>
                                        <input type="number" min="1" class="form-control @error('invoice_next_number') is-invalid @enderror" id="invoice_next_number" name="invoice_next_number" value="{{ old('invoice_next_number', $form['invoice_next_number']) }}">
                                        @error('invoice_next_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'operations' ? 'show active' : '' }}" id="shop-settings-operations" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <h6 class="mb-1">Inventory Defaults</h6>
                                            <p class="text-muted small mb-3">Use a shop-wide low stock threshold for new products and replenishment checks.</p>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label for="low_stock_threshold" class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
                                                    <input type="number" min="0" class="form-control @error('low_stock_threshold') is-invalid @enderror" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $form['low_stock_threshold']) }}">
                                                    @error('low_stock_threshold')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                                <div>
                                                    <h6 class="mb-1">Business Hours</h6>
                                                    <p class="text-muted small mb-0">These hours are stored in tenant settings for future scheduling, reminders, and customer-facing displays.</p>
                                                </div>
                                                <span class="badge bg-label-info">Tenant only</span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th style="width: 140px;">Closed</th>
                                                            <th style="width: 180px;">Open</th>
                                                            <th style="width: 180px;">Close</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($weekdayOptions as $day => $label)
                                                            @php
                                                                $closed = (bool) old("business_hours.{$day}.is_closed", data_get($form, "business_hours.{$day}.is_closed"));
                                                            @endphp
                                                            <tr data-business-hours-row>
                                                                <td class="fw-medium">{{ $label }}</td>
                                                                <td>
                                                                    <input type="hidden" name="business_hours[{{ $day }}][is_closed]" value="0">
                                                                    <div class="form-check form-switch">
                                                                        <input
                                                                            class="form-check-input business-hours-closed-toggle"
                                                                            type="checkbox"
                                                                            role="switch"
                                                                            name="business_hours[{{ $day }}][is_closed]"
                                                                            value="1"
                                                                            @checked($closed)
                                                                        >
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        type="time"
                                                                        class="form-control @error("business_hours.{$day}.open") is-invalid @enderror"
                                                                        name="business_hours[{{ $day }}][open]"
                                                                        value="{{ old("business_hours.{$day}.open", data_get($form, "business_hours.{$day}.open")) }}"
                                                                        data-business-hours-time
                                                                        @disabled($closed)
                                                                    >
                                                                    @error("business_hours.{$day}.open")
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input
                                                                        type="time"
                                                                        class="form-control @error("business_hours.{$day}.close") is-invalid @enderror"
                                                                        name="business_hours[{{ $day }}][close]"
                                                                        value="{{ old("business_hours.{$day}.close", data_get($form, "business_hours.{$day}.close")) }}"
                                                                        data-business-hours-time
                                                                        @disabled($closed)
                                                                    >
                                                                    @error("business_hours.{$day}.close")
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'notifications' ? 'show active' : '' }}" id="shop-settings-notifications" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <h6 class="mb-1">Notification Preferences</h6>
                                            <p class="text-muted small mb-3">Decide which customer communications should be enabled by default for this shop.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <input type="hidden" name="reminder_email_enabled" value="0">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="reminder_email_enabled" name="reminder_email_enabled" value="1" @checked(old('reminder_email_enabled', $form['reminder_email_enabled']))>
                                                        <label class="form-check-label" for="reminder_email_enabled">Reminder emails enabled</label>
                                                    </div>
                                                    @error('reminder_email_enabled')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="hidden" name="receipt_email_enabled" value="0">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="receipt_email_enabled" name="receipt_email_enabled" value="1" @checked(old('receipt_email_enabled', $form['receipt_email_enabled']))>
                                                        <label class="form-check-label" for="receipt_email_enabled">Receipt emails enabled</label>
                                                    </div>
                                                    @error('receipt_email_enabled')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                                <div>
                                                    <h6 class="mb-1">Loyalty Configuration</h6>
                                                    <p class="text-muted small mb-0">Store the baseline loyalty preference now so future POS and customer modules can use a consistent default.</p>
                                                </div>
                                                <span class="badge bg-label-warning">Foundation ready</span>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <input type="hidden" name="loyalty_enabled" value="0">
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="loyalty_enabled" name="loyalty_enabled" value="1" @checked(old('loyalty_enabled', $form['loyalty_enabled']))>
                                                        <label class="form-check-label" for="loyalty_enabled">Enable loyalty program defaults</label>
                                                    </div>
                                                    @error('loyalty_enabled')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="loyalty_points_per_currency" class="form-label">Points Per Currency Unit <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0" class="form-control @error('loyalty_points_per_currency') is-invalid @enderror" id="loyalty_points_per_currency" name="loyalty_points_per_currency" value="{{ old('loyalty_points_per_currency', $form['loyalty_points_per_currency']) }}">
                                                    @error('loyalty_points_per_currency')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tenant.dashboard') }}" class="btn btn-label-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>
                                Save Shop Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/tenant/shop-settings.js') }}"></script>
@endsection
