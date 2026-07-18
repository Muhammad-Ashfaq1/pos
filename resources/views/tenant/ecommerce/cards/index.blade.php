@extends('layouts.app')

@section('title', $activeModule['title'] ?? 'Cards')

@section('content')
    @php
        $singular = $activeModule['singular'];
        $isDiscount = $cardType === \App\Models\Card::TYPE_DISCOUNT;
        $isReward = $cardType === \App\Models\Card::TYPE_REWARD;
        $valueLabel = $isDiscount
            ? 'Discount Percentage'
            : ($isReward ? 'Reward Points' : 'Gift Amount');
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

            .modal .card-product-select + .select2-container .select2-selection--multiple {
                min-height: calc(1.5em + 0.875rem + 2px);
                padding: 0.25rem 0.5rem;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__rendered:not(:has(.select2-selection__choice)) .select2-search--inline {
                float: none;
                display: block;
                width: 100%;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-selection__rendered:not(:has(.select2-selection__choice)) .select2-search__field {
                width: 100% !important;
                margin-top: 0;
            }

            .card-product-select + .select2-container .select2-selection--multiple .select2-search__field::placeholder {
                color: #a8aaae;
                opacity: 1;
            }
        </style>
    @endonce

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2" id="cardTableActions">
            <div class="dropdown">
                <button
                    type="button"
                    class="btn btn-label-secondary btn-icon"
                    data-bs-toggle="dropdown"
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

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="cards-datatables table">
                <thead class="bg-label-primary">
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

    <div class="modal fade" id="cardModal" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="card_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="card_name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>

                            @if ($isDiscount)
                                <div class="col-md-6" id="card_discount_type_wrap">
                                    <label for="card_discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <select id="card_discount_type" name="discount_type" class="form-select select2" data-placeholder="Select a discount type" data-dropdown-parent="#cardModal">
                                            @foreach($discountTypes as $type => $label)
                                                <option value="{{ $type }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label for="card_value" class="form-label" id="card_value_label">{{ $valueLabel }} <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="{{ $isReward ? '1' : '0.01' }}"
                                    min="0.01"
                                    class="form-control"
                                    id="card_value"
                                    name="value"
                                    @if ($isDiscount) max="100" @endif
                                >
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="card_minimum_spend" class="form-label">Minimum Spend <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="card_minimum_spend" name="minimum_spend" value="0">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="card_product_ids">Select Products</label>
                                <select
                                    id="card_product_ids"
                                    name="product_ids[]"
                                    class="form-select select2 card-product-select"
                                    multiple
                                    data-placeholder="Select a product"
                                    data-dropdown-parent="#cardModal"
                                >
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="card_valid_until" class="form-label">Valid Until</label>
                                <input type="date" class="form-control" id="card_valid_until" name="valid_until">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="card_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="card_is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
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
    <script src="{{ asset('assets/js/tenant/e-com/cards.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/cards.js')) }}"></script>
@endsection
