<div class="modal fade" id="addDiscountGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h4 class="modal-title fw-bold mb-0" id="addDiscountGroupModalLabel">Customer Discount Group</h4>
                <button type="button" class="btn-close bg-label-secondary rounded-circle p-2" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addDiscountGroupForm" 
                    data-store-url="{{ route('tenant.discounts.group.store') }}"
                    data-update-url="{{ route('tenant.discounts.group.update', ':id') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form_method">
                    <input type="hidden" name="id" id="discount_group_id">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-lg border shadow-none rounded-3"
                                id="group_title" name="title" placeholder="Group Title">
                        </div>
                        <div class="col-md-6">
                            <select id="discount_type" name="type"
                                class="form-select form-select-lg border shadow-none rounded-3 select2"
                                data-placeholder="Select Discount Type" data-dropdown-parent="#addDiscountGroupModal">
                                <option value="" selected disabled>Select Discount Type</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="number" step="0.01"
                                class="form-control form-control-lg border shadow-none rounded-3" id="discount_value"
                                name="value" placeholder="Select Discount Value.">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit"
                            class="add-discount-group btn btn-primary btn-lg px-5 fw-bold rounded-3">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
