<h4 class="account-settings-password-title">Change Password</h4>

<form method="POST" action="{{ route('account.password.update') }}" class="account-settings-form">
    @csrf
    <div class="row g-4">
        <div class="col-md-4 form-password-toggle">
            <label class="form-label" for="current_password">Current Password</label>
            <div class="input-group input-group-merge">
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    class="form-control @error('current_password') is-invalid @enderror"
                    placeholder="Enter current password"
                    required
                    autocomplete="current-password">
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-4 form-password-toggle">
            <label class="form-label" for="password">New Password</label>
            <div class="input-group input-group-merge">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter new password"
                    required
                    autocomplete="new-password">
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-4 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirm New Password</label>
            <div class="input-group input-group-merge">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Enter confirm new password"
                    required
                    autocomplete="new-password">
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
        </div>
    </div>

    <div class="account-settings-actions">
        <button type="submit" class="btn btn-primary account-settings-save-btn">Change Password</button>
    </div>
</form>
