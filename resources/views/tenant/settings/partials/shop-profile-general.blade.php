<form id="shopGeneralSettingsForm">
    <div class="row">
        <div class="col-md-6">
            <label for="shop_name" class="form-label">Shop Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="shop_name" name="shop_name" maxlength="150" value="{{ old('shop_name', $form['shop_name']) }}" required>
        </div>
        <div class="col-md-6">
            <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="business_name" name="business_name" maxlength="150" value="{{ old('business_name', $form['business_name']) }}" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="owner_name" name="owner_name" maxlength="150" value="{{ old('owner_name', $form['owner_name']) }}" required>
        </div>
        <div class="col-md-6">
            <label for="website_url" class="form-label">Website URL</label>
            <input type="url" class="form-control" id="website_url" name="website_url" maxlength="255" value="{{ old('website_url', $form['website_url']) }}" placeholder="https://example.com">
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Contact Details</h6>

    <div class="row">
        <div class="col-md-6">
            <label for="business_email" class="form-label">Business Email</label>
            <input type="email" class="form-control" id="business_email" name="business_email" maxlength="150" value="{{ old('business_email', $form['business_email']) }}">
        </div>
        <div class="col-md-6">
            <label for="business_phone" class="form-label">Business Phone</label>
            <input type="text" class="form-control" id="business_phone" name="business_phone" maxlength="30" value="{{ old('business_phone', $form['business_phone']) }}">
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Business Address</h6>

    <div class="row">
        <div class="col-12 mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3" maxlength="1000">{{ old('address', $form['address']) }}</textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" class="form-control" id="city" name="city" maxlength="150" value="{{ old('city', $form['city']) }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="state" class="form-label">State</label>
            <input type="text" class="form-control" id="state" name="state" maxlength="150" value="{{ old('state', $form['state']) }}">
        </div>
        <div class="col-md-4 mb-3">
            <label for="country" class="form-label">Country</label>
            <input type="text" class="form-control" id="country" name="country" maxlength="150" value="{{ old('country', $form['country']) }}">
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" id="saveShopGeneralSettingsButton">Save Settings</button>
    </div>
</form>
