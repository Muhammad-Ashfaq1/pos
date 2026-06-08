<div class="modal fade" id="addDiscountGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h4 class="modal-title fw-bold mb-0" id="addDiscountGroupModalLabel">Customer Discount Group</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addDiscountGroupForm" data-store-url="{{ route('tenant.discounts.group.store') }}"
                    data-update-url="{{ route('tenant.discounts.group.update', ':id') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form_method">
                    <input type="hidden" name="id" id="discount_group_id">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="group_title">Title Name</label>
                            <input type="text" class="form-control border shadow-none" id="group_title"
                                name="title" placeholder="Group Title">
                        </div>
                        <div class="col-md-6">
                            <label for="discount_type">Discount Type</label>
                            <select id="discount_type" name="type" class="form-select border shadow-none select2"
                                data-placeholder="Select Discount Type" data-dropdown-parent="#addDiscountGroupModal">
                                <option value="" selected disabled>Select Discount Type</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_value">Discount Value</label>
                            <input type="number" min="0" step="0.01" class="form-control border shadow-none"
                                id="discount_value" name="value" placeholder="Select Discount Value.">
                        </div>
                        <div class="col-md-6 d-none" id="min_limit_div">
                            <label for="min_limit">Min Purchase Limit</label>
                            <input type="number" min="0" step="0.01" class="form-control border shadow-none"
                                id="min_limit" name="min_limit" placeholder="Select Min Purchase Limit">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch" id="is_active_div">
                                <input class="form-check-input border shadow-none" type="checkbox" id="is_active"
                                    name="is_active" checked>
                                <label class="form-check-label" for="is_active">Is Active</label>
                            </div>
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
