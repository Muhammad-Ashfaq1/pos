<form id="shopRegionalSettingsForm">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
            <select id="currency" name="currency" class="form-select select2" data-placeholder="Select currency" required>
                @foreach($currencyOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('currency', $form['currency']) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
            <select id="timezone" name="timezone" class="form-select select2" data-placeholder="Select timezone" required>
                @foreach($timezoneOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('timezone', $form['timezone']) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="locale" class="form-label">Locale <span class="text-danger">*</span></label>
            <select id="locale" name="locale" class="form-select select2" data-placeholder="Select locale" required>
                @foreach($localeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('locale', $form['locale']) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Billing Defaults</h6>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="tax_name" class="form-label">Tax Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="tax_name" name="tax_name" maxlength="100" value="{{ old('tax_name', $form['tax_name']) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="tax_percentage" class="form-label">Tax Percentage <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', $form['tax_percentage']) }}" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="invoice_prefix" class="form-label">Invoice Prefix <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" maxlength="20" value="{{ old('invoice_prefix', $form['invoice_prefix']) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="invoice_next_number" class="form-label">Invoice Next Number <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control" id="invoice_next_number" name="invoice_next_number" value="{{ old('invoice_next_number', $form['invoice_next_number']) }}" required>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" id="saveShopRegionalSettingsButton">Save Settings</button>
    </div>
</form>
