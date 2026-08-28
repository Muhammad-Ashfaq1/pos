@extends('layouts.app')

@section('title', $activeModule['title'] ?? 'Cards')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
    @php
        $singular = $activeModule['singular'];
        $valueLabel = \App\Models\Card::metaFor($cardType)['value_label'];
    @endphp

    @once
        <style>
            /* Same product multi-select styling as employee cards */
            .card-product-select + .select2-container {
                width: 100% !important;
            }

            .card-product-select.is-invalid + .select2-container .select2-selection {
                border-color: #ea5455;
            }

            .card-product-select + .select2-container .select2-selection--multiple {
                position: relative;
                padding: 0.35rem 1.75rem 0.35rem 0.5rem;
            }

            .modal .card-product-select + .select2-container .select2-selection--multiple {
                min-height: calc(1.5em + 0.875rem + 2px);
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__rendered {
                display: flex !important;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.35rem;
                margin: 0 !important;
                padding: 0 !important;
                list-style: none;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__choice {
                float: none !important;
                display: inline-flex;
                align-items: center;
                margin: 0 !important;
                max-width: 100%;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__clear {
                position: absolute !important;
                top: 0.45rem;
                right: 0.5rem;
                left: auto !important;
                float: none !important;
                margin: 0 !important;
                z-index: 2;
                line-height: 1;
                order: 99;
            }

            .card-product-select + .select2-container .select2-selection__choice[title=""],
            .card-product-select + .select2-container .select2-selection__choice:not([title]) {
                display: none !important;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-search--inline {
                float: none !important;
                flex: 1 1 5rem;
                min-width: 5rem;
                margin: 0 !important;
                line-height: 1.4;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field {
                width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__rendered:not(:has(.select2-selection__choice)) .select2-search--inline {
                flex: 1 1 100%;
                width: 100%;
                min-width: 100%;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__rendered:not(:has(.select2-selection__choice)) .select2-search__field {
                width: 100% !important;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-search__field::placeholder {
                color: #a8aaae;
                opacity: 1;
            }
        </style>
    @endonce

    <div class="pos-listing">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <h4 class="pos-listing-title">{{ $activeModule['title'] ?? 'Cards' }}</h4>
                <div class="pos-listing-search-slot" aria-hidden="true"></div>
                <div class="pos-listing-toolbar-tools">
                    <div class="pos-listing-toolbar-actions" id="cardTableActions">
                        <div class="dropdown">
                            <button
                                type="button"
                                class="btn btn-label-secondary btn-icon"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                title="Filters"
                            >
                                <i class="ti tabler-filter"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                                <div class="mb-3">
                                    <label for="card_status" class="form-label">Status</label>
                                    <select
                                        id="card_status"
                                        class="form-select filter-control select2"
                                        data-placeholder="All statuses"
                                        data-allow-clear="false"
                                        data-minimum-results-for-search="Infinity"
                                    >
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="card_sort" class="form-label">Sort By</label>
                                    <select
                                        id="card_sort"
                                        class="form-select filter-control select2"
                                        data-placeholder="Sort cards"
                                        data-allow-clear="false"
                                        data-minimum-results-for-search="Infinity"
                                    >
                                        <option value="latest">Latest</option>
                                        <option value="name">Name A-Z</option>
                                        <option value="value_high_low">Value High-Low</option>
                                        <option value="valid_until">Valid Until</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @can('create', \App\Models\Card::class)
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="addCardBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#cardModal"
                            >
                                <i class="ti tabler-plus me-1"></i>
                                Add {{ $singular }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="cards-datatables table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Card</th>
                            <th>Value</th>
                            <th>Min. Spend</th>
                            <th>Products</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="modal fade pos-listing-modal" id="cardModal" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="cardForm" action="{{ $saveUrl }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="card_id">
                        <input type="hidden" name="card_type" id="card_type" value="{{ $cardType }}">

                        <div class="modal-header">
                            <h5 class="modal-title" id="cardModalLabel">Add {{ $singular }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <x-cards.form-fields
                                :card-type="$cardType"
                                :products="$products"
                                id-prefix="card"
                                modal-id="cardModal"
                                :currency-symbol="\App\Support\Currency::symbol()"
                                :discount-types="$discountTypes"
                                :show-status="true"
                                :value-label="$valueLabel"
                                :discount-select2="true"
                            />
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="cardSubmitBtn"
                                data-create-text="Save {{ $singular }}"
                                data-update-text="Update {{ $singular }}"
                            >
                                Save {{ $singular }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.cardListingUrl = @json($listingUrl);
        window.cardEditUrlTemplate = @json($editUrlTemplate);
        window.cardType = @json($cardType);
        window.cardSingular = @json($singular);
        window.currencySymbol = @json(\App\Support\Currency::symbol());
    </script>
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/cards-form.js') }}?v={{ filemtime(public_path('assets/js/cards-form.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/cards.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/cards.js')) }}"></script>
@endsection
