<form id="shopNotificationsSettingsForm">
    <div class="row">
        <div class="col-md-6 mb-3">
            <input type="hidden" name="reminder_email_enabled" value="0">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="reminder_email_enabled" name="reminder_email_enabled" value="1" @checked(old('reminder_email_enabled', $form['reminder_email_enabled']))>
                <label class="form-check-label" for="reminder_email_enabled">Reminder emails enabled</label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <input type="hidden" name="receipt_email_enabled" value="0">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="receipt_email_enabled" name="receipt_email_enabled" value="1" @checked(old('receipt_email_enabled', $form['receipt_email_enabled']))>
                <label class="form-check-label" for="receipt_email_enabled">Receipt emails enabled</label>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Loyalty Settings</h6>

    <div class="row">
        <div class="col-md-6 mb-3">
            <input type="hidden" name="loyalty_enabled" value="0">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="loyalty_enabled" name="loyalty_enabled" value="1" @checked(old('loyalty_enabled', $form['loyalty_enabled']))>
                <label class="form-check-label" for="loyalty_enabled">Enable loyalty program defaults</label>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="loyalty_points_per_currency" class="form-label">Points Per Currency Unit <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" class="form-control" id="loyalty_points_per_currency" name="loyalty_points_per_currency" value="{{ old('loyalty_points_per_currency', $form['loyalty_points_per_currency']) }}" required>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" id="saveShopNotificationsSettingsButton">Save Settings</button>
    </div>
</form>
