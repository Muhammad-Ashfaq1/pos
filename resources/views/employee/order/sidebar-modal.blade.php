<!-- Discount Offcanvas -->
    <div class="offcanvas offcanvas-start offcanvas-discount" tabindex="-1" id="offcanvasDiscount"
        aria-labelledby="offcanvasDiscountLabel">
        <div class="offcanvas-header border-bottom py-3">
            <div class="d-flex align-items-center gap-3">
                <button type="button"
                    class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38"
                    data-bs-dismiss="offcanvas">
                    <i class="ti tabler-arrow-left fs-4"></i>
                </button>
                <h4 class="offcanvas-title fw-bold" id="offcanvasDiscountLabel">Discount</h4>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4">
            <h5 class="fw-bold mb-4">Discount Details</h5>

            <div class="mb-5">
                <label class="form-label fw-bold text-muted small text-uppercase">Available Discounts</label>
                <div id="order_discount_lines" class="order-discount-lines"></div>
            </div>

            <div class="custom-discount-section">
                <h5 class="fw-bold mb-4">Custom Discount</h5>
                <div class="mb-4">
                    <label class="form-label fw-bold small">Discount Title</label>
                    <input type="text" class="form-control py-2" placeholder="Discount Title">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small">Discount Type</label>
                    <select class="form-select py-2">
                        <option selected>Select Discount Type</option>
                        <option value="fixed">Fixed</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small">Discount Value</label>
                    <input type="text" class="form-control py-2" placeholder="Select Discount Value">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small">Valid Until</label>
                    <input type="text" class="form-control py-2 app-datepicker" placeholder="YYYY-MM-DD" autocomplete="off">
                    <small class="text-muted mt-2 d-block fs-tiny">Please choose an expiry date for
                        the discount.</small>
                </div>
                <div class="text-end mt-5">
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-bold">Apply
                        Discount</button>
                </div>
            </div>
        </div>
    </div>

    @foreach([
        'discount' => ['title' => 'Discount Cards', 'icon' => 'tabler-ticket', 'valueSuffix' => ''],
        'gift' => ['title' => 'Gift Cards', 'icon' => 'tabler-gift', 'valueSuffix' => ''],
        'reward' => ['title' => 'Reward Cards', 'icon' => 'tabler-trophy', 'valueSuffix' => ' points'],
    ] as $cardType => $config)
        <div class="offcanvas offcanvas-start offcanvas-discount order-card-offcanvas" tabindex="-1"
            id="offcanvas{{ ucfirst($cardType) }}Cards"
            aria-labelledby="offcanvas{{ ucfirst($cardType) }}CardsLabel">
            <div class="offcanvas-header border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <button type="button"
                        class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38"
                        data-bs-dismiss="offcanvas">
                        <i class="ti tabler-arrow-left fs-4"></i>
                    </button>
                    <h4 class="offcanvas-title fw-bold mb-0" id="offcanvas{{ ucfirst($cardType) }}CardsLabel">{{ $config['title'] }}</h4>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-4">
                @can('create', \App\Models\Card::class)
                    <div class="d-flex justify-content-end mb-3">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary btn-add-order-card"
                            data-card-type="{{ $cardType }}"
                            data-bs-toggle="modal"
                            data-bs-target="#{{ \App\Models\Card::metaFor($cardType)['modal'] }}"
                        >
                            <i class="ti tabler-plus me-1"></i>
                            Add
                        </button>
                    </div>
                @endcan
                <p class="text-muted mb-4">Choose a valid {{ $cardType }} card to apply to this order.</p>

                <div class="order-card-list" data-card-type="{{ $cardType }}">
                    @forelse($orderCards->get($cardType, collect()) as $card)
                        @include('employee.order.partials.order-card-option', ['card' => $card])
                    @empty
                        <div class="text-center py-5 border rounded-3" data-order-card-empty="{{ $cardType }}">
                            <i class="ti {{ $config['icon'] }} fs-1 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No valid {{ strtolower($config['title']) }} available.</p>
                        </div>
                    @endforelse
                </div>

                @php $hasOrderCards = $orderCards->get($cardType, collect())->isNotEmpty(); @endphp

                <div
                    class="d-flex justify-content-between gap-2 mt-4 order-card-actions {{ $hasOrderCards ? '' : 'd-none' }}"
                    data-card-type="{{ $cardType }}"
                >
                    <button type="button" class="btn btn-outline-danger clear-order-card" data-card-type="{{ $cardType }}">Remove</button>
                    <button type="button" class="btn btn-primary apply-order-card" data-card-type="{{ $cardType }}">Apply Card</button>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Service Fee Offcanvas -->
    <div class="offcanvas offcanvas-start offcanvas-discount" tabindex="-1" id="offcanvasServiceFee"
        aria-labelledby="offcanvasServiceFeeLabel">
        <div class="offcanvas-header border-bottom py-3">
            <div class="d-flex align-items-center gap-3">
                <button type="button"
                    class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38"
                    data-bs-dismiss="offcanvas">
                    <i class="ti tabler-arrow-left fs-4"></i>
                </button>
                <h4 class="offcanvas-title fw-bold" id="offcanvasServiceFeeLabel">Service Fee</h4>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4">
            <h5 class="fw-bold mb-4">Service Fee Details</h5>

            <div class="service-fee-picker-panel mb-4">
                <label for="order_service_filter" class="form-label fw-bold text-muted small text-uppercase">Service</label>
                <select id="order_service_filter"
                    class="form-select select2"
                    multiple
                    data-placeholder="Select services"
                    data-allow-clear="true"
                    data-minimum-results-for-search="0"
                    data-dropdown-parent="#offcanvasServiceFee"
                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.services') }}">
                </select>
            </div>

            <div class="custom-service-fee-section">
                <h5 class="fw-bold mb-4">Selected Services</h5>

                <div class="selected-service-fee-rows" id="selected_service_fee_rows"></div>

                <div class="service-fee-actions mt-4">
                    <button type="button" class="btn btn-outline-danger px-4 py-2 rounded-3 fw-bold" id="remove_custom_service_fee">
                        <i class="ti tabler-refresh me-1"></i> Clear
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 rounded-3 fw-bold" id="apply_custom_service_fee">
                        <i class="ti tabler-plus me-1"></i> Add to Order
                    </button>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">Applied Service Fees</h6>
                <div id="service_fee_lines" class="service-fee-items"></div>
            </div>
        </div>
    </div>
