@extends('layouts.app')

@section('title', __('services.title'))

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        {{-- <div>
            <h4 class="mb-1">Services</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Catalog &amp; Services</li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                </ol>
            </nav>
        </div> --}}

        <div class="d-flex align-items-center gap-2" id="serviceTableActions">
            <div class="dropdown">
                <button
                    type="button"
                    class="btn btn-label-secondary btn-icon"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="{{ __('services.filters') }}"
                >
                    <i class="ti tabler-filter"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 340px;">
                    <div class="mb-3">
                        <label for="service_filter_category" class="form-label">{{ __('services.category') }}</label>
                        <select
                            id="service_filter_category"
                            class="form-select category-select2"
                            data-placeholder="{{ __('services.all_categories') }}"
                            data-allow-clear="true"
                        >
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_status" class="form-label">{{ __('services.status') }}</label>
                        <select
                            id="service_status"
                            class="form-select filter-control select2"
                            data-placeholder="{{ __('services.all_statuses') }}"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">{{ __('app.all') }}</option>
                            <option value="1">{{ __('app.active') }}</option>
                            <option value="0">{{ __('app.inactive') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_requires_technician_filter" class="form-label">{{ __('services.technician') }}</label>
                        <select
                            id="service_requires_technician_filter"
                            class="form-select filter-control select2"
                            data-placeholder="{{ __('services.all_services') }}"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">{{ __('app.all') }}</option>
                            <option value="1">{{ __('app.required') }}</option>
                            <option value="0">{{ __('app.not_required') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="service_sort" class="form-label">{{ __('services.sort_by') }}</label>
                        <select
                            id="service_sort"
                            class="form-select filter-control select2"
                            data-placeholder="{{ __('services.sort_services') }}"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">{{ __('services.latest') }}</option>
                            <option value="category">{{ __('services.category_a_z') }}</option>
                            <option value="name">{{ __('services.name_a_z') }}</option>
                            <option value="price_low_high">{{ __('services.price_low_high') }}</option>
                            <option value="duration_low_high">{{ __('services.duration_low_high') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Service::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addServiceBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#serviceModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    {{ __('services.add_service') }}
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="services-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>{{ __('services.category') }}</th>
                        <th>{{ __('services.service') }}</th>
                        <th>{{ __('services.code') }}</th>
                        <th>{{ __('services.price') }}</th>
                        <th>{{ __('services.duration') }}</th>
                        <th>{{ __('services.mapped_products') }}</th>
                        <th>{{ __('services.technician') }}</th>
                        <th>{{ __('services.status') }}</th>
                        <th>{{ __('services.created') }}</th>
                        <th class="text-center">{{ __('services.actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="serviceForm" action="{{ route('tenant.ecommerce.services.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="service_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalLabel">{{ __('services.add_service') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('app.close') }}"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="service_category_id" class="form-label">{{ __('services.category') }}</label>
                                <div class="position-relative">
                                    <select
                                        id="service_category_id"
                                        name="category_id"
                                        class="form-select category-select2"
                                        data-placeholder="{{ __('services.select_category') }}"
                                        data-allow-clear="true"
                                        data-dropdown-parent="#serviceModal"
                                    ></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="service_title_en" class="form-label">{{ __('services.title_en') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="service_title_en" name="title_en" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="service_title_ar" class="form-label">{{ __('services.title_ar') }}</label>
                                <input type="text" class="form-control" id="service_title_ar" name="title_ar" maxlength="150" dir="rtl">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="service_code" class="form-label">{{ __('services.code') }}</label>
                                <input type="text" class="form-control" id="service_code" name="code" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="service_description_en" class="form-label">{{ __('services.description_en') }}</label>
                                <textarea class="form-control" id="service_description_en" name="description_en" rows="3" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="service_description_ar" class="form-label">{{ __('services.description_ar') }}</label>
                                <textarea class="form-control" id="service_description_ar" name="description_ar" rows="3" maxlength="2000" dir="rtl"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="service_standard_price" class="form-label">{{ __('services.standard_price') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="service_standard_price" name="standard_price" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_estimated_duration_minutes" class="form-label">{{ __('services.duration_minutes') }}</label>
                                <input type="number" min="0" class="form-control" id="service_estimated_duration_minutes" name="estimated_duration_minutes">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_tax_percentage" class="form-label">{{ __('services.tax_percentage') }}</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="service_tax_percentage" name="tax_percentage">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_reminder_interval_days" class="form-label">{{ __('services.reminder_days') }}</label>
                                <input type="number" min="0" class="form-control" id="service_reminder_interval_days" name="reminder_interval_days">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="service_mileage_interval" class="form-label">{{ __('services.mileage_interval') }}</label>
                                <input type="number" min="0" class="form-control" id="service_mileage_interval" name="mileage_interval">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">{{ __('services.technician_required') }}</label>
                                <input type="hidden" name="requires_technician" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="service_requires_technician" name="requires_technician" value="1">
                                    <label class="form-check-label" for="service_requires_technician">{{ __('app.required') }}</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">{{ __('services.status') }}</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="service_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="service_is_active">{{ __('app.active') }}</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">{{ __('services.product_consumption_mapping') }}</h6>
                                    <p class="text-muted small mb-0">{{ __('services.product_mapping_help') }}</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-label-primary" id="addServiceMappingRowBtn">
                                    <i class="ti tabler-plus me-1"></i>
                                    {{ __('services.add_product') }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="serviceMappingsTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 280px;">{{ __('services.product') }}</th>
                                            <th style="min-width: 140px;">{{ __('services.quantity') }}</th>
                                            <th style="min-width: 160px;">{{ __('services.unit') }}</th>
                                            <th style="min-width: 120px;">{{ __('app.required') }}</th>
                                            <th class="text-center" style="width: 60px;">#</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="invalid-feedback d-block mt-2" id="service_mappings_feedback"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="serviceSubmitBtn" data-create-text="{{ __('services.save_service') }}" data-update-text="{{ __('services.update_service') }}">
                            {{ __('services.save_service') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.serviceListingUrl = @json($listingUrl);
        window.serviceEditUrlTemplate = @json($editUrlTemplate);
        window.categoryDropdownUrl = @json($categoriesDropdownUrl);
        window.serviceProductDropdownUrl = @json($productsDropdownUrl);
        window.serviceTranslations = {
            add_service: @json(__('services.add_service')),
            edit_service: @json(__('services.edit_service')),
            save_service: @json(__('services.save_service')),
            update_service: @json(__('services.update_service')),
            saving_service: @json(__('services.saving_service')),
            loading_service: @json(__('services.loading_service')),
            deleting_service: @json(__('services.deleting_service')),
            selected_item: @json(__('app.selected_item')),
            selected_product: @json(__('app.selected_product')),
            select_product: @json(__('services.select_product')),
            optional_unit: @json(__('services.optional_unit')),
            search_placeholder: @json(__('services.search_placeholder')),
            no_services_found: @json(__('services.no_services_found')),
            delete_service_title: @json(__('services.delete_service_title')),
            delete_service_text: @json(__('services.delete_service_text')),
            unable_to_load_service: @json(__('services.unable_to_load_service')),
            unable_to_save_service: @json(__('services.unable_to_save_service')),
            unable_to_delete_service: @json(__('services.unable_to_delete_service')),
            service_saved: @json(__('services.service_updated')),
            service_deleted: @json(__('services.service_deleted')),
            minutes_short: @json(__('services.minutes_short')),
            not_available: @json(__('app.not_available')),
            title_en_required: @json(__('services.title_en_required')),
            title_en_max: @json(__('services.title_en_max')),
            title_ar_max: @json(__('services.title_ar_max')),
            description_max: @json(__('services.description_max')),
            standard_price_required: @json(__('services.standard_price_required')),
            standard_price_numeric: @json(__('services.standard_price_numeric')),
            standard_price_min: @json(__('services.standard_price_min')),
        };
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/services.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/services.js')) }}"></script>
@endsection
