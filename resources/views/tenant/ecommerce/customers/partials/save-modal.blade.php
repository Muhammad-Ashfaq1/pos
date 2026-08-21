<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="customerForm" action="{{ $customerSaveUrl ?? route('tenant.ecommerce.customers.save') }}" method="POST" novalidate>
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
                            <label for="customer_discount_group" class="form-label">Discount Group</label>
                            <div class="position-relative">
                                <select id="customer_discount_group" name="discount_group"
                                    class="form-select modal-select2" data-placeholder="Select a discount group"
                                    data-allow-clear="true"
                                    data-dropdown-parent="#customerModal"
                                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.discount-groups') }}">
                                    <option value=""></option>
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
                            <input type="text" class="form-control app-datepicker" id="customer_date_of_birth" name="date_of_birth" placeholder="YYYY-MM-DD" autocomplete="off">
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

                    {{-- Portal access + store-credit ledger (edit mode only) --}}
                    <div class="card border mb-2 d-none" id="customer_portal_panel"
                        data-invite-url="" data-adjust-url="" data-history-url="">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h6 class="mb-0 fw-bold"><i class="ti tabler-wallet me-1 text-primary"></i>Customer Portal & Store Credit</h6>
                                <span id="customer_portal_status" class="badge bg-label-secondary">Portal: Off</span>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <span class="d-block text-muted small">Current Balance</span>
                                    <span class="h5 fw-bold text-primary" id="customer_portal_balance">—</span>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Adjust Amount</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="adjust_credit_amount" placeholder="e.g. 10 or -5">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Reason</label>
                                    <input type="text" class="form-control form-control-sm" id="adjust_credit_reason" maxlength="255" placeholder="Reason for adjustment">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" id="adjust_credit_btn">Apply</button>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-label-primary" id="invite_portal_btn">
                                    <i class="ti tabler-mail me-1"></i><span id="invite_portal_label">Send Portal Invite</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-label-secondary" id="credit_history_btn">
                                    <i class="ti tabler-history me-1"></i>View Credit History
                                </button>
                            </div>

                            <div class="table-responsive mt-3 d-none" id="credit_history_wrap">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="table-light"><tr>
                                        <th class="small">Date</th><th class="small">Type</th><th class="small">Note</th><th class="small text-end">Amount</th><th class="small text-end">Balance</th>
                                    </tr></thead>
                                    <tbody id="credit_history_body"></tbody>
                                </table>
                            </div>
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
