<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="customerForm" action="{{ route('tenant.ecommerce.customers.save') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id" id="customer_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-5 p-0">
                        <div class="col">
                            <label for="customer_type" class="form-label">Customer Type <span
                                    class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select id="customer_type" name="customer_type" class="form-select modal-select2"
                                    data-placeholder="Select a customer type" data-dropdown-parent="#customerModal">
                                    <option value=""></option>
                                    @foreach (\App\Models\Customer::typeOptions() as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col">
                            <label for="customer_name" class="form-label">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="name"
                                maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="customer_phone" name="phone"
                                maxlength="30">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col d-none" id="discount_group_div">
                            <label for="customer_discount_group" class="form-label">Discount Group <span
                                    class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select id="customer_discount_group" name="discount_group"
                                    class="form-select modal-select2" data-placeholder="Select a discount group"
                                    data-dropdown-parent="#customerModal"
                                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.discount-groups') }}">
                                    <option value="">None</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customer_email" name="email"
                                maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="customer_date_of_birth" class="form-label">Date Of Birth</label>
                            <input type="date" class="form-control" id="customer_date_of_birth" name="date_of_birth">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="customer_last_visit_at" class="form-label">Last Visit</label>
                            <input type="datetime-local" class="form-control" id="customer_last_visit_at"
                                name="last_visit_at">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-5">
                        <label for="customer_address" class="form-label">Address</label>
                        <textarea class="form-control" id="customer_address" name="address" rows="3" maxlength="1000"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-12 mb-5">
                        <label for="customer_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="customer_notes" name="notes" rows="3" maxlength="2000"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-3">
                            <label for="customer_total_visits" class="form-label">Total Visits</label>
                            <input type="number" min="0" class="form-control" id="customer_total_visits"
                                name="total_visits" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_lifetime_value" class="form-label">Lifetime Value</label>
                            <input type="number" step="0.01" class="form-control" id="customer_lifetime_value"
                                name="lifetime_value" value="0.00">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_loyalty_points_balance" class="form-label">Loyalty Points</label>
                            <input type="number" min="0" class="form-control"
                                id="customer_loyalty_points_balance" name="loyalty_points_balance" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_credit_balance" class="form-label">Credit Balance</label>
                            <input type="number" step="0.01" class="form-control" id="customer_credit_balance"
                                name="credit_balance" value="0.00">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="customerSubmitBtn"
                        data-create-text="Save Customer" data-update-text="Update Customer">
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
