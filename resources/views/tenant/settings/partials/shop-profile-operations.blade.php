<form id="shopOperationsSettingsForm">
    <div class="row">
        <div class="col-md-4 col-12">
            <label for="low_stock_threshold" class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
            <input type="number" min="0" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $form['low_stock_threshold']) }}" required>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Business Hours</h6>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Closed</th>
                    <th>Open</th>
                    <th>Close</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weekdayOptions as $day => $label)
                    @php
                        $closed = (bool) old("business_hours.{$day}.is_closed", data_get($form, "business_hours.{$day}.is_closed"));
                    @endphp
                    <tr data-business-hours-row>
                        <td class="fw-medium">{{ $label }}</td>
                        <td>
                            <input type="hidden" name="business_hours[{{ $day }}][is_closed]" value="0">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input business-hours-closed-toggle"
                                    type="checkbox"
                                    name="business_hours[{{ $day }}][is_closed]"
                                    value="1"
                                    @checked($closed)
                                >
                            </div>
                        </td>
                        <td>
                            <input
                                type="time"
                                class="form-control"
                                name="business_hours[{{ $day }}][open]"
                                value="{{ old("business_hours.{$day}.open", data_get($form, "business_hours.{$day}.open")) }}"
                                data-business-hours-time
                                @disabled($closed)
                            >
                        </td>
                        <td>
                            <input
                                type="time"
                                class="form-control"
                                name="business_hours[{{ $day }}][close]"
                                value="{{ old("business_hours.{$day}.close", data_get($form, "business_hours.{$day}.close")) }}"
                                data-business-hours-time
                                @disabled($closed)
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" id="saveShopOperationsSettingsButton">Save Settings</button>
    </div>
</form>
