<!-- Discount Offcanvas -->
    <div class="offcanvas offcanvas-start offcanvas-discount" tabindex="-1" id="offcanvasDiscount"
        aria-labelledby="offcanvasDiscountLabel">
        <div class="offcanvas-header border-bottom py-3">
            <div class="d-flex align-items-center">
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
                <div class="text-center py-5 border rounded-3 bg-light bg-opacity-50">
                    <span class="text-muted small">No discounts available</span>
                </div>
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
                    <input type="date" class="form-control py-2">
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

    <!-- Service Fee Offcanvas -->
    <div class="offcanvas offcanvas-start offcanvas-discount" tabindex="-1" id="offcanvasServiceFee"
        aria-labelledby="offcanvasServiceFeeLabel">
        <div class="offcanvas-header border-bottom py-3">
            <div class="d-flex align-items-center">
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
            <h5 class="fw-bold mb-4">Service fee Details</h5>

            <div class="mb-5">
                <label class="form-label fw-bold text-muted small text-uppercase">Available Service Fees</label>
                <div class="text-center py-5 border rounded-3 bg-light bg-opacity-50">
                    <span class="text-muted small">No service fee available</span>
                </div>
            </div>

            <div class="custom-service-fee-section">
                <h5 class="fw-bold mb-4">Custom Service Fee</h5>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Title</label>
                    <input type="text" class="form-control py-2" placeholder="Enter title (e.g. Service Fee)">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Value</label>
                    <div class="input-group input-group-merge">
                        <input type="text" class="form-control py-2" placeholder="Enter Value (%)">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="text-end mt-5">
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-bold">Apply
                        Service Fee</button>
                </div>
            </div>
        </div>
    </div>
