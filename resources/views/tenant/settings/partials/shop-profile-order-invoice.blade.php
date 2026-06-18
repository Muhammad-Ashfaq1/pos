<form id="shopOrderInvoiceSettingsForm">
    <div class="row">
        <div class="col-md-6 mb-3">
            <input type="hidden" name="vehicle_required" value="0">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="vehicle_required" name="vehicle_required" value="1" @checked(old('vehicle_required', $form['vehicle_required']))>
                <label class="form-check-label" for="vehicle_required">Vehicle required?</label>
            </div>
            <small class="text-muted d-block mt-1">When enabled, staff must select a vehicle before saving an order.</small>
        </div>
        <div class="col-md-6 mb-3">
            <label for="return_days_after_purchase" class="form-label">Return Days After Purchase <span class="text-danger">*</span></label>
            <input type="number" min="0" max="3650" step="1" class="form-control" id="return_days_after_purchase" name="return_days_after_purchase" value="{{ old('return_days_after_purchase', $form['return_days_after_purchase']) }}" required>
            <small class="text-muted d-block mt-1">Number of days after purchase during which a customer can return an order. Set to 0 to disable returns.</small>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" id="saveShopOrderInvoiceSettingsButton">Save Settings</button>
    </div>
</form>
